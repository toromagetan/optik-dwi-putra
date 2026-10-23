<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageAndChannelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->product = Product::factory()->create();
    }

    public function test_valid_image_upload_is_stored(): void
    {
        $file = UploadedFile::fake()->image('foto.jpg', 800, 600);

        $this->actingAs($this->admin)
            ->post(route('admin.products.images.store', $this->product), ['image' => $file, 'alt_text' => 'Foto uji'])
            ->assertSessionHas('success');

        $image = $this->product->images()->first();
        $this->assertNotNull($image);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_fake_image_file_is_rejected(): void
    {
        // Berkas teks asli bernama .jpg — MIME dideteksi dari isi berkas (finfo),
        // bukan dari ekstensi. (UploadedFile::fake() meniru MIME dari ekstensi,
        // jadi pengujian ini memakai berkas temp sungguhan.)
        $path = tempnam(sys_get_temp_dir(), 'jahat').'.jpg';
        file_put_contents($path, 'ini bukan gambar, hanya teks');
        $fake = new UploadedFile($path, 'jahat.jpg', null, null, true);
        $this->assertSame('text/plain', $fake->getMimeType());

        $this->actingAs($this->admin)
            ->post(route('admin.products.images.store', $this->product), ['image' => $fake])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, $this->product->images()->count());
        @unlink($path);
    }

    public function test_oversized_image_is_rejected(): void
    {
        $big = UploadedFile::fake()->image('besar.jpg')->size(3000); // 3 MB > batas 2 MB

        $this->actingAs($this->admin)
            ->post(route('admin.products.images.store', $this->product), ['image' => $big])
            ->assertSessionHasErrors('image');
    }

    public function test_image_delete_removes_file_and_row(): void
    {
        $image = $this->product->images()->create(['path' => 'products/'.$this->product->id.'/x.jpg', 'alt_text' => 'x', 'sort_order' => 0]);
        Storage::disk('public')->put($image->path, 'isi');

        $this->actingAs($this->admin)
            ->delete(route('admin.products.images.destroy', [$this->product, $image]))
            ->assertSessionHas('success');

        Storage::disk('public')->assertMissing($image->path);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_dangerous_channel_url_is_rejected(): void
    {
        foreach (['javascript:alert(1)', 'data:text/html;base64,PHNjcmlwdD4=', 'ftp://contoh.id/berkas'] as $badUrl) {
            $this->actingAs($this->admin)
                ->post(route('admin.products.channels.store', $this->product), [
                    'channel_type' => 'shopee',
                    'url' => $badUrl,
                    'is_active' => 1,
                ])
                ->assertSessionHasErrors('url');
        }

        $this->assertSame(0, $this->product->channels()->count());
    }

    public function test_valid_channel_is_saved_and_shown_on_product_page(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.channels.store', $this->product), [
                'channel_type' => 'shopee',
                'label' => 'Shopee Resmi',
                'url' => 'https://shopee.co.id/produk-uji',
                'is_active' => 1,
            ])
            ->assertSessionHas('success');

        $this->get(route('product.show', $this->product->slug))
            ->assertSee('https://shopee.co.id/produk-uji')
            ->assertSee('Shopee Resmi');
    }

    public function test_inactive_channel_is_hidden_from_product_page(): void
    {
        $this->product->channels()->create([
            'channel_type' => 'tokopedia',
            'label' => 'Tautan Nonaktif Uji',
            'url' => 'https://www.tokopedia.com/sembunyi',
            'is_active' => false,
        ]);

        $this->get(route('product.show', $this->product->slug))->assertDontSee('Tautan Nonaktif Uji');
    }

    public function test_guest_cannot_upload_image_or_channel(): void
    {
        $file = UploadedFile::fake()->image('foto.jpg');
        $this->post(route('admin.products.images.store', $this->product), ['image' => $file])
            ->assertRedirect(route('login'));

        $this->post(route('admin.products.channels.store', $this->product), [
            'channel_type' => 'shopee', 'url' => 'https://shopee.co.id/x',
        ])->assertRedirect(route('login'));
    }
}

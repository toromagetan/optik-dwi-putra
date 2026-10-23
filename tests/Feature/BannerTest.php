<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_homepage_shows_active_banners_in_order_and_hides_inactive(): void
    {
        Banner::create(['title' => 'Promo Kedua', 'is_active' => true, 'sort_order' => 2]);
        Banner::create(['title' => 'Promo Pertama', 'is_active' => true, 'sort_order' => 1]);
        Banner::create(['title' => 'Promo Tersembunyi', 'is_active' => false, 'sort_order' => 0]);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('Promo Pertama');
        $response->assertSee('Promo Kedua');
        $response->assertDontSee('Promo Tersembunyi');
        $response->assertSeeInOrder(['Promo Pertama', 'Promo Kedua']);
        $response->assertSee('promo-slider', false);
    }

    public function test_guest_cannot_access_banner_admin(): void
    {
        $this->get(route('admin.banners.index'))->assertRedirect(route('login'));
        $this->post(route('admin.banners.store'), ['title' => 'X'])->assertRedirect(route('login'));
    }

    public function test_admin_can_create_banner_with_valid_image(): void
    {
        $file = UploadedFile::fake()->image('banner.jpg', 1680, 560);

        $this->actingAs($this->admin)
            ->post(route('admin.banners.store'), [
                'title' => 'Promo Uji',
                'subtitle' => 'Subjudul uji',
                'link_url' => '/katalog',
                'sort_order' => 1,
                'is_active' => '1',
                'image' => $file,
            ])
            ->assertRedirect(route('admin.banners.index'))
            ->assertSessionHas('success');

        $banner = Banner::first();
        $this->assertNotNull($banner);
        $this->assertSame('Promo Uji', $banner->title);
        $this->assertTrue($banner->is_active);
        Storage::disk('public')->assertExists($banner->image_path);
    }

    public function test_fake_image_file_is_rejected(): void
    {
        // Berkas teks berekstensi .jpg — MIME nyata bukan gambar.
        $path = tempnam(sys_get_temp_dir(), 'jahat').'.jpg';
        file_put_contents($path, 'ini bukan gambar');
        $fake = new UploadedFile($path, 'jahat.jpg', null, null, true);

        $this->actingAs($this->admin)
            ->post(route('admin.banners.store'), ['title' => 'Jahat', 'image' => $fake])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, Banner::count());
    }

    public function test_dangerous_link_is_rejected_but_internal_and_https_allowed(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.banners.store'), ['title' => 'JS', 'link_url' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('link_url');
        $this->assertSame(0, Banner::count());

        $this->actingAs($this->admin)
            ->post(route('admin.banners.store'), ['title' => 'Internal', 'link_url' => '/katalog'])
            ->assertSessionHas('success');
        $this->actingAs($this->admin)
            ->post(route('admin.banners.store'), ['title' => 'Eksternal', 'link_url' => 'https://shopee.co.id/toko'])
            ->assertSessionHas('success');
        $this->assertSame(2, Banner::count());
    }

    public function test_update_replaces_image_and_delete_removes_file(): void
    {
        $banner = Banner::create(['title' => 'Lama', 'is_active' => true, 'sort_order' => 0]);
        Storage::disk('public')->put('banners/lama.jpg', 'isi-lama');
        $banner->update(['image_path' => 'banners/lama.jpg']);

        $this->actingAs($this->admin)
            ->put(route('admin.banners.update', $banner), [
                'title' => 'Baru',
                'image' => UploadedFile::fake()->image('baru.jpg', 1200, 400),
            ])
            ->assertSessionHas('success');

        $banner->refresh();
        $this->assertSame('Baru', $banner->title);
        Storage::disk('public')->assertMissing('banners/lama.jpg');
        Storage::disk('public')->assertExists($banner->image_path);
        $newPath = $banner->image_path;

        $this->actingAs($this->admin)
            ->delete(route('admin.banners.destroy', $banner))
            ->assertSessionHas('success');
        $this->assertSame(0, Banner::count());
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_move_swaps_sort_order(): void
    {
        $a = Banner::create(['title' => 'A', 'is_active' => true, 'sort_order' => 1]);
        $b = Banner::create(['title' => 'B', 'is_active' => true, 'sort_order' => 2]);

        $this->actingAs($this->admin)
            ->post(route('admin.banners.move', $b), ['direction' => 'up'])
            ->assertSessionHas('success');

        $this->assertSame(1, $b->fresh()->sort_order);
        $this->assertSame(2, $a->fresh()->sort_order);
    }
}

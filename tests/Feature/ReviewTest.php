<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_reviews_appear_on_homepage_with_summary(): void
    {
        Review::create(['name' => 'Pelanggan Senang', 'rating' => 5, 'text' => 'Pelayanannya luar biasa.', 'is_active' => true]);
        Review::create(['name' => 'Pelanggan Sembunyi', 'rating' => 1, 'text' => 'Tidak tampil.', 'is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Apa Kata Pelanggan')
            ->assertSee('Pelanggan Senang')
            ->assertSee('Pelayanannya luar biasa.')
            ->assertDontSee('Pelanggan Sembunyi');
    }

    public function test_review_with_screenshot_image_shows_image_on_homepage(): void
    {
        Storage::fake('public');
        $shot = UploadedFile::fake()->image('testimoni-wa.jpg', 400, 600);
        $path = $shot->store('reviews', 'public');

        Review::create(['name' => 'Pengirim Screenshot', 'rating' => 5, 'image_path' => $path, 'is_active' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Tangkapan layar ulasan Pengirim Screenshot')
            ->assertSee('Pengirim Screenshot');
    }

    public function test_guest_cannot_manage_reviews(): void
    {
        $this->get('/admin/reviews')->assertRedirect('/masuk');
    }

    public function test_admin_can_create_review_with_screenshot(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/admin/reviews', [
                'name' => 'Ulasan Baru',
                'location' => 'Surabaya',
                'source' => 'Google',
                'rating' => 5,
                'text' => 'Sangat memuaskan.',
                'image' => UploadedFile::fake()->image('shot.png', 300, 400),
                'is_active' => '1',
            ])
            ->assertRedirect('/admin/reviews');

        $review = Review::where('name', 'Ulasan Baru')->firstOrFail();
        $this->assertSame(5, $review->rating);
        Storage::disk('public')->assertExists($review->image_path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'review.created']);
    }

    public function test_invalid_rating_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/admin/reviews', ['name' => 'X', 'rating' => 9])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseMissing('reviews', ['name' => 'X']);
    }

    public function test_non_image_review_upload_is_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/admin/reviews', [
                'name' => 'Ulasan Palsu',
                'rating' => 5,
                'image' => UploadedFile::fake()->create('jahat.php', 10, 'application/x-php'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('reviews', ['name' => 'Ulasan Palsu']);
    }

    public function test_delete_review_removes_its_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $path = UploadedFile::fake()->image('hapus.jpg', 100, 100)->store('reviews', 'public');
        $review = Review::create(['name' => 'Dihapus', 'rating' => 5, 'image_path' => $path]);

        $this->actingAs($admin)->delete('/admin/reviews/'.$review->id)->assertRedirect();

        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}

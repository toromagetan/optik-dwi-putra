<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
        $this->get(route('admin.settings.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_login_and_reach_dashboard(): void
    {
        $admin = User::factory()->create(['email' => 'admin@uji.id', 'role' => 'admin']);

        $this->post(route('login.attempt'), ['email' => 'admin@uji.id', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'admin@uji.id']);

        $this->post(route('login.attempt'), ['email' => 'admin@uji.id', 'password' => 'salah-total'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        User::factory()->create(['email' => 'admin@uji.id']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.attempt'), ['email' => 'admin@uji.id', 'password' => 'salah-'.$i]);
        }

        // Percobaan ke-6 — bahkan dengan kata sandi benar — harus ditolak throttle.
        $this->post(route('login.attempt'), ['email' => 'admin@uji.id', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_non_admin_role_cannot_enter_admin_panel(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_logout_returns_to_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}

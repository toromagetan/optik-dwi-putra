<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\StoreSetting;
use App\Models\User;
use Database\Seeders\StoreSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StoreSettingSeeder::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_updates_store_identity_and_it_shows_on_storefront(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'store_name' => 'Optik Nama Baru Uji',
            'tagline' => 'Tagline Uji',
            'whatsapp_number' => '0812-9999-8888',
        ])->assertSessionHas('success');

        $this->assertSame('Optik Nama Baru Uji', StoreSetting::get('store_name'));
        // Nomor WhatsApp dibersihkan menjadi digit saja.
        $this->assertSame('081299998888', StoreSetting::get('whatsapp_number'));

        $this->get(route('home'))->assertSee('Optik Nama Baru Uji');

        $this->assertTrue(AuditLog::where('action', 'settings.updated')->exists());
    }

    public function test_invalid_setting_values_are_rejected(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'email' => 'bukan-email',
            'telegram_url' => 'javascript:alert(1)',
            'cart_max_qty' => 0,
        ])->assertSessionHasErrors(['email', 'telegram_url', 'cart_max_qty']);
    }

    public function test_guest_cannot_open_or_update_settings(): void
    {
        $this->get(route('admin.settings.index'))->assertRedirect(route('login'));
        $this->put(route('admin.settings.update'), ['store_name' => 'Dibajak'])->assertRedirect(route('login'));
        $this->assertSame('Optik Dwi Putra', StoreSetting::get('store_name'));
    }
}

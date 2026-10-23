<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StoreSetting extends Model
{
    protected $fillable = ['setting_key', 'setting_value', 'setting_type'];

    /**
     * Registri pengaturan toko: semua yang dapat diatur dari panel admin.
     * key => [label, type, default, group]
     */
    public const REGISTRY = [
        // Identitas
        'store_name' => ['Nama Toko', 'text', 'Optik Dwi Putra', 'identitas'],
        'tagline' => ['Tagline', 'text', 'Kacamata & Lensa Berkualitas', 'identitas'],
        'logo_path' => ['Logo Toko', 'image', null, 'identitas'],
        'about' => ['Tentang Toko', 'textarea', null, 'identitas'],
        'promo_text' => ['Teks Promosi (banner beranda)', 'text', null, 'identitas'],
        'footer_note' => ['Catatan Kaki (footer)', 'text', null, 'identitas'],
        // Kontak & kanal
        'address' => ['Alamat Toko', 'textarea', null, 'kontak'],
        'phone' => ['Telepon', 'text', null, 'kontak'],
        'email' => ['Email', 'email', null, 'kontak'],
        'whatsapp_number' => ['Nomor WhatsApp (format 62…)', 'text', null, 'kontak'],
        'telegram_url' => ['Tautan Telegram', 'url', null, 'kontak'],
        'opening_hours' => ['Jam Buka', 'text', null, 'kontak'],
        // Media sosial & toko marketplace
        'instagram_url' => ['Instagram', 'url', null, 'sosial'],
        'facebook_url' => ['Facebook', 'url', null, 'sosial'],
        'tiktok_url' => ['TikTok', 'url', null, 'sosial'],
        'shopee_store_url' => ['Toko Shopee', 'url', null, 'sosial'],
        'tokopedia_store_url' => ['Toko Tokopedia', 'url', null, 'sosial'],
        // Katalog & keranjang
        'cart_max_qty' => ['Kuantitas maks per item keranjang', 'number', '10', 'katalog'],
        'free_consultation_note' => ['Catatan konsultasi gratis', 'text', null, 'katalog'],
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = Cache::rememberForever("setting.{$key}", function () use ($key) {
            return static::query()->where('setting_key', $key)->value('setting_value');
        });

        if ($value === null || $value === '') {
            return $default ?? (self::REGISTRY[$key][2] ?? null);
        }

        return $value;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value, 'setting_type' => self::REGISTRY[$key][1] ?? 'text']
        );

        Cache::forget("setting.{$key}");
    }

    public static function groups(): array
    {
        return [
            'identitas' => 'Identitas Toko',
            'kontak' => 'Kontak & Kanal Komunikasi',
            'sosial' => 'Media Sosial & Marketplace',
            'katalog' => 'Katalog & Keranjang',
        ];
    }
}

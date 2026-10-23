<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /** Nilai awal (dummy) — semuanya dapat diubah dari Panel Admin → Pengaturan Toko. */
    public function run(): void
    {
        $defaults = [
            'store_name' => 'Optik Dwi Putra',
            'tagline' => 'Jernih Melihat, Nyaman Beraktivitas',
            'about' => 'Optik Dwi Putra melayani pemeriksaan mata, pembuatan kacamata resep, lensa, softlens, dan aksesori optik dengan harga jujur dan pengerjaan rapi. Kunjungi toko kami atau pesan melalui WhatsApp dan marketplace.',
            'promo_text' => 'Promo bulan ini: GRATIS periksa mata & konsultasi lensa untuk semua pengunjung toko!',
            'footer_note' => null,
            'address' => 'Jl. Contoh Raya No. 88, Surabaya, Jawa Timur',
            'phone' => '(031) 555-0134',
            'email' => 'halo@optikdwiputra.id',
            'whatsapp_number' => '6281234567890',
            'telegram_url' => 'https://t.me/optikdwiputra',
            'opening_hours' => 'Senin–Sabtu 09.00–20.00 WIB · Minggu 10.00–17.00 WIB',
            'instagram_url' => 'https://instagram.com/optikdwiputra',
            'facebook_url' => 'https://facebook.com/optikdwiputra',
            'tiktok_url' => 'https://www.tiktok.com/@optikdwiputra',
            'shopee_store_url' => 'https://shopee.co.id/optikdwiputra',
            'tokopedia_store_url' => 'https://www.tokopedia.com/optikdwiputra',
            'cart_max_qty' => '10',
            'free_consultation_note' => 'Gratis periksa mata komputer & konsultasi pemilihan lensa di toko — tanpa wajib membeli.',
        ];

        foreach ($defaults as $key => $value) {
            StoreSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value, 'setting_type' => StoreSetting::REGISTRY[$key][1] ?? 'text']
            );
        }
    }
}

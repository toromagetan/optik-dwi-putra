<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Support\DummyImageGenerator;
use Illuminate\Database\Seeder;

/**
 * Tiga banner promo contoh untuk slider beranda — naskah mengikuti
 * rekomendasi versi sebelumnya (dapat diganti sepenuhnya di panel
 * admin, termasuk gambarnya). Gambar = ilustrasi generator berlabel
 * "Foto contoh (dummy)".
 */
class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $generator = new DummyImageGenerator;

        $banners = [
            [
                'title' => 'Promo Akhir Tahun — Diskon hingga 50%',
                'subtitle' => 'Frame & lensa pilihan — hemat sampai setengah harga.',
                'link_url' => '/katalog',
                'sort_order' => 1,
            ],
            [
                'title' => 'Koleksi Premium — Harga Spesial Akhir Tahun',
                'subtitle' => 'Material premium pilihan dengan harga spesial sampai akhir tahun.',
                'link_url' => '/katalog',
                'sort_order' => 2,
            ],
            [
                'title' => 'Periksa Mata Gratis — Tanpa Syarat',
                'subtitle' => 'Cek mata oleh optometrist kami, tanpa syarat pembelian.',
                'link_url' => null,
                'sort_order' => 3,
            ],
        ];

        foreach ($banners as $i => $data) {
            Banner::updateOrCreate(
                ['title' => $data['title']],
                $data + [
                    'image_path' => $generator->generateBanner($i, $data['title']),
                    'is_active' => true,
                ]
            );
        }
    }
}

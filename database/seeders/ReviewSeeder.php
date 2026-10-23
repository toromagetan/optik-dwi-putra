<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Support\DummyImageGenerator;
use Illuminate\Database\Seeder;

/**
 * Ulasan CONTOH (dummy) untuk seksi "Apa Kata Pelanggan" — termasuk
 * dua "tangkapan layar" hasil generator berlabel dummy. Ganti dengan
 * testimoni asli pelanggan lewat panel admin Ulasan Pelanggan.
 */
class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $generator = app(DummyImageGenerator::class);

        Review::updateOrCreate(
            ['name' => 'Rina Amelia'],
            [
                'location' => 'Surabaya',
                'source' => 'WhatsApp',
                'rating' => 5,
                'text' => 'Kacamatanya sudah sampai kemarin. Ringan banget dipakainya, pusingku berkurang banyak. Makasih banyak!',
                'image_path' => $generator->generateReviewShot('wa', 'Rina Amelia'),
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Review::updateOrCreate(
            ['name' => 'Budi Santoso'],
            [
                'location' => 'Sidoarjo',
                'source' => 'Google',
                'rating' => 5,
                'text' => 'Sudah langganan dari 2023. Pelayanannya ramah, periksa matanya teliti, frame-nya awet. Sangat direkomendasikan!',
                'image_path' => $generator->generateReviewShot('google', 'Budi Santoso'),
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Review::updateOrCreate(
            ['name' => 'Dewi Lestari'],
            [
                'location' => 'Surabaya',
                'source' => 'Google',
                'rating' => 5,
                'text' => 'Periksa matanya gratis dan teliti banget, minus anak saya ketahuan naik. Dibantu pilihkan frame yang cocok, tidak asal jual. Terima kasih!',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        Review::updateOrCreate(
            ['name' => 'Andi Pratama'],
            [
                'location' => 'Gresik',
                'source' => 'WhatsApp',
                'rating' => 5,
                'text' => 'Pesan lewat WhatsApp, admin fast respon. Lensa photochromic selesai lebih cepat dari janji. Hasilnya rapi.',
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        Review::updateOrCreate(
            ['name' => 'Sari Wulandari'],
            [
                'location' => 'Surabaya',
                'source' => 'Instagram',
                'rating' => 4,
                'text' => 'Frame-nya lucu-lucu dan harganya jujur. Softlens juga selalu ready. Langganan keluarga kami.',
                'is_active' => true,
                'sort_order' => 5,
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\DummyImageGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Katalog contoh: 5 kategori, 3 merek, 10 produk dummy masing-masing
     * dengan 3 gambar hasil gambar program (DummyImageGenerator).
     * Seluruhnya dapat diubah/diganti dari panel admin.
     */
    public function run(): void
    {
        $categories = [];
        foreach ([
            ['Frame Kacamata', 'Bingkai kacamata untuk lensa minus, plus, dan silinder.', 1],
            ['Lensa', 'Lensa bening, antiradiasi, photochromic, dan progresif.', 2],
            ['Kacamata Hitam', 'Sunglasses untuk perlindungan UV dan gaya.', 3],
            ['Softlens', 'Lensa kontak bening dan berwarna beserta perawatannya.', 4],
            ['Aksesoris', 'Cairan, case, lap, dan perlengkapan perawatan kacamata.', 5],
        ] as [$name, $desc, $order]) {
            $categories[$name] = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $desc, 'is_active' => true, 'sort_order' => $order]
            );
        }

        $brands = [];
        foreach (['DwiVision', 'Arunika', 'Kala Eyewear'] as $name) {
            $brands[$name] = Brand::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
        }

        // [nama, merek, kategori, sku, harga, stok, ketersediaan, unggulan, palet bg, palet frame, deskripsi]
        $products = [
            ['Frame Titanium Ringan A1', 'DwiVision', 'Frame Kacamata', 'FRM-DV-A1-BLK', 450000, 12, 'available', true, [214, 232, 224], [35, 40, 45],
                'Frame titanium ultraringan (hanya 9 gram) dengan engsel fleksibel dan nose pad silikon. Nyaman dipakai seharian, cocok untuk lensa minus tinggi. Garansi engsel 6 bulan.'],
            ['Frame Acetate Klasik Retro B2', 'Arunika', 'Frame Kacamata', 'FRM-AR-B2-TOR', 325000, 8, 'available', true, [238, 228, 214], [122, 74, 38],
                'Frame acetate motif tortoise bergaya retro klasik. Material tebal premium dengan finishing poles tangan. Ukuran 52-20-145, cocok untuk wajah sedang hingga lebar.'],
            ['Frame Metal Slim C3', 'Kala Eyewear', 'Frame Kacamata', 'FRM-KL-C3-GLD', 275000, 0, 'available', false, [226, 230, 238], [176, 141, 62],
                'Frame metal ramping warna emas dengan desain minimalis elegan. Pilihan tepat untuk tampilan profesional. (Contoh produk stok habis — status otomatis tampil “Stok Habis”.)'],
            ['Lensa Antiradiasi Bluecut 1.56', 'DwiVision', 'Lensa', 'LNS-DV-BC156', 350000, 25, 'available', true, [216, 232, 240], [52, 84, 110],
                'Lensa indeks 1.56 dengan lapisan bluecut untuk menyaring cahaya biru dari layar, plus anti-UV dan anti-refleksi. Harga per pasang, sudah termasuk pemasangan standar.'],
            ['Lensa Photochromic Transisi 1.61', 'Arunika', 'Lensa', 'LNS-AR-PC161', 685000, 6, 'available', false, [232, 224, 238], [84, 64, 120],
                'Lensa photochromic yang berubah gelap otomatis di bawah sinar matahari dan kembali bening di dalam ruangan. Indeks 1.61 lebih tipis untuk minus sedang–tinggi. Harga per pasang.'],
            ['Lensa Progresif Premium 1.67', 'DwiVision', 'Lensa', 'LNS-DV-PG167', 1450000, 3, 'contact_for_availability', false, [224, 236, 228], [36, 92, 66],
                'Lensa progresif premium indeks 1.67 dengan koridor pandang luas untuk jarak jauh, menengah, dan baca dalam satu lensa. Wajib fitting & pengukuran segmen di toko sebelum dipesan.'],
            ['Sunglasses Polarized Wayfarer D4', 'Kala Eyewear', 'Kacamata Hitam', 'SUN-KL-D4-BLK', 399000, 15, 'available', true, [232, 232, 226], [28, 30, 34],
                'Kacamata hitam model wayfarer dengan lensa polarized anti-silau dan perlindungan UV400. Cocok untuk menyetir dan aktivitas luar ruangan. Termasuk hard case dan lap microfiber.'],
            ['Sunglasses Aviator Gold E5', 'Arunika', 'Kacamata Hitam', 'SUN-AR-E5-GLD', 525000, 5, 'available', false, [240, 232, 214], [168, 128, 52],
                'Sunglasses aviator klasik rangka emas dengan lensa gradasi hijau G-15. Perlindungan UV400, bantalan hidung dapat disetel. Termasuk soft case kulit sintetis.'],
            ['Softlens Bening Bulanan Plano', 'DwiVision', 'Softlens', 'SFT-DV-CLR30', 98000, 40, 'available', false, [220, 238, 244], [58, 110, 140],
                'Softlens bening plano (tanpa minus) untuk pemakaian bulanan, kadar air 55% — nyaman hingga 8 jam. Harga per pasang. Tersedia juga varian minus hingga -6.00 di toko.'],
            ['Set Perawatan Kacamata & Softlens', 'Kala Eyewear', 'Aksesoris', 'ACC-KL-SET01', 45000, 60, 'available', false, [236, 240, 232], [70, 96, 60],
                'Paket perawatan lengkap: cairan pembersih lensa 100 ml, cairan softlens 60 ml, lap microfiber, case kacamata, dan case softlens. Praktis untuk pemakaian sehari-hari dan perjalanan.'],
        ];

        $generator = new DummyImageGenerator;

        foreach ($products as [$name, $brandName, $categoryName, $sku, $price, $stock, $availability, $featured, $bg, $frame, $description]) {
            $product = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $categories[$categoryName]->id,
                    'brand_id' => $brands[$brandName]->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock,
                    'publication_status' => 'published',
                    'availability_status' => $availability,
                    'is_featured' => $featured,
                ]
            );

            if ($product->images()->count() === 0) {
                $images = $generator->generateFor($product->id, $product->name, $product->sku, ['bg' => $bg, 'frame' => $frame]);
                foreach ($images as $i => $image) {
                    $product->images()->create([
                        'path' => $image['path'],
                        'alt_text' => $image['alt'],
                        'sort_order' => $i,
                    ]);
                }
            }

            if ($product->channels()->count() === 0 && in_array($sku, ['FRM-DV-A1-BLK', 'FRM-AR-B2-TOR', 'LNS-DV-BC156', 'SUN-KL-D4-BLK'], true)) {
                $product->channels()->createMany([
                    ['channel_type' => 'shopee', 'label' => 'Shopee', 'url' => 'https://shopee.co.id/'.Str::slug($name).'-i.889900.'.($product->id * 111), 'is_active' => true],
                    ['channel_type' => 'tokopedia', 'label' => 'Tokopedia', 'url' => 'https://www.tokopedia.com/optikdwiputra/'.Str::slug($name), 'is_active' => true],
                ]);
            }
        }
    }
}

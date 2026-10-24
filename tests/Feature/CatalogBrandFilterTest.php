<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogBrandFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_filter_shows_only_that_brands_products(): void
    {
        $rayban = Brand::factory()->create(['name' => 'Merek Alpha Uji', 'slug' => 'merek-alpha-uji']);
        $oakley = Brand::factory()->create(['name' => 'Merek Beta Uji', 'slug' => 'merek-beta-uji']);
        Product::factory()->create(['brand_id' => $rayban->id, 'name' => 'Produk Alpha Satu']);
        Product::factory()->create(['brand_id' => $oakley->id, 'name' => 'Produk Beta Satu']);

        $response = $this->get(route('catalog', ['merek' => 'merek-alpha-uji']));
        $response->assertOk()
            ->assertSee('Produk Alpha Satu')
            ->assertDontSee('Produk Beta Satu')
            ->assertSee('Menampilkan merek')
            ->assertSee('Merek Alpha Uji');

        // Dropdown filter memuat kedua merek.
        $this->get(route('catalog'))->assertSee('Merek Alpha Uji')->assertSee('Merek Beta Uji');
    }

    public function test_brand_filter_combines_with_category_filter(): void
    {
        $brand = Brand::factory()->create(['slug' => 'merek-gabung-uji']);
        $frame = Category::factory()->create(['name' => 'Kategori Frame Uji', 'slug' => 'kategori-frame-uji']);
        $lensa = Category::factory()->create(['name' => 'Kategori Lensa Uji', 'slug' => 'kategori-lensa-uji']);
        Product::factory()->create(['brand_id' => $brand->id, 'category_id' => $frame->id, 'name' => 'Produk Gabung Cocok']);
        Product::factory()->create(['brand_id' => $brand->id, 'category_id' => $lensa->id, 'name' => 'Produk Gabung Beda Kategori']);

        $this->get(route('catalog', ['merek' => 'merek-gabung-uji', 'kategori' => 'kategori-frame-uji']))
            ->assertOk()
            ->assertSee('Produk Gabung Cocok')
            ->assertDontSee('Produk Gabung Beda Kategori');
    }

    public function test_unknown_brand_slug_is_ignored_like_unknown_category(): void
    {
        Product::factory()->create(['name' => 'Produk Semua Merek']);

        $this->get(route('catalog', ['merek' => 'merek-tidak-ada']))
            ->assertOk()
            ->assertSee('Produk Semua Merek');
    }

    public function test_inactive_brand_and_brand_without_published_products_are_not_offered(): void
    {
        $inactive = Brand::factory()->create(['name' => 'Merek Nonaktif Uji', 'slug' => 'merek-nonaktif-uji', 'is_active' => false]);
        Product::factory()->create(['brand_id' => $inactive->id, 'name' => 'Produk Merek Nonaktif']);

        $draftOnly = Brand::factory()->create(['name' => 'Merek Draft Saja Uji', 'slug' => 'merek-draft-saja-uji']);
        Product::factory()->draft()->create(['brand_id' => $draftOnly->id, 'name' => 'Produk Draft Saja']);

        // Nama merek nonaktif masih bisa muncul lewat kartu produknya, jadi
        // yang diuji adalah opsi dropdown-nya (bukan teks halaman).
        $catalog = $this->get(route('catalog'));
        $catalog->assertDontSee('value="merek-nonaktif-uji"', false)
            ->assertDontSee('value="merek-draft-saja-uji"', false);

        // Slug merek nonaktif tidak memfilter (diabaikan seperti slug tak dikenal).
        $this->get(route('catalog', ['merek' => 'merek-nonaktif-uji']))
            ->assertOk()
            ->assertSee('Produk Merek Nonaktif');
    }

    public function test_products_without_brand_are_excluded_when_brand_filter_active(): void
    {
        $brand = Brand::factory()->create(['slug' => 'merek-saja-uji']);
        Product::factory()->create(['brand_id' => $brand->id, 'name' => 'Produk Bermerek']);
        Product::factory()->create(['brand_id' => null, 'name' => 'Produk Tanpa Merek']);

        $this->get(route('catalog', ['merek' => 'merek-saja-uji']))
            ->assertSee('Produk Bermerek')
            ->assertDontSee('Produk Tanpa Merek');
    }

    public function test_product_detail_brand_name_links_to_brand_filtered_catalog(): void
    {
        $brand = Brand::factory()->create(['name' => 'Merek Taut Uji', 'slug' => 'merek-taut-uji']);
        $product = Product::factory()->create(['brand_id' => $brand->id, 'name' => 'Produk Taut Uji']);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee(route('catalog', ['merek' => 'merek-taut-uji']), false);
    }
}

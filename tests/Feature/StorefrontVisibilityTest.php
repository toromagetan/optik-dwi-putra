<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_product_appears_in_catalog_and_detail(): void
    {
        $product = Product::factory()->create(['name' => 'Frame Uji Terbit']);

        $this->get(route('catalog'))->assertOk()->assertSee('Frame Uji Terbit');
        $this->get(route('product.show', $product->slug))->assertOk()->assertSee('Frame Uji Terbit');
    }

    public function test_draft_and_archived_products_are_hidden(): void
    {
        $draft = Product::factory()->draft()->create(['name' => 'Produk Draft Rahasia']);
        $archived = Product::factory()->archived()->create(['name' => 'Produk Arsip Rahasia']);

        $catalog = $this->get(route('catalog'));
        $catalog->assertOk()->assertDontSee('Produk Draft Rahasia')->assertDontSee('Produk Arsip Rahasia');

        $this->get(route('product.show', $draft->slug))->assertNotFound();
        $this->get(route('product.show', $archived->slug))->assertNotFound();
    }

    public function test_search_matches_name_and_sku(): void
    {
        Product::factory()->create(['name' => 'Lensa Antiradiasi Pro', 'sku' => 'LNS-PRO-1']);
        Product::factory()->create(['name' => 'Frame Biasa Saja', 'sku' => 'FRM-BSA-2']);

        $this->get(route('catalog', ['q' => 'Antiradiasi']))->assertSee('Lensa Antiradiasi Pro')->assertDontSee('Frame Biasa Saja');
        $this->get(route('catalog', ['q' => 'FRM-BSA']))->assertSee('Frame Biasa Saja')->assertDontSee('Lensa Antiradiasi Pro');
    }

    public function test_category_filter_and_inactive_category_hidden(): void
    {
        $active = Category::factory()->create(['name' => 'Kategori Aktif Uji', 'is_active' => true]);
        $inactive = Category::factory()->inactive()->create(['name' => 'Kategori Nonaktif Uji']);
        Product::factory()->create(['category_id' => $active->id, 'name' => 'Produk Di Kategori Aktif']);
        Product::factory()->create(['category_id' => $inactive->id, 'name' => 'Produk Di Kategori Nonaktif']);

        $response = $this->get(route('catalog', ['kategori' => $active->slug]));
        $response->assertSee('Produk Di Kategori Aktif')->assertDontSee('Produk Di Kategori Nonaktif');
        // Chip filter hanya memuat kategori aktif.
        $this->get(route('catalog'))->assertDontSee('Kategori Nonaktif Uji');
    }

    public function test_zero_stock_product_is_never_shown_as_available(): void
    {
        $product = Product::factory()->create([
            'stock' => 0,
            'availability_status' => 'available',
            'name' => 'Produk Stok Kosong',
        ]);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee('Stok Habis')
            ->assertDontSee('Stok di toko:');
    }

    public function test_featured_products_appear_on_home(): void
    {
        Product::factory()->featured()->create(['name' => 'Produk Unggulan Beranda']);
        Product::factory()->create(['name' => 'Produk Biasa Beranda']);

        $this->get(route('home'))->assertSee('Produk Unggulan Beranda');
    }

    public function test_sitemap_lists_only_published_products(): void
    {
        $published = Product::factory()->create();
        $draft = Product::factory()->draft()->create();

        $response = $this->get(route('sitemap'));
        $response->assertOk();
        $this->assertStringContainsString(route('product.show', $published->slug), $response->getContent());
        $this->assertStringNotContainsString(route('product.show', $draft->slug), $response->getContent());
    }
}

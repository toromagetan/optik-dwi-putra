<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StoreSetting;
use Database\Seeders\StoreSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StoreSettingSeeder::class);
    }

    private function validate(array $items)
    {
        return $this->postJson(route('cart.validate'), ['items' => $items]);
    }

    public function test_subtotal_uses_current_database_price(): void
    {
        $product = Product::factory()->create(['price' => 100000, 'stock' => 10]);

        $this->validate([['product_id' => $product->id, 'qty' => 2]])
            ->assertOk()
            ->assertJsonPath('subtotal', 200000);

        // Harga berubah SETELAH item masuk keranjang → subtotal mengikuti harga terbaru.
        $product->update(['price' => 120000]);

        $this->validate([['product_id' => $product->id, 'qty' => 2]])
            ->assertOk()
            ->assertJsonPath('subtotal', 240000)
            ->assertJsonPath('lines.0.price', 120000);
    }

    public function test_empty_cart_validates_to_zero(): void
    {
        $this->validate([])
            ->assertOk()
            ->assertJsonPath('subtotal', 0)
            ->assertJsonPath('lines', [])
            ->assertJsonPath('whatsapp_url', null);
    }

    public function test_unpublished_product_is_removed_with_issue(): void
    {
        $draft = Product::factory()->draft()->create(['name' => 'Produk Sedang Draft']);
        $ok = Product::factory()->create(['price' => 50000]);

        $response = $this->validate([
            ['product_id' => $draft->id, 'qty' => 1],
            ['product_id' => $ok->id, 'qty' => 1],
        ]);

        $response->assertOk()->assertJsonCount(1, 'lines')->assertJsonPath('subtotal', 50000);
        $this->assertStringContainsString('Produk Sedang Draft', implode(' ', $response->json('issues')));
    }

    public function test_out_of_stock_product_is_removed_with_issue(): void
    {
        $product = Product::factory()->create(['stock' => 0, 'availability_status' => 'available', 'name' => 'Produk Kosong Uji']);

        $response = $this->validate([['product_id' => $product->id, 'qty' => 1]]);

        $response->assertOk()->assertJsonCount(0, 'lines')->assertJsonPath('subtotal', 0);
        $this->assertStringContainsString('Produk Kosong Uji', implode(' ', $response->json('issues')));
    }

    public function test_invalid_quantities_are_handled(): void
    {
        $product = Product::factory()->create(['stock' => 50]);

        // qty nol → item dikeluarkan dengan isu.
        $response = $this->validate([['product_id' => $product->id, 'qty' => 0]]);
        $response->assertJsonCount(0, 'lines');
        $this->assertNotEmpty($response->json('issues'));

        // qty melebihi batas per item (cart_max_qty = 10 dari seeder) → dibatasi.
        $response = $this->validate([['product_id' => $product->id, 'qty' => 99]]);
        $response->assertJsonPath('lines.0.qty', 10);
        $this->assertNotEmpty($response->json('issues'));
    }

    public function test_quantity_is_capped_by_available_stock(): void
    {
        $product = Product::factory()->create(['stock' => 3, 'price' => 10000]);

        $this->validate([['product_id' => $product->id, 'qty' => 8]])
            ->assertJsonPath('lines.0.qty', 3)
            ->assertJsonPath('subtotal', 30000);
    }

    public function test_contact_for_availability_product_stays_in_cart(): void
    {
        $product = Product::factory()->contactForAvailability()->create(['stock' => 2, 'price' => 75000]);

        $this->validate([['product_id' => $product->id, 'qty' => 1]])
            ->assertOk()
            ->assertJsonCount(1, 'lines')
            ->assertJsonPath('lines.0.availability', 'contact_for_availability');
    }

    public function test_whatsapp_url_contains_store_number_and_full_summary(): void
    {
        StoreSetting::set('whatsapp_number', '6287771234567');
        $product = Product::factory()->create(['name' => 'Frame WA Uji', 'sku' => 'WA-UJI-1', 'price' => 200000, 'stock' => 5]);

        $response = $this->validate([['product_id' => $product->id, 'qty' => 2]]);
        $response->assertOk();

        $url = $response->json('whatsapp_url');
        $this->assertStringStartsWith('https://wa.me/6287771234567?text=', $url);

        $message = urldecode(parse_url($url, PHP_URL_QUERY));
        $this->assertStringContainsString('Frame WA Uji', $message);
        $this->assertStringContainsString('WA-UJI-1', $message);
        $this->assertStringContainsString('Rp400.000', $message);
    }

    public function test_validation_includes_active_marketplace_channels(): void
    {
        $product = Product::factory()->create();
        $product->channels()->create(['channel_type' => 'shopee', 'label' => null, 'url' => 'https://shopee.co.id/aktif', 'is_active' => true]);
        $product->channels()->create(['channel_type' => 'lazada', 'label' => 'Nonaktif', 'url' => 'https://lazada.co.id/mati', 'is_active' => false]);

        $response = $this->validate([['product_id' => $product->id, 'qty' => 1]]);
        $channels = $response->json('lines.0.channels');

        $this->assertCount(1, $channels);
        $this->assertSame('https://shopee.co.id/aktif', $channels[0]['url']);
        $this->assertTrue($channels[0]['is_marketplace']);
    }
}

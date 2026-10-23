<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->category = Category::factory()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Produk Uji Baru',
            'sku' => 'UJI-001',
            'category_id' => $this->category->id,
            'brand_id' => null,
            'description' => 'Deskripsi produk uji.',
            'price' => 150000,
            'stock' => 7,
            'publication_status' => 'published',
            'availability_status' => 'available',
        ], $overrides);
    }

    public function test_admin_creates_product_with_audit_record(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['sku' => 'UJI-001', 'price' => 150000, 'stock' => 7]);

        $product = Product::where('sku', 'UJI-001')->first();
        $this->assertNotNull($product);
        $this->assertSame('produk-uji-baru', $product->slug);
        $this->assertTrue(AuditLog::where('action', 'product.created')->where('entity_id', $product->id)->exists());
    }

    public function test_price_and_stock_changes_are_audited(): void
    {
        $product = Product::factory()->create(['price' => 100000, 'stock' => 5]);

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), $this->payload([
            'name' => $product->name,
            'sku' => $product->sku,
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'price' => 175000,
            'stock' => 2,
            'publication_status' => $product->publication_status,
            'availability_status' => $product->availability_status,
        ]))->assertRedirect();

        $log = AuditLog::where('action', 'product.updated')->where('entity_id', $product->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame([100000, 175000], array_map('intval', $log->changes['price']));
        $this->assertSame([5, 2], array_map('intval', $log->changes['stock']));
    }

    public function test_invalid_product_data_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload(['price' => -5, 'stock' => -1]))
            ->assertSessionHasErrors(['price', 'stock']);

        $this->assertDatabaseMissing('products', ['sku' => 'UJI-001']);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        Product::factory()->create(['sku' => 'UJI-001']);

        $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $this->payload())
            ->assertSessionHasErrors('sku');
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        Product::factory()->create(['category_id' => $this->category->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $this->category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }

    public function test_delete_archives_product_instead_of_removing(): void
    {
        $product = Product::factory()->create(['publication_status' => 'published']);

        $this->actingAs($this->admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'publication_status' => 'archived']);
        $this->assertTrue(AuditLog::where('action', 'product.archived')->where('entity_id', $product->id)->exists());
    }

    public function test_quick_status_change_publishes_draft(): void
    {
        $product = Product::factory()->draft()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.products.status', $product), ['publication_status' => 'published'])
            ->assertRedirect();

        $this->assertSame('published', $product->fresh()->publication_status);
    }

    public function test_guest_cannot_create_product(): void
    {
        $this->post(route('admin.products.store'), $this->payload())->assertRedirect(route('login'));
        $this->assertDatabaseMissing('products', ['sku' => 'UJI-001']);
    }
}

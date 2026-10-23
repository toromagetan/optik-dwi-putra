<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'brand'])
            ->withCount('images')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q');
                $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('publication_status', $request->string('status')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['publication_status' => 'draft', 'availability_status' => 'available']),
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $product = Product::create($data);
        AuditLog::record('product.created', $product, [
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
        ]);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Produk berhasil dibuat. Tambahkan gambar dan tautan kanal di bawah ini.');
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'channels']);

        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);

        // Perubahan penting yang wajib teraudit (AD-11): harga, stok, status.
        $changes = [];
        foreach (['price', 'stock', 'publication_status', 'availability_status'] as $field) {
            if ((string) $product->{$field} !== (string) $data[$field]) {
                $changes[$field] = [$product->{$field}, $data[$field]];
            }
        }

        $product->update($data);

        if ($changes !== []) {
            AuditLog::record('product.updated', $product, $changes);
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'Produk berhasil diperbarui.');
    }

    /** Aksi cepat ubah status publikasi dari daftar produk. */
    public function setStatus(Request $request, Product $product)
    {
        $data = $request->validate([
            'publication_status' => ['required', Rule::in(Product::PUBLICATION_STATUSES)],
        ]);

        $old = $product->publication_status;
        $product->update(['publication_status' => $data['publication_status']]);
        AuditLog::record('product.status_changed', $product, ['publication_status' => [$old, $data['publication_status']]]);

        return back()->with('success', "Status {$product->name} diubah menjadi {$data['publication_status']}.");
    }

    /** Produk tidak dihapus permanen — diarsipkan (blueprint §4). */
    public function destroy(Product $product)
    {
        $old = $product->publication_status;
        $product->update(['publication_status' => 'archived']);
        AuditLog::record('product.archived', $product, ['publication_status' => [$old, 'archived']]);

        return redirect()->route('admin.products.index')->with('success', "Produk {$product->name} diarsipkan (tidak dihapus permanen).");
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product?->id)],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'publication_status' => ['required', Rule::in(Product::PUBLICATION_STATUSES)],
            'availability_status' => ['required', Rule::in(Product::AVAILABILITY_STATUSES)],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['sku'] = strtoupper(trim($data['sku']));
        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }
}

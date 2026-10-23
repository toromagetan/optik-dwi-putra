<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::active()->orderBy('sort_order')->orderBy('name')->get();
        $activeCategory = $request->filled('kategori')
            ? $categories->firstWhere('slug', $request->string('kategori'))
            : null;

        $products = Product::published()
            ->with(['images', 'brand', 'category'])
            ->when($activeCategory, fn ($q) => $q->where('category_id', $activeCategory->id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($w) => $w
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%"));
            })
            ->when($request->string('urut') === 'harga-rendah', fn ($q) => $q->orderBy('price'))
            ->when($request->string('urut') === 'harga-tinggi', fn ($q) => $q->orderByDesc('price'))
            ->when($request->string('urut') === 'nama', fn ($q) => $q->orderBy('name'))
            ->when(! $request->filled('urut'), fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        return view('storefront.catalog', compact('products', 'categories', 'activeCategory'));
    }

    public function show(string $slug)
    {
        $product = Product::published()
            ->with(['images', 'brand', 'category', 'activeChannels'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::published()
            ->with(['images', 'brand'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        return view('storefront.product', compact('product', 'related'));
    }
}

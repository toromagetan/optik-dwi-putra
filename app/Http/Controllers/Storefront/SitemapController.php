<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [
            ['loc' => route('home')],
            ['loc' => route('catalog')],
        ];

        foreach (Category::active()->get() as $category) {
            $urls[] = ['loc' => route('catalog', ['kategori' => $category->slug])];
        }

        foreach (Product::published()->get(['slug', 'updated_at']) as $product) {
            $urls[] = [
                'loc' => route('product.show', $product->slug),
                'lastmod' => $product->updated_at?->toAtomString(),
            ];
        }

        return response()
            ->view('storefront.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }
}

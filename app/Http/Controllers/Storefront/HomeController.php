<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Product::published()->featured()
            ->with(['images', 'brand', 'category'])
            ->latest()
            ->take(8)
            ->get();

        $latest = Product::published()
            ->with(['images', 'brand', 'category'])
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::active()
            ->withCount('publishedProducts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('storefront.home', [
            'featured' => $featured,
            'latest' => $latest,
            'categories' => $categories,
            'promoText' => StoreSetting::get('promo_text'),
            'banners' => Banner::active()->get(),
        ]);
    }
}

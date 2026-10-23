<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => Product::count(),
            'published' => Product::where('publication_status', 'published')->count(),
            'draft' => Product::where('publication_status', 'draft')->count(),
            'archived' => Product::where('publication_status', 'archived')->count(),
            'out_of_stock' => Product::published()
                ->where(fn ($q) => $q->where('stock', '<=', 0)->orWhere('availability_status', 'out_of_stock'))
                ->count(),
            'categories' => Category::count(),
            'brands' => Brand::count(),
        ];

        $lowStock = Product::published()
            ->where('stock', '>', 0)
            ->where('stock', '<=', 3)
            ->orderBy('stock')
            ->take(6)
            ->get();

        $recentActivity = AuditLog::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact('stats', 'lowStock', 'recentActivity'));
    }
}

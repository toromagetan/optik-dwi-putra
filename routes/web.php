<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductChannelController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\SitemapController;
use Illuminate\Support\Facades\Route;

// ---------- Storefront (publik) ----------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [CatalogController::class, 'index'])->name('catalog');
Route::get('/produk/{slug}', [CatalogController::class, 'show'])->name('product.show');
Route::get('/keranjang', [CartController::class, 'index'])->name('cart');
Route::post('/keranjang/validasi', [CartController::class, 'validateCart'])->name('cart.validate');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// ---------- Autentikasi admin ----------
Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
Route::post('/masuk', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ---------- Panel admin (Blade kustom) ----------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('products/{product}/status', [ProductController::class, 'setStatus'])->name('products.status');

    Route::post('products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::post('products/{product}/images/{image}/move', [ProductImageController::class, 'move'])->name('products.images.move');
    Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');

    Route::post('products/{product}/channels', [ProductChannelController::class, 'store'])->name('products.channels.store');
    Route::put('products/{product}/channels/{channel}', [ProductChannelController::class, 'update'])->name('products.channels.update');
    Route::delete('products/{product}/channels/{channel}', [ProductChannelController::class, 'destroy'])->name('products.channels.destroy');

    Route::resource('banners', BannerController::class)->except(['show']);
    Route::post('banners/{banner}/move', [BannerController::class, 'move'])->name('banners.move');

    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('brands', BrandController::class)->except(['show']);

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

<?php

namespace App\Providers;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bagikan pengaturan toko ke semua view (nama, logo, kontak, kanal).
        View::composer('*', function ($view) {
            if (! Schema::hasTable('store_settings')) {
                $view->with('store', []);

                return;
            }

            $store = [];
            foreach (StoreSetting::REGISTRY as $key => $meta) {
                $store[$key] = StoreSetting::get($key);
            }
            $store['logo_url'] = ! empty($store['logo_path'])
                ? Storage::disk('public')->url($store['logo_path'])
                : null;

            $view->with('store', $store);
        });
    }
}

<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ProductChannel;
use App\Models\StoreSetting;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('storefront.cart');
    }

    /**
     * Validasi isi keranjang dari browser terhadap database terbaru.
     * Mengembalikan baris tervalidasi, subtotal, isu, dan tautan handoff.
     */
    public function validateCart(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'items' => ['present', 'array', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.qty' => ['required', 'integer'],
        ]);

        $validated = $cart->validate($data['items']);

        // Sertakan tautan marketplace aktif per produk untuk handoff per produk.
        $ids = collect($validated['lines'])->pluck('product_id');
        $channels = ProductChannel::whereIn('product_id', $ids)
            ->where('is_active', true)
            ->get()
            ->groupBy('product_id');

        $validated['lines'] = collect($validated['lines'])->map(function ($line) use ($channels) {
            $line['channels'] = $channels->get($line['product_id'], collect())
                ->map(fn ($c) => [
                    'type' => $c->channel_type,
                    'label' => $c->displayLabel(),
                    'url' => $c->url,
                    'is_marketplace' => $c->isMarketplace(),
                ])->values()->all();

            return $line;
        })->all();

        return response()->json([
            'lines' => $validated['lines'],
            'subtotal' => $validated['subtotal'],
            'subtotal_formatted' => $validated['subtotal_formatted'],
            'issues' => $validated['issues'],
            'whatsapp_url' => $validated['lines'] !== [] ? $cart->whatsAppUrl($validated) : null,
            'whatsapp_text' => $validated['lines'] !== [] ? $cart->composeWhatsAppMessage($validated) : null,
            'telegram_url' => StoreSetting::get('telegram_url'),
        ]);
    }
}

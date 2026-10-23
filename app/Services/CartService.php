<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StoreSetting;

/**
 * Validasi keranjang terhadap data backend terbaru (PRD §6.3):
 * browser hanya menyimpan product_id + qty; harga, stok, dan status
 * selalu diambil ulang dari database di sini.
 */
class CartService
{
    /**
     * @param  array<int, array{product_id: mixed, qty: mixed}>  $items
     * @return array{lines: array<int, array<string, mixed>>, subtotal: int, issues: array<int, string>, has_issues: bool}
     */
    public function validate(array $items): array
    {
        $maxQty = max(1, (int) (StoreSetting::get('cart_max_qty') ?? 10));

        $ids = collect($items)
            ->map(fn ($i) => (int) ($i['product_id'] ?? 0))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $products = Product::with(['images', 'brand'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $lines = [];
        $issues = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $qty = (int) ($item['qty'] ?? 0);

            if ($productId <= 0) {
                continue;
            }

            $product = $products->get($productId);

            if (! $product) {
                $issues[] = 'Sebuah produk di keranjang sudah tidak ditemukan dan dikeluarkan.';

                continue;
            }

            if ($qty <= 0) {
                $issues[] = "Kuantitas {$product->name} tidak valid dan item dikeluarkan.";

                continue;
            }

            if ($qty > $maxQty) {
                $issues[] = "Kuantitas {$product->name} dibatasi maksimal {$maxQty} per item.";
                $qty = $maxQty;
            }

            if ($product->publication_status !== 'published') {
                $issues[] = "{$product->name} sedang tidak dijual dan dikeluarkan dari keranjang.";

                continue;
            }

            $availability = $product->effectiveAvailability();

            if ($availability === 'out_of_stock') {
                $issues[] = "Stok {$product->name} sedang habis dan dikeluarkan dari keranjang.";

                continue;
            }

            if ($qty > $product->stock && $product->stock > 0) {
                $issues[] = "Stok {$product->name} tersisa {$product->stock}; kuantitas disesuaikan.";
                $qty = $product->stock;
            }

            $lineTotal = $product->price * $qty;
            $subtotal += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'slug' => $product->slug,
                'brand' => $product->brand?->name,
                'image' => $product->mainImage()?->url(),
                'price' => $product->price,
                'price_formatted' => $product->formattedPrice(),
                'qty' => $qty,
                'line_total' => $lineTotal,
                'line_total_formatted' => 'Rp'.number_format($lineTotal, 0, ',', '.'),
                'availability' => $availability,
                'stock' => $product->stock,
            ];
        }

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'Rp'.number_format($subtotal, 0, ',', '.'),
            'issues' => array_values(array_unique($issues)),
            'has_issues' => $issues !== [],
        ];
    }

    /**
     * Susun pesan WhatsApp dari hasil validasi (PRD §6.3 butir 6).
     */
    public function composeWhatsAppMessage(array $validated): string
    {
        $store = StoreSetting::get('store_name');
        $rows = ["Halo {$store}, saya ingin memesan:"];

        foreach ($validated['lines'] as $line) {
            $rows[] = "- {$line['name']} (SKU: {$line['sku']}) x{$line['qty']} — {$line['line_total_formatted']}";
        }

        $rows[] = '';
        $rows[] = "Subtotal referensi: {$validated['subtotal_formatted']}";
        $rows[] = 'Mohon konfirmasi ketersediaan stok dan harga final. Terima kasih.';

        return implode("\n", $rows);
    }

    /** Ringkasan teks untuk disalin (jalur Telegram, PRD §6.3 butir 7). */
    public function composeCopySummary(array $validated): string
    {
        return $this->composeWhatsAppMessage($validated);
    }

    public function whatsAppUrl(array $validated): ?string
    {
        $number = preg_replace('/\D+/', '', (string) StoreSetting::get('whatsapp_number'));

        if (! $number) {
            return null;
        }

        return 'https://wa.me/'.$number.'?text='.urlencode($this->composeWhatsAppMessage($validated));
    }
}

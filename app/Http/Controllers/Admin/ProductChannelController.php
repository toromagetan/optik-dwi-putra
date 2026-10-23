<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductChannel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductChannelController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $this->validated($request);
        $channel = $product->channels()->create($data);
        AuditLog::record('product.channel_added', $product, ['channel_id' => $channel->id, 'type' => $channel->channel_type]);

        return back()->with('success', 'Tautan kanal berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product, ProductChannel $channel)
    {
        abort_if($channel->product_id !== $product->id, 404);

        $channel->update($this->validated($request));
        AuditLog::record('product.channel_updated', $product, ['channel_id' => $channel->id, 'type' => $channel->channel_type]);

        return back()->with('success', 'Tautan kanal berhasil diperbarui.');
    }

    public function destroy(Product $product, ProductChannel $channel)
    {
        abort_if($channel->product_id !== $product->id, 404);

        AuditLog::record('product.channel_deleted', $product, ['channel_id' => $channel->id, 'type' => $channel->channel_type]);
        $channel->delete();

        return back()->with('success', 'Tautan kanal berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'channel_type' => ['required', Rule::in(array_keys(ProductChannel::TYPES))],
            'label' => ['nullable', 'string', 'max:100'],
            // Hanya http/https — menolak skema berbahaya seperti javascript: (AD/AC).
            'url' => ['required', 'string', 'max:2048', 'url', 'starts_with:http://,https://'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

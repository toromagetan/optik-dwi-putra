<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('image');

        // Verifikasi MIME nyata berkas, bukan hanya ekstensi (skenario uji wajib).
        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return back()->with('error', 'Berkas yang diunggah bukan gambar yang valid.');
        }

        $name = Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs("products/{$product->id}", $name, 'public');

        $image = $product->images()->create([
            'path' => $path,
            'alt_text' => $request->input('alt_text') ?: $product->name,
            'sort_order' => (int) $product->images()->max('sort_order') + 1,
        ]);

        AuditLog::record('product.image_added', $product, ['image_id' => $image->id, 'path' => $path]);

        return back()->with('success', 'Gambar berhasil ditambahkan.');
    }

    public function move(Request $request, Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);

        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $images = $product->images()->get()->values();
        $index = $images->search(fn ($i) => $i->id === $image->id);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && isset($images[$swapIndex])) {
            $other = $images[$swapIndex];
            [$image->sort_order, $other->sort_order] = [$other->sort_order, $image->sort_order];
            $image->save();
            $other->save();
        }

        return back()->with('success', 'Urutan gambar diperbarui. Gambar pertama menjadi gambar utama.');
    }

    public function destroy(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);

        Storage::disk('public')->delete($image->path);
        AuditLog::record('product.image_deleted', $product, ['image_id' => $image->id, 'path' => $image->path]);
        $image->delete();

        return back()->with('success', 'Gambar berhasil dihapus.');
    }
}

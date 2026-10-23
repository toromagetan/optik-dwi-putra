@extends('layouts.admin')
@section('title', 'Produk')

@section('content')
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU…"
                class="min-w-52 flex-1 rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100">
            <select name="status" class="rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm font-medium outline-none focus:border-brand-600">
                <option value="">Semua status</option>
                @foreach(\App\Models\Product::PUBLICATION_STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select name="category_id" class="rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm font-medium outline-none focus:border-brand-600">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-stone-800 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-stone-900">Filter</button>
        </form>
        <a href="{{ route('admin.products.create') }}" class="rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">+ Tambah Produk</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[880px] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                        <th class="px-4 py-3 font-bold">Produk</th>
                        <th class="px-4 py-3 font-bold">Kategori</th>
                        <th class="px-4 py-3 font-bold">Harga</th>
                        <th class="px-4 py-3 font-bold">Stok</th>
                        <th class="px-4 py-3 font-bold">Ketersediaan</th>
                        <th class="px-4 py-3 font-bold">Publikasi</th>
                        <th class="px-4 py-3 text-right font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($products as $product)
                        <tr class="transition hover:bg-brand-50/40">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($product->mainImage())
                                        <img src="{{ $product->mainImage()->url() }}" alt="" class="h-11 w-11 rounded-lg object-cover ring-1 ring-stone-200">
                                    @else
                                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-stone-100 text-[10px] font-bold text-stone-400">FOTO</span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-brand-950">{{ $product->name }} @if($product->is_featured)<span class="text-gold-500" title="Unggulan">★</span>@endif</p>
                                        <p class="text-xs text-stone-400">SKU {{ $product->sku }} · {{ $product->images_count }} foto</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-stone-600">{{ $product->category?->name }}<span class="block text-xs text-stone-400">{{ $product->brand?->name }}</span></td>
                            <td class="px-4 py-3 font-bold text-stone-700">{{ $product->formattedPrice() }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-extrabold {{ $product->stock <= 0 ? 'bg-red-100 text-red-700' : ($product->stock <= 3 ? 'bg-amber-100 text-amber-700' : 'bg-brand-100 text-brand-800') }}">{{ $product->stock }}</span>
                            </td>
                            <td class="px-4 py-3 text-stone-600">{{ ['available' => 'Tersedia', 'out_of_stock' => 'Habis', 'contact_for_availability' => 'Tanya dulu'][$product->effectiveAvailability()] }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-extrabold {{ $product->publication_status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($product->publication_status === 'draft' ? 'bg-amber-100 text-amber-700' : 'bg-stone-200 text-stone-600') }}">{{ ucfirst($product->publication_status) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg bg-brand-700 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-brand-800">Ubah</a>
                                    @if($product->publication_status !== 'published')
                                        <form method="POST" action="{{ route('admin.products.status', $product) }}">@csrf<input type="hidden" name="publication_status" value="published"><button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700">Terbitkan</button></form>
                                    @endif
                                    @if($product->publication_status === 'published')
                                        <form method="POST" action="{{ route('admin.products.status', $product) }}">@csrf<input type="hidden" name="publication_status" value="draft"><button class="rounded-lg bg-stone-200 px-3 py-1.5 text-xs font-bold text-stone-600 transition hover:bg-stone-300">Draft</button></form>
                                    @endif
                                    @if($product->publication_status !== 'archived')
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Arsipkan produk ini? Produk tidak dihapus permanen dan bisa diterbitkan lagi.')">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 ring-1 ring-red-200 transition hover:bg-red-100">Arsipkan</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-stone-500">Belum ada produk yang cocok. <a href="{{ route('admin.products.create') }}" class="font-bold text-brand-700">Tambah produk pertama →</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5">{{ $products->links() }}</div>
@endsection

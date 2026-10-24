@extends('layouts.storefront')

@section('title', 'Katalog Produk')
@section('meta_description', 'Katalog lengkap '.($store['store_name'] ?? 'Optik Dwi Putra').': kacamata, frame, lensa, sunglasses, softlens, dan aksesori optik.')

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-8 lg:px-8">
        <h1 class="text-2xl font-extrabold text-brand-950">Katalog Produk</h1>
        <p class="mt-1 text-sm text-stone-500">
            @if($activeCategory)
                Menampilkan kategori <strong class="text-brand-800">{{ $activeCategory->name }}</strong> —
            @endif
            @if($activeBrand)
                Menampilkan merek <strong class="text-brand-800">{{ $activeBrand->name }}</strong> —
            @endif
            {{ $products->total() }} produk ditemukan.
        </p>

        {{-- Filter kategori (mempertahankan filter merek yang aktif) --}}
        <div class="no-scrollbar -mx-4 mt-5 flex gap-2 overflow-x-auto px-4 pb-1">
            <a href="{{ route('catalog', array_filter(['merek' => $activeBrand?->slug, 'q' => request('q'), 'urut' => request('urut')])) }}"
                class="shrink-0 rounded-full px-4 py-2 text-sm font-bold transition {{ !$activeCategory ? 'bg-brand-700 text-white shadow' : 'border border-stone-300 bg-white text-stone-600 hover:border-brand-400' }}">Semua</a>
            @foreach($categories as $category)
                <a href="{{ route('catalog', array_filter(['kategori' => $category->slug, 'merek' => $activeBrand?->slug, 'q' => request('q'), 'urut' => request('urut')])) }}"
                    class="shrink-0 rounded-full px-4 py-2 text-sm font-bold transition {{ $activeCategory?->id === $category->id ? 'bg-brand-700 text-white shadow' : 'border border-stone-300 bg-white text-stone-600 hover:border-brand-400' }}">{{ $category->name }}</a>
            @endforeach
        </div>

        {{-- Pencarian + filter merek + urutan --}}
        <form method="GET" class="mt-4 flex flex-col gap-3 sm:flex-row">
            @if($activeCategory)<input type="hidden" name="kategori" value="{{ $activeCategory->slug }}">@endif
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama produk, SKU, atau kata kunci…"
                class="w-full flex-1 rounded-full border border-stone-300 bg-white px-5 py-2.5 text-sm outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100">
            <select name="merek" class="rounded-full border border-stone-300 bg-white px-4 py-2.5 text-sm font-medium outline-none focus:border-brand-600">
                <option value="">Semua merek</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->slug }}" @selected($activeBrand?->id === $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
            <select name="urut" onchange="this.form.submit()" class="rounded-full border border-stone-300 bg-white px-4 py-2.5 text-sm font-medium outline-none focus:border-brand-600">
                <option value="">Urutan: Terbaru</option>
                <option value="harga-rendah" @selected(request('urut') === 'harga-rendah')>Harga terendah</option>
                <option value="harga-tinggi" @selected(request('urut') === 'harga-tinggi')>Harga tertinggi</option>
                <option value="nama" @selected(request('urut') === 'nama')>Nama A–Z</option>
            </select>
            <button type="submit" class="rounded-full bg-brand-700 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-brand-800">Cari</button>
        </form>

        @if($products->isNotEmpty())
            <div class="mt-8 grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 xl:grid-cols-4">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @else
            <div class="mt-6 rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-14 text-center">
                <p class="font-bold text-brand-950">Tidak ada produk yang cocok</p>
                <p class="mt-1 text-sm text-stone-500">Coba kata kunci lain atau jelajahi kategori dan merek lainnya.</p>
                <a href="{{ route('catalog') }}" class="mt-4 inline-block rounded-full bg-brand-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-800">Lihat Semua Produk</a>
            </div>
        @endif
    </section>
@endsection

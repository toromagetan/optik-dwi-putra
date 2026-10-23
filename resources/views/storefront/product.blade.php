@extends('layouts.storefront')

@section('title', $product->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 150) ?: $product->name.' — '.$product->formattedPrice().' di '.($store['store_name'] ?? 'Optik Dwi Putra'))
@section('canonical', route('product.show', $product->slug))

@section('content')
@php($availability = $product->effectiveAvailability())
<section class="mx-auto max-w-7xl px-4 pt-6 lg:px-8">
    <nav class="flex flex-wrap items-center gap-1.5 text-[13px] font-medium text-stone-500">
        <a href="{{ route('home') }}" class="hover:text-brand-700">Beranda</a>
        <span>/</span>
        <a href="{{ route('catalog') }}" class="hover:text-brand-700">Katalog</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('catalog', ['kategori' => $product->category->slug]) }}" class="hover:text-brand-700">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span class="text-brand-900">{{ \Illuminate\Support\Str::limit($product->name, 40) }}</span>
    </nav>

    <div class="mt-5 grid gap-8 lg:grid-cols-2">
        {{-- Galeri --}}
        <div>
            <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                @if($product->mainImage())
                    <img id="gallery-main" src="{{ $product->mainImage()->url() }}" alt="{{ $product->mainImage()->alt_text ?? $product->name }}" class="aspect-[4/3] w-full object-cover">
                @else
                    <div class="flex aspect-[4/3] w-full items-center justify-center bg-stone-100 text-stone-300">
                        <svg class="h-20 w-20" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    </div>
                @endif
            </div>
            @if($product->images->count() > 1)
                <div class="mt-3 flex gap-3 overflow-x-auto pb-1">
                    @foreach($product->images as $image)
                        <button type="button" onclick="document.getElementById('gallery-main').src = @js($image->url())"
                            class="shrink-0 overflow-hidden rounded-xl border-2 border-transparent bg-white shadow-sm transition hover:border-brand-500 focus:border-brand-600">
                            <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $product->name }}" class="h-20 w-24 object-cover" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Info produk --}}
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-stone-400">{{ $product->brand?->name ?? 'Tanpa merek' }} · SKU {{ $product->sku }}</p>
            <h1 class="mt-2 text-2xl font-extrabold leading-tight text-brand-950 sm:text-3xl">{{ $product->name }}</h1>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <span class="rounded-full px-3 py-1.5 text-xs font-extrabold
                    {{ $availability === 'available' ? 'bg-brand-600 text-white' : ($availability === 'contact_for_availability' ? 'bg-amber-500 text-white' : 'bg-red-600 text-white') }}">
                    {{ $availability === 'available' ? 'Tersedia' : ($availability === 'contact_for_availability' ? 'Tanya Ketersediaan Dulu' : 'Stok Habis') }}
                </span>
                @if($availability === 'available')
                    <span class="text-sm font-medium text-stone-500">Stok di toko: {{ $product->stock }} pcs</span>
                @endif
            </div>

            <p class="mt-5 text-3xl font-extrabold text-brand-700">{{ $product->formattedPrice() }}</p>
            <p class="mt-1 text-xs text-stone-400">Harga referensi — konfirmasi harga final & stok ke admin saat pemesanan.</p>

            @if($product->description)
                <div class="prose-sm mt-6 max-w-none leading-relaxed text-stone-600">{!! nl2br(e($product->description)) !!}</div>
            @endif

            <div class="mt-7 flex flex-wrap items-stretch gap-3">
                <div class="flex items-center rounded-xl border border-stone-300 bg-white">
                    <button type="button" onclick="stepQty(-1)" class="px-4 py-3 text-lg font-bold text-stone-500 hover:text-brand-700">−</button>
                    <input id="qty-input" type="number" value="1" min="1" max="999" class="w-14 border-0 text-center text-sm font-bold outline-none">
                    <button type="button" onclick="stepQty(1)" class="px-4 py-3 text-lg font-bold text-stone-500 hover:text-brand-700">+</button>
                </div>
                <button type="button" @if($availability === 'out_of_stock') disabled @endif
                    onclick="addToCart({{ $product->id }}, Math.max(1, parseInt(document.getElementById('qty-input').value) || 1), @js($product->name))"
                    class="flex-1 rounded-xl px-6 py-3 text-sm font-extrabold transition sm:flex-none sm:px-8 {{ $availability === 'out_of_stock' ? 'cursor-not-allowed bg-stone-200 text-stone-400' : 'bg-brand-700 text-white shadow hover:bg-brand-800' }}">
                    {{ $availability === 'out_of_stock' ? 'Stok Habis' : '+ Masukkan Keranjang' }}
                </button>
                @if($availability !== 'out_of_stock')
                    <button type="button"
                        onclick="addToCart({{ $product->id }}, Math.max(1, parseInt(document.getElementById('qty-input').value) || 1), @js($product->name)); window.location.href = '{{ route('cart') }}';"
                        class="rounded-xl border-2 border-brand-700 px-6 py-3 text-sm font-extrabold text-brand-800 transition hover:bg-brand-50">
                        Beli Sekarang
                    </button>
                @endif
            </div>

            {{-- Kanal marketplace produk ini --}}
            @php($marketplaceChannels = $product->activeChannels->filter(fn ($c) => $c->isMarketplace()))
            @if($marketplaceChannels->isNotEmpty())
                <div class="mt-8 rounded-2xl border border-stone-200 bg-white p-5">
                    <p class="text-sm font-extrabold text-brand-950">Beli produk ini di marketplace</p>
                    <p class="mt-0.5 text-xs text-stone-500">Tautan resmi ke halaman produk di marketplace pilihan toko.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($marketplaceChannels as $channel)
                            <a href="{{ $channel->url }}" target="_blank" rel="noopener"
                                class="rounded-full bg-orange-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600">{{ $channel->displayLabel() }} ↗</a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($store['free_consultation_note']))
                <p class="mt-5 rounded-xl bg-brand-50 px-4 py-3 text-[13px] font-medium text-brand-800 ring-1 ring-brand-100">{{ $store['free_consultation_note'] }}</p>
            @endif
        </div>
    </div>

    @if($related->isNotEmpty())
        <section class="mt-14">
            <h2 class="text-xl font-extrabold text-brand-950">Produk Terkait</h2>
            <div class="mt-5 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach($related as $rel)
                    <x-product-card :product="$rel" />
                @endforeach
            </div>
        </section>
    @endif
</section>

@push('scripts')
<script>
    function stepQty(delta) {
        const input = document.getElementById('qty-input');
        const next = Math.min(999, Math.max(1, (parseInt(input.value) || 1) + delta));
        input.value = next;
    }
</script>
@endpush
@endsection

@extends('layouts.storefront')

@section('title', ($store['store_name'] ?? 'Optik Dwi Putra'))

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-700 text-white">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-gold-500/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 left-1/3 h-80 w-80 rounded-full bg-brand-400/20 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:py-20 lg:px-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-gold-400">Katalog Resmi</p>
            <h1 class="mt-3 max-w-2xl text-3xl font-extrabold leading-tight sm:text-5xl">{{ $store['store_name'] ?? 'Optik Dwi Putra' }}</h1>
            <p class="mt-3 max-w-xl text-base text-brand-100 sm:text-lg">{{ $store['tagline'] ?? 'Kacamata & lensa berkualitas untuk penglihatan yang lebih jernih.' }}</p>
            @if(!empty($store['about']))
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-brand-200/90">{{ \Illuminate\Support\Str::limit($store['about'], 200) }}</p>
            @endif
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('catalog') }}" class="rounded-full bg-white px-6 py-3 text-sm font-extrabold text-brand-900 shadow-lg transition hover:bg-brand-50">Lihat Katalog</a>
                @if(!empty($store['whatsapp_number']))
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', $store['whatsapp_number']) }}" target="_blank" rel="noopener" class="rounded-full border border-white/40 px-6 py-3 text-sm font-bold text-white transition hover:bg-white/10">Chat WhatsApp</a>
                @endif
            </div>
            @if(!empty($store['free_consultation_note']))
                <p class="mt-6 inline-flex rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold text-brand-100 ring-1 ring-white/20">{{ $store['free_consultation_note'] }}</p>
            @endif
        </div>
    </section>

    {{-- Banner Promo besar (slider) — pola versi sebelumnya, bahasa desain saat ini --}}
    @if($banners->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-8 lg:px-8">
            <div id="promo-slider" class="group relative aspect-[16/9] w-full overflow-hidden rounded-3xl bg-brand-950 shadow-lg sm:aspect-[21/8] lg:aspect-[21/6]">
                <div id="promo-track" class="flex h-full transition-transform duration-700 ease-in-out">
                    @foreach($banners as $banner)
                        <div class="relative h-full w-full flex-shrink-0">
                            @if($banner->link_url)<a href="{{ $banner->link_url }}" class="block h-full w-full">@endif
                                @if($banner->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($banner->image_path) }}" alt="{{ $banner->title }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full bg-gradient-to-br from-brand-900 via-brand-800 to-brand-600"></div>
                                @endif
                                @if($banner->title || $banner->subtitle)
                                    <div class="pointer-events-none absolute inset-0 flex items-center bg-gradient-to-r from-brand-950/85 via-brand-950/45 to-brand-950/10 sm:from-brand-950/70 sm:via-brand-950/30 sm:to-transparent">
                                        <div class="max-w-2xl px-6 sm:px-10 lg:px-14">
                                            @if($banner->title)
                                                <h2 class="text-2xl font-black uppercase leading-tight tracking-wide text-white drop-shadow-lg sm:text-4xl lg:text-5xl">{{ $banner->title }}</h2>
                                            @endif
                                            @if($banner->subtitle)
                                                <p class="mt-2 text-sm font-medium text-brand-50/90 sm:text-base">{{ $banner->subtitle }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @if($banner->link_url)</a>@endif
                        </div>
                    @endforeach
                </div>

                @if($banners->count() > 1)
                    <button id="promo-prev" type="button" aria-label="Banner sebelumnya" class="absolute left-4 top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-brand-950 opacity-0 shadow-lg backdrop-blur transition hover:bg-white group-hover:opacity-100 md:flex">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button id="promo-next" type="button" aria-label="Banner berikutnya" class="absolute right-4 top-1/2 z-10 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-brand-950 opacity-0 shadow-lg backdrop-blur transition hover:bg-white group-hover:opacity-100 md:flex">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <div class="absolute bottom-4 left-1/2 z-10 flex -translate-x-1/2 gap-1.5">
                        @foreach($banners as $i => $banner)
                            <button type="button" data-dot="{{ $i }}" aria-label="Ke banner {{ $i + 1 }}" class="h-2 w-2 rounded-full bg-white/40 transition-all"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        <script>
        (function () {
            const slider = document.getElementById('promo-slider');
            const track = document.getElementById('promo-track');
            if (!slider || !track) return;
            const total = track.children.length;
            const dots = slider.querySelectorAll('[data-dot]');
            let cur = 0, timer = null, touchX = null;
            function go(i) {
                cur = ((i % total) + total) % total;
                track.style.transform = 'translateX(-' + (cur * 100) + '%)';
                dots.forEach(function (d, j) {
                    d.classList.toggle('bg-white', j === cur);
                    d.classList.toggle('w-6', j === cur);
                    d.classList.toggle('bg-white/40', j !== cur);
                });
            }
            function start() { stop(); if (total > 1) timer = setInterval(function () { go(cur + 1); }, 6000); }
            function stop() { if (timer) clearInterval(timer); timer = null; }
            slider.addEventListener('mouseenter', stop);
            slider.addEventListener('mouseleave', start);
            slider.addEventListener('touchstart', function (e) { touchX = e.touches[0].clientX; stop(); }, { passive: true });
            slider.addEventListener('touchend', function (e) {
                if (touchX !== null) {
                    const dx = e.changedTouches[0].clientX - touchX;
                    if (Math.abs(dx) > 40) go(cur + (dx < 0 ? 1 : -1));
                    touchX = null;
                }
                start();
            }, { passive: true });
            const prev = document.getElementById('promo-prev');
            const next = document.getElementById('promo-next');
            if (prev) prev.addEventListener('click', function () { go(cur - 1); });
            if (next) next.addEventListener('click', function () { go(cur + 1); });
            dots.forEach(function (d, j) { d.addEventListener('click', function () { go(j); }); });
            go(0); start();
        })();
        </script>
    @endif

    {{-- Kategori --}}
    @if($categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-10 lg:px-8">
            <div class="flex items-end justify-between">
                <h2 class="text-xl font-extrabold text-brand-950">Belanja per Kategori</h2>
                <a href="{{ route('catalog') }}" class="text-sm font-bold text-brand-700 hover:text-brand-800">Semua produk →</a>
            </div>
            <div class="no-scrollbar -mx-4 mt-4 flex gap-3 overflow-x-auto px-4 pb-1">
                @foreach($categories as $category)
                    <a href="{{ route('catalog', ['kategori' => $category->slug]) }}"
                        class="flex shrink-0 items-center gap-3 rounded-2xl border border-stone-200 bg-white px-5 py-4 shadow-sm transition hover:border-brand-300 hover:shadow">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12a8.25 8.25 0 1 1 16.5 0 8.25 8.25 0 0 1-16.5 0Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-brand-950">{{ $category->name }}</span>
                            <span class="block text-xs text-stone-500">{{ $category->published_products_count }} produk</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Produk unggulan --}}
    @if($featured->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-12 lg:px-8">
            <h2 class="text-xl font-extrabold text-brand-950">Produk Unggulan</h2>
            <p class="mt-1 text-sm text-stone-500">Pilihan terbaik dari {{ $store['store_name'] ?? 'toko kami' }}.</p>
            <div class="mt-5 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                @foreach($featured as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Produk terbaru --}}
    <section class="mx-auto max-w-7xl px-4 pt-12 lg:px-8">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-brand-950">Produk Terbaru</h2>
                <p class="mt-1 text-sm text-stone-500">Koleksi terbaru yang baru saja tiba di toko.</p>
            </div>
            <a href="{{ route('catalog') }}" class="shrink-0 text-sm font-bold text-brand-700 hover:text-brand-800">Lihat semua →</a>
        </div>
        @if($latest->isNotEmpty())
            <div class="mt-5 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                @foreach($latest as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @else
            <div class="mt-5 rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-12 text-center text-sm text-stone-500">
                Katalog sedang disiapkan. Silakan hubungi kami melalui WhatsApp untuk menanyakan produk.
            </div>
        @endif
    </section>
@endsection

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', ($store['store_name'] ?? 'Optik Dwi Putra')) — {{ $store['tagline'] ?? '' }}</title>
    <meta name="description" content="@yield('meta_description', $store['about'] ?? 'Katalog resmi '.($store['store_name'] ?? 'Optik Dwi Putra').' — kacamata, lensa, dan aksesori optik berkualitas.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('title', $store['store_name'] ?? 'Optik Dwi Putra')">
    <meta property="og:description" content="@yield('meta_description', $store['tagline'] ?? '')">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 font-sans text-stone-800 antialiased">
    @if(!empty($store['promo_text']))
        <div class="bg-brand-950 text-center text-xs font-medium text-brand-100">
            <p class="mx-auto max-w-7xl px-4 py-1.5">{{ $store['promo_text'] }}</p>
        </div>
    @endif

    <header class="sticky top-0 z-40 border-b border-stone-200/80 bg-white/95 shadow-[0_1px_12px_rgba(0,0,0,0.04)] backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-2 sm:gap-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                @if(!empty($store['logo_url']))
                    <img src="{{ $store['logo_url'] }}" alt="{{ $store['store_name'] }}" class="h-10 w-10 rounded-xl object-cover ring-1 ring-stone-200">
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-base font-extrabold text-brand-950">{{ \Illuminate\Support\Str::substr($store['store_name'] ?? 'D', 0, 1) }}</span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-base font-extrabold leading-tight text-brand-950">{{ $store['store_name'] ?? 'Optik Dwi Putra' }}</span>
                    <span class="block truncate text-xs font-medium text-stone-500">{{ $store['tagline'] ?? '' }}</span>
                </span>
            </a>

            <form action="{{ route('catalog') }}" method="GET" class="mx-auto hidden w-full max-w-xl flex-1 md:block">
                <div class="relative">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari kacamata, lensa, SKU…"
                        class="w-full rounded-full border border-stone-300 bg-stone-50 py-2 pl-4 pr-12 text-sm outline-none transition focus:border-brand-600 focus:bg-white focus:ring-2 focus:ring-brand-100">
                    <button type="submit" aria-label="Cari" class="absolute right-1 top-1 flex h-8 w-8 items-center justify-center rounded-full bg-brand-700 text-white transition hover:bg-brand-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    </button>
                </div>
            </form>

            <nav class="ml-auto flex items-center gap-1 sm:gap-2">
                <button type="button" id="mobile-search-toggle" aria-label="Cari produk" aria-expanded="false" class="flex h-10 w-10 items-center justify-center rounded-full text-stone-600 transition hover:bg-brand-50 hover:text-brand-800 md:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                </button>
                <a href="{{ route('home') }}" class="hidden rounded-full px-3 py-2 text-sm font-semibold text-stone-600 transition hover:bg-brand-50 hover:text-brand-800 sm:block">Beranda</a>
                <a href="{{ route('catalog') }}" class="hidden rounded-full px-3 py-2 text-sm font-semibold text-stone-600 transition hover:bg-brand-50 hover:text-brand-800 sm:block">Katalog</a>
                <a href="{{ route('cart') }}" class="relative flex items-center gap-2 rounded-full bg-brand-700 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 4.6a1 1 0 0 0 .9 1.4H19M9 22a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span id="cart-count" class="absolute -right-1.5 -top-1.5 hidden h-5 min-w-5 items-center justify-center rounded-full bg-gold-500 px-1 text-[11px] font-extrabold text-white ring-2 ring-white">0</span>
                </a>
            </nav>
        </div>
        <div id="mobile-search" class="hidden border-t border-stone-100 px-4 py-2 md:hidden">
            <form action="{{ route('catalog') }}" method="GET">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk atau SKU…" id="mobile-search-input"
                    class="w-full rounded-full border border-stone-300 bg-stone-50 px-4 py-2 text-sm outline-none focus:border-brand-600 focus:bg-white">
            </form>
        </div>
        <script>
        (function () {
            var btn = document.getElementById('mobile-search-toggle');
            var panel = document.getElementById('mobile-search');
            if (!btn || !panel) return;
            btn.addEventListener('click', function () {
                var open = panel.classList.toggle('hidden') === false;
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) { var i = document.getElementById('mobile-search-input'); if (i) i.focus(); }
            });
        })();
        </script>
    </header>

    <main>@yield('content')</main>

    <footer class="mt-16 border-t border-brand-900/20 bg-brand-950 text-brand-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <div>
                <p class="text-lg font-extrabold text-white">{{ $store['store_name'] ?? 'Optik Dwi Putra' }}</p>
                <p class="mt-1 text-sm text-brand-300">{{ $store['tagline'] ?? '' }}</p>
                @if(!empty($store['about']))
                    <p class="mt-3 text-sm leading-relaxed text-brand-200/90">{{ \Illuminate\Support\Str::limit($store['about'], 180) }}</p>
                @endif
            </div>
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-gold-400">Kategori</p>
                <ul class="mt-3 space-y-2 text-sm">
                    @php($footerCategories = \Illuminate\Support\Facades\Schema::hasTable('categories') ? \App\Models\Category::active()->orderBy('sort_order')->take(6)->get() : collect())
                    @foreach($footerCategories as $cat)
                        <li><a class="transition hover:text-white" href="{{ route('catalog', ['kategori' => $cat->slug]) }}">{{ $cat->name }}</a></li>
                    @endforeach
                    <li><a class="transition hover:text-white" href="{{ route('catalog') }}">Semua Produk</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-gold-400">Hubungi Kami</p>
                <ul class="mt-3 space-y-2 text-sm text-brand-200/90">
                    @if(!empty($store['address']))<li>{{ $store['address'] }}</li>@endif
                    @if(!empty($store['phone']))<li>Telepon: {{ $store['phone'] }}</li>@endif
                    @if(!empty($store['email']))<li>Email: {{ $store['email'] }}</li>@endif
                    @if(!empty($store['opening_hours']))<li>{{ $store['opening_hours'] }}</li>@endif
                </ul>
            </div>
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-gold-400">Belanja & Ikuti Kami</p>
                <ul class="mt-3 space-y-2 text-sm">
                    @if(!empty($store['shopee_store_url']))<li><a class="transition hover:text-white" href="{{ $store['shopee_store_url'] }}" target="_blank" rel="noopener">Shopee</a></li>@endif
                    @if(!empty($store['tokopedia_store_url']))<li><a class="transition hover:text-white" href="{{ $store['tokopedia_store_url'] }}" target="_blank" rel="noopener">Tokopedia</a></li>@endif
                    @if(!empty($store['instagram_url']))<li><a class="transition hover:text-white" href="{{ $store['instagram_url'] }}" target="_blank" rel="noopener">Instagram</a></li>@endif
                    @if(!empty($store['facebook_url']))<li><a class="transition hover:text-white" href="{{ $store['facebook_url'] }}" target="_blank" rel="noopener">Facebook</a></li>@endif
                    @if(!empty($store['tiktok_url']))<li><a class="transition hover:text-white" href="{{ $store['tiktok_url'] }}" target="_blank" rel="noopener">TikTok</a></li>@endif
                    @if(!empty($store['telegram_url']))<li><a class="transition hover:text-white" href="{{ $store['telegram_url'] }}" target="_blank" rel="noopener">Telegram</a></li>@endif
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10">
            <p class="mx-auto max-w-7xl px-4 py-4 text-center text-xs text-brand-300 lg:px-8">
                {{ $store['footer_note'] ?? '© '.date('Y').' '.($store['store_name'] ?? 'Optik Dwi Putra').'. Harga di website adalah harga referensi; konfirmasi final melalui admin toko.' }}
            </p>
        </div>
    </footer>

    <div id="toast" class="pointer-events-none fixed bottom-5 left-1/2 z-50 hidden -translate-x-1/2 rounded-full bg-brand-950 px-5 py-3 text-sm font-semibold text-white shadow-xl"></div>

    <script>
        const CART_KEY = 'dwiputra_cart';
        function getCart() { try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; } catch (e) { return []; } }
        function setCart(items) { localStorage.setItem(CART_KEY, JSON.stringify(items)); syncCartBadge(); }
        function cartQtyTotal() { return getCart().reduce((s, i) => s + (parseInt(i.qty) || 0), 0); }
        function syncCartBadge() {
            const badge = document.getElementById('cart-count');
            if (!badge) return;
            const n = cartQtyTotal();
            badge.textContent = n;
            badge.classList.toggle('hidden', n === 0);
            badge.classList.toggle('flex', n > 0);
        }
        function showToast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg; t.classList.remove('hidden');
            clearTimeout(window.__toastTimer);
            window.__toastTimer = setTimeout(() => t.classList.add('hidden'), 2600);
        }
        function addToCart(productId, qty, name) {
            const items = getCart();
            const found = items.find(i => i.product_id === productId);
            if (found) { found.qty = Math.min((parseInt(found.qty) || 0) + qty, 999); }
            else { items.push({ product_id: productId, qty: qty }); }
            setCart(items);
            showToast((name || 'Produk') + ' ditambahkan ke keranjang');
        }
        document.addEventListener('DOMContentLoaded', syncCartBadge);
    </script>
    @stack('scripts')
</body>
</html>

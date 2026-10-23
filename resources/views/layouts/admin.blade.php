<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Panel Admin {{ $store['store_name'] ?? 'Optik Dwi Putra' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans text-stone-800 antialiased">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col bg-brand-950 text-brand-100 lg:flex">
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
            @if(!empty($store['logo_url']))
                <img src="{{ $store['logo_url'] }}" alt="" class="h-10 w-10 rounded-lg object-cover">
            @else
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-700 font-extrabold text-white">{{ \Illuminate\Support\Str::substr($store['store_name'] ?? 'D', 0, 1) }}</span>
            @endif
            <div class="min-w-0">
                <p class="truncate font-extrabold text-white">{{ $store['store_name'] ?? 'Optik Dwi Putra' }}</p>
                <p class="text-xs text-brand-300">Panel Admin</p>
            </div>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm font-semibold">
            @php($nav = [
                ['admin.dashboard', 'Dashboard', 'M4 6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm10 0a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2V6ZM4 16a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2Zm10 0a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2v-2Z'],
                ['admin.products.index', 'Produk', 'M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8Z'],
                ['admin.categories.index', 'Kategori', 'M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42l-8.704-8.704ZM6 7a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z'],
                ['admin.brands.index', 'Merek', 'M7.5 3a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9Zm7 0a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9ZM3 21a6 6 0 0 1 12 0M15 15a6 6 0 0 1 6 6'],
                ['admin.settings.index', 'Pengaturan Toko', 'M10.5 6a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9ZM12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.2 2.2m8.4 8.4 2.2 2.2m0-12.8-2.2 2.2M7.8 16.2l-2.2 2.2'],
                ['admin.audit-logs.index', 'Audit Log', 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ])
            @foreach($nav as [$routeName, $label, $path])
                <a href="{{ route($routeName) }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition {{ request()->routeIs($routeName) || request()->routeIs(str_replace('.index', '.*', $routeName)) ? 'bg-brand-700 text-white shadow' : 'text-brand-200 hover:bg-white/5 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="space-y-1 border-t border-white/10 px-3 py-4 text-sm font-semibold">
            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-brand-200 transition hover:bg-white/5 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                Lihat Toko
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-red-300 transition hover:bg-white/5 hover:text-red-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Konten --}}
    <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
        <header class="sticky top-0 z-30 flex items-center gap-3 border-b border-stone-200 bg-white/95 px-4 py-3 backdrop-blur lg:px-8">
            <details class="relative lg:hidden">
                <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg bg-brand-700 text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                </summary>
                <nav class="absolute left-0 top-12 w-56 space-y-1 rounded-2xl border border-stone-200 bg-white p-2 text-sm font-semibold shadow-xl">
                    @foreach($nav as [$routeName, $label, $path])
                        <a href="{{ route($routeName) }}" class="block rounded-lg px-3 py-2 text-stone-700 hover:bg-brand-50">{{ $label }}</a>
                    @endforeach
                    <a href="{{ route('home') }}" target="_blank" class="block rounded-lg px-3 py-2 text-stone-700 hover:bg-brand-50">Lihat Toko</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="block w-full rounded-lg px-3 py-2 text-left text-red-600 hover:bg-red-50">Keluar</button></form>
                </nav>
            </details>
            <h1 class="truncate text-lg font-extrabold text-brand-950">@yield('title', 'Dashboard')</h1>
            <div class="ml-auto flex items-center gap-3">
                <span class="hidden text-sm font-medium text-stone-500 sm:block">{{ auth()->user()?->name }}</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-sm font-extrabold text-brand-800">{{ \Illuminate\Support\Str::substr(auth()->user()?->name ?? 'A', 0, 1) }}</span>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 lg:px-8">
            @if(session('success'))
                <div class="mb-5 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-semibold text-brand-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-bold">Periksa kembali isian berikut:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="px-4 pb-6 text-center text-xs text-stone-400 lg:px-8">Panel Admin {{ $store['store_name'] ?? '' }} — semua perubahan penting tercatat di Audit Log.</footer>
    </div>
</div>
@stack('scripts')
</body>
</html>

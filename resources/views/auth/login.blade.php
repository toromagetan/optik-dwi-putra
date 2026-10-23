<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Admin — {{ $store['store_name'] ?? 'Optik Dwi Putra' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-brand-950 via-brand-900 to-brand-700 px-4 py-10 font-sans antialiased">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            @if(!empty($store['logo_url']))
                <img src="{{ $store['logo_url'] }}" alt="" class="mx-auto h-16 w-16 rounded-2xl object-cover shadow-lg">
            @else
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 text-2xl font-extrabold text-white ring-1 ring-white/25">{{ \Illuminate\Support\Str::substr($store['store_name'] ?? 'D', 0, 1) }}</span>
            @endif
            <h1 class="mt-4 text-2xl font-extrabold text-white">{{ $store['store_name'] ?? 'Optik Dwi Putra' }}</h1>
            <p class="text-sm text-brand-200">Masuk ke panel admin toko</p>
        </div>

        <form method="POST" action="{{ route('login.attempt') }}" class="rounded-3xl bg-white p-6 shadow-2xl sm:p-8">
            @csrf
            <label class="block text-sm font-bold text-stone-700">Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="mt-1.5 w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100" placeholder="admin@tokosaya.id">
            </label>
            @error('email')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror

            <label class="mt-5 block text-sm font-bold text-stone-700">Kata Sandi
                <input type="password" name="password" required autocomplete="current-password"
                    class="mt-1.5 w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100" placeholder="••••••••">
            </label>
            @error('password')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror

            <label class="mt-5 flex cursor-pointer items-center gap-2 text-sm font-medium text-stone-600">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-stone-300 text-brand-700 focus:ring-brand-500">
                Ingat saya di perangkat ini
            </label>

            <button type="submit" class="mt-6 w-full rounded-xl bg-brand-700 py-3 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">Masuk ke Panel Admin</button>

            <p class="mt-4 text-center text-xs leading-relaxed text-stone-400">Halaman ini khusus pengelola toko. Percobaan masuk dibatasi demi keamanan.</p>
        </form>

        <p class="mt-5 text-center text-sm"><a href="{{ route('home') }}" class="font-semibold text-brand-200 transition hover:text-white">← Kembali ke toko</a></p>
    </div>
</body>
</html>

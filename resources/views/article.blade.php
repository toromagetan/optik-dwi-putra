<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article['title'] }} - {{ $settings->shop_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #FAFAFA; }
        .theme-bg { background-color: {{ $settings->theme_color }}; }
        .theme-text { color: {{ $settings->theme_color }}; }
        .hover-theme:hover { filter: brightness(0.9); }
        .article-body p { margin-bottom: 1rem; line-height: 1.75; color: #374151; }
        .article-body h3 { font-weight: 800; color: #111827; font-size: 1.125rem; margin: 1.75rem 0 0.5rem; }
        .article-body ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; color: #374151; }
        .article-body li { margin-bottom: 0.4rem; line-height: 1.7; }
        .article-body table { width: 100%; border-collapse: collapse; margin: 1rem 0 1.5rem; font-size: 0.9rem; }
        .article-body th { background: #111827; color: #fff; text-align: left; padding: 0.6rem 0.8rem; font-weight: 700; }
        .article-body td { border-bottom: 1px solid #E5E7EB; padding: 0.6rem 0.8rem; color: #374151; }
    </style>
</head>
<body class="antialiased text-gray-900">

    <!-- Navbar -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-4 flex justify-between items-center">
            <a href="/" class="text-2xl font-extrabold tracking-tighter uppercase theme-text">
                {{ $settings->shop_name }}
            </a>
            <div class="flex items-center gap-4">
                <a href="/" class="text-sm font-bold text-gray-900 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full transition">Katalog</a>
                <a href="https://wa.me/{{ $settings->whatsapp_number }}" class="hidden lg:block text-sm font-bold text-gray-900 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full transition">Tanya Admin</a>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 lg:px-8 py-12">
        <nav class="text-sm font-medium text-gray-500 mb-6">
            <a href="/" class="hover:text-gray-900">Beranda</a>
            <span class="mx-2 text-gray-300">/</span>
            <span class="text-gray-900 font-bold">{{ $article['title'] }}</span>
        </nav>

        <h1 class="text-3xl md:text-4xl font-black tracking-tight text-gray-900 leading-tight">{{ $article['title'] }}</h1>
        <p class="text-lg text-gray-500 mt-3 leading-relaxed">{{ $article['excerpt'] }}</p>

        <article class="article-body bg-white border border-gray-100 rounded-3xl shadow-sm p-6 md:p-10 mt-8">
            {!! $article['body'] !!}
        </article>

        <!-- Artikel Lainnya -->
        <section class="mt-12">
            <h2 class="text-xl font-black tracking-tight text-gray-900 mb-4">Artikel Lainnya</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($articles as $aSlug => $a)
                @if($aSlug !== $slug)
                <a href="{{ route('articles.show', $aSlug) }}" class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm block group">
                    <h3 class="font-extrabold text-gray-900 group-hover:underline">{{ $a['title'] }}</h3>
                    <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ Str::limit($a['excerpt'], 90) }}</p>
                </a>
                @endif
                @endforeach
            </div>
        </section>
    </main>

    <!-- Footer ringkas -->
    <footer class="bg-white border-t border-gray-200 pt-16 pb-10 mt-10">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8 mb-12">
            <div>
                <a href="/" class="text-2xl font-extrabold tracking-tighter uppercase text-gray-900 mb-6 inline-block">{{ $settings->shop_name }}<span class="theme-text">.</span></a>
                <p class="text-gray-500 text-sm leading-relaxed">Solusi penglihatan terbaik dengan koleksi frame premium dan layanan resep mata presisi. Dipercaya oleh ribuan pelanggan di seluruh Indonesia.</p>
            </div>
            <div>
                <h4 class="font-extrabold text-gray-900 mb-6 uppercase tracking-wider text-sm">Layanan & Bantuan</h4>
                <ul class="space-y-4 text-sm font-medium text-gray-500">
                    @foreach($articles as $aSlug => $a)
                    <li><a href="{{ route('articles.show', $aSlug) }}" class="hover:text-gray-900 hover:translate-x-1 inline-block transition transform">{{ $a['title'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="col-span-1 md:col-span-2">
                <h4 class="font-extrabold text-gray-900 mb-6 uppercase tracking-wider text-sm">Informasi Toko</h4>
                <ul class="space-y-4 text-sm font-medium text-gray-600">
                    <li><span class="block text-gray-900 font-bold mb-1">Alamat Optik</span><span class="text-gray-500">{{ $settings->address ?? 'Jl. Kacamata Utama No.123, Blok A, Surabaya, Jawa Timur 60281' }}</span></li>
                    <li><span class="block text-gray-900 font-bold mb-1">WhatsApp Customer Service</span><a href="https://wa.me/{{ $settings->whatsapp_number }}" class="theme-text font-bold">{{ $settings->whatsapp_number }}</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-6xl mx-auto px-4 lg:px-8 pt-6 border-t border-gray-100 text-center text-xs text-gray-400 font-medium">
            &copy; {{ date('Y') }} {{ $settings->shop_name }}. All rights reserved.
        </div>
    </footer>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - {{ $settings->shop_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #FAFAFA; }
        .theme-bg { background-color: {{ $settings->theme_color }}; }
        .theme-text { color: {{ $settings->theme_color }}; }
        .theme-border { border-color: {{ $settings->theme_color }}; }
        .hover-theme:hover { filter: brightness(0.9); }
        .color-swatch.active { outline: 2px solid {{ $settings->theme_color }}; outline-offset: 2px; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased text-gray-900" x-data="productPage()">

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

    <main class="max-w-6xl mx-auto px-4 lg:px-8 py-10">
        <!-- Breadcrumb -->
        <nav class="text-sm font-medium text-gray-500 mb-8">
            <a href="/" class="hover:text-gray-900">Beranda</a>
            <span class="mx-2 text-gray-300">/</span>
            <span>{{ $product->category->name ?? 'Produk' }}</span>
            <span class="mx-2 text-gray-300">/</span>
            <span class="text-gray-900 font-bold">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">
            <!-- Foto Produk (berganti mengikuti varian) -->
            <div>
                <div class="bg-gray-100 aspect-square rounded-3xl relative overflow-hidden flex items-center justify-center shadow-sm">
                    @if($product->badge)
                    <div class="absolute top-4 left-4 bg-gray-900 text-white text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider z-10 shadow-md">
                        {{ $product->badge }}
                    </div>
                    @endif
                    <img x-show="currentVariant?.image_path" :src="currentVariant?.image_path ? '/storage/' + currentVariant.image_path : ''" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-cover z-[5]">
                    <div class="w-full h-full flex items-center justify-center transition-colors duration-500 relative" :style="`background-color: ${currentVariant?.color_hex || '#e5e7eb'}20`">
                        <svg class="w-40 h-40 opacity-80" :style="`color: ${currentVariant?.color_hex || '#9ca3af'}`" fill="currentColor" viewBox="0 0 24 24"><path d="M21.947 9.179a1.001 1.001 0 0 0-.868-.492h-5.26c-1.396 0-2.617.915-3.056 2.232L12 13.2l-.763-2.281a3.228 3.228 0 0 0-3.056-2.232H2.92a1 1 0 0 0-.868.492 1.002 1.002 0 0 0 .153 1.077L4.5 13.5v3a1 1 0 0 0 1 1h4a3.504 3.504 0 0 0 3.5-3.5 1 1 0 0 1 2 0 3.504 3.504 0 0 0 3.5 3.5h4a1 1 0 0 0 1-1v-3l2.295-3.244a1.002 1.002 0 0 0 .152-1.077z"/></svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-3 font-medium">Warna pada foto: <span class="text-gray-900 font-bold" x-text="currentVariant?.color_name || '-'"></span></p>
            </div>

            <!-- Detail Produk -->
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                    {{ $product->material ?? 'Premium' }}@if($product->brand) • {{ $product->brand->name }}@endif
                </p>
                <h1 class="text-3xl md:text-4xl font-black tracking-tight text-gray-900 leading-tight">{{ $product->name }}</h1>

                <div class="mt-4 flex items-end gap-3">
                    @if($product->promo_price)
                    <p class="text-lg text-gray-400 line-through">Rp{{ number_format($product->base_price, 0, ',', '.') }}</p>
                    <p class="text-3xl font-black text-gray-900">Rp{{ number_format($product->promo_price, 0, ',', '.') }}</p>
                    @else
                    <p class="text-3xl font-black text-gray-900">Rp{{ number_format($product->base_price, 0, ',', '.') }}</p>
                    @endif
                </div>

                <!-- Pilihan Warna Varian -->
                <div class="mt-8">
                    <label class="block text-sm font-bold text-gray-900 mb-3">Pilih Warna: <span class="theme-text" x-text="currentVariant?.color_name || '-'"></span></label>
                    <div class="flex gap-3" x-show="variants.length > 0">
                        <template x-for="(variant, index) in variants" :key="variant.id">
                            <button @click="selectVariant(index)"
                                    :class="{'active': currentIndex === index, 'opacity-30 cursor-not-allowed grayscale': variant.stock <= 0}"
                                    class="w-10 h-10 rounded-full shadow-inner color-swatch transition transform hover:scale-110 relative"
                                    :style="`background-color: ${variant.color_hex}`"
                                    :title="variant.color_name + (variant.stock <= 0 ? ' (Stok Habis)' : '')">
                                <span x-show="variant.stock <= 0" class="absolute inset-0 flex items-center justify-center text-red-600 transform rotate-45 text-xs font-black">|</span>
                            </button>
                        </template>
                    </div>
                    <p class="text-sm mt-3 font-medium" :class="currentVariant?.stock > 0 ? 'text-green-600' : 'text-red-500'" x-text="currentVariant?.stock > 0 ? `Stok tersedia: ${currentVariant.stock}` : 'Stok habis untuk warna ini'"></p>
                </div>

                <!-- Deskripsi -->
                <div class="mt-8 text-gray-600 leading-relaxed text-[15px]">
                    @if($product->description)
                    <p>{{ $product->description }}</p>
                    @else
                    <p>Frame {{ strtolower($product->material ?? 'premium') }} dengan desain ergonomis yang ringan dipakai seharian. Setiap pembelian dilengkapi hard case, kain microfiber, dan layanan penyetelan gratis di optik kami.</p>
                    @endif
                </div>

                <!-- Pilar Kepercayaan (USP Blueprint) -->
                <div class="mt-8 bg-gray-50 border border-gray-100 rounded-2xl p-5 space-y-3">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 theme-text mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <p class="text-sm text-gray-700"><strong class="text-gray-900">Free Eye Examination</strong> — periksa mata gratis tanpa tekanan beli, sebelum Anda memesan.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 theme-text mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <p class="text-sm text-gray-700"><strong class="text-gray-900">Premium & Reliable Materials</strong> — material pilihan ({{ $product->material ?? 'Acetate / Titanium' }}) yang awet untuk pemakaian harian.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 theme-text mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <p class="text-sm text-gray-700"><strong class="text-gray-900">Garansi Layanan 6 Bulan</strong> — penyetelan gratis dan perlindungan cacat produksi. <a href="{{ route('articles.show', 'garansi-layanan') }}" class="theme-text font-bold hover:underline">Baca ketentuannya</a>.</p>
                    </div>
                </div>

                <!-- CTA Pilih Lensa (Blueprint UX langkah 2) -->
                <button @click="openWizard()"
                        :class="currentVariant?.stock > 0 ? 'hover-theme cursor-pointer' : 'opacity-50 cursor-not-allowed bg-gray-400'"
                        :disabled="currentVariant?.stock <= 0"
                        class="mt-8 w-full bg-gray-900 text-white font-extrabold py-4 rounded-xl transition shadow-lg shadow-gray-200 text-base">
                    <span x-text="currentVariant?.stock <= 0 ? 'Stok Habis' : 'Pilih Lensa'"></span>
                </button>
                <p class="text-xs text-gray-400 mt-3 text-center font-medium">Di langkah berikutnya Anda memilih tipe lensa dan mengisi resep (atau Frame Only), lalu pesanan diteruskan ke WhatsApp kami.</p>
            </div>
        </div>

        @if($related->count() > 0)
        <!-- Produk Lainnya -->
        <section class="mt-20">
            <h2 class="text-2xl font-black tracking-tight text-gray-900 mb-6">Produk Lainnya</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($related as $rel)
                @php $firstVariant = $rel->variants->first(); @endphp
                <a href="{{ route('products.show', $rel->slug) }}" class="group bg-white p-5 rounded-2xl shadow-sm border border-gray-100 block">
                    <div class="bg-gray-100 aspect-square rounded-xl mb-4 relative overflow-hidden flex items-center justify-center" @if($firstVariant) style="background-color: {{ $firstVariant->color_hex }}20" @endif>
                        @if($firstVariant?->image_path)
                        <img src="{{ Storage::url($firstVariant->image_path) }}" alt="{{ $rel->name }}" class="absolute inset-0 w-full h-full object-cover">
                        @elseif($firstVariant)
                        <svg class="w-24 h-24 opacity-80" style="color: {{ $firstVariant->color_hex }}" fill="currentColor" viewBox="0 0 24 24"><path d="M21.947 9.179a1.001 1.001 0 0 0-.868-.492h-5.26c-1.396 0-2.617.915-3.056 2.232L12 13.2l-.763-2.281a3.228 3.228 0 0 0-3.056-2.232H2.92a1 1 0 0 0-.868.492 1.002 1.002 0 0 0 .153 1.077L4.5 13.5v3a1 1 0 0 0 1 1h4a3.504 3.504 0 0 0 3.5-3.5 1 1 0 0 1 2 0 3.504 3.504 0 0 0 3.5 3.5h4a1 1 0 0 0 1-1v-3l2.295-3.244a1.002 1.002 0 0 0 .152-1.077z"/></svg>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ $rel->material ?? 'Premium' }}</p>
                    <h3 class="font-extrabold text-lg leading-tight group-hover:underline">{{ $rel->name }}</h3>
                    <p class="font-extrabold text-gray-900 mt-1">Rp{{ number_format($rel->promo_price ?? $rel->base_price, 0, ',', '.') }}</p>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </main>

    <!-- LENS WIZARD MODAL (sama seperti beranda) -->
    <div x-show="wizard.open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]" @click.away="wizard.open = false">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-gray-900">Kustomisasi Lensa</h2>
                    <p class="text-sm font-medium theme-text" x-text="`${PRODUCT_NAME} - ${currentVariant?.color_name ?? ''}`"></p>
                </div>
                <button @click="wizard.open = false" class="text-gray-400 hover:text-gray-900 p-2 bg-gray-100 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 bg-white">
                <div id="wizardDetail">

                    <!-- Pilihan Lensa -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-900 mb-3">Pilih Tipe Lensa</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="border-2 rounded-xl p-4 cursor-pointer transition relative" :class="wizard.lensType === 'Frame Only' ? 'theme-border bg-gray-50' : 'border-gray-100 hover:border-gray-300'">
                                <input type="radio" name="lens_type" value="Frame Only" x-model="wizard.lensType" class="sr-only">
                                <span class="font-extrabold block text-gray-900">Frame Only</span>
                                <span class="text-xs font-medium text-gray-500 mt-1 block">Beli kacamata tanpa lensa resep</span>
                                <div x-show="wizard.lensType === 'Frame Only'" class="absolute top-4 right-4 theme-text">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </div>
                            </label>
                            <label class="border-2 rounded-xl p-4 cursor-pointer transition relative" :class="wizard.lensType === 'Single Vision' ? 'theme-border bg-gray-50' : 'border-gray-100 hover:border-gray-300'">
                                <input type="radio" name="lens_type" value="Single Vision" x-model="wizard.lensType" class="sr-only">
                                <span class="font-extrabold block text-gray-900">Single Vision</span>
                                <span class="text-xs font-medium text-gray-500 mt-1 block">Untuk mata minus, plus, atau silinder</span>
                                <div x-show="wizard.lensType === 'Single Vision'" class="absolute top-4 right-4 theme-text">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Input Resep (Jika bukan frame only) -->
                    <div x-show="wizard.lensType !== 'Frame Only'" x-cloak>
                        <h3 class="font-bold text-sm mb-3 text-gray-900">Masukkan Resep Mata (Prescription)</h3>
                        <div class="bg-gray-50 p-5 rounded-xl mb-6 border border-gray-100">
                            <div class="grid grid-cols-4 gap-4 items-center mb-4">
                                <div class="col-span-1 font-extrabold text-sm text-gray-700">Kanan (OD)</div>
                                <div>
                                    <label class="text-xs font-bold text-gray-500 block mb-1">SPH</label>
                                    <input type="text" x-model="wizard.rx.r_sph" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="-1.00">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-500 block mb-1">CYL</label>
                                    <input type="text" x-model="wizard.rx.r_cyl" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="-0.50">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-500 block mb-1">AXIS</label>
                                    <input type="text" x-model="wizard.rx.r_axis" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="180">
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-4 items-center">
                                <div class="col-span-1 font-extrabold text-sm text-gray-700">Kiri (OS)</div>
                                <div>
                                    <input type="text" x-model="wizard.rx.l_sph" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="-1.00">
                                </div>
                                <div>
                                    <input type="text" x-model="wizard.rx.l_cyl" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="-0.50">
                                </div>
                                <div>
                                    <input type="text" x-model="wizard.rx.l_axis" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="180">
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <label class="text-xs font-bold text-gray-500 block mb-1">Pupillary Distance (PD) - mm</label>
                                <input type="text" x-model="wizard.rx.pd" class="w-1/3 border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="62">
                            </div>
                            <p class="text-xs text-gray-400 mt-3">Belum tahu cara membaca resep? <a href="{{ route('articles.show', 'cara-membaca-resep-mata') }}" class="theme-text font-bold hover:underline">Baca panduannya</a>.</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Total & Tombol Order -->
            <div class="p-6 border-t border-gray-100 bg-white flex justify-between items-center shadow-[0_-10px_20px_-5px_rgba(0,0,0,0.05)]">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Harga</p>
                    <p class="text-2xl font-black text-gray-900" x-text="`Rp${new Intl.NumberFormat('id-ID').format(EFFECTIVE_PRICE)}`"></p>
                </div>
                <button @click="orderViaWhatsApp()" class="theme-bg text-black font-extrabold py-3.5 px-8 rounded-xl transition hover-theme shadow-lg shadow-lime-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    Hubungi
                </button>
            </div>
        </div>
    </div>

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
                    @foreach($articles ?? [] as $aSlug => $a)
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

    <!-- Script Logika Halaman Produk -->
    <script>
        const SHOP_NAME = @json($settings->shop_name ?? 'Optik Dwi Putra');
        const WHATSAPP_NUMBER = @json($settings->whatsapp_number ?? '628000000000');
        const PRODUCT_NAME = @json($product->name);
        const EFFECTIVE_PRICE = {{ $product->promo_price ?? $product->base_price }};

        document.addEventListener('alpine:init', () => {
            Alpine.data('productPage', () => ({
                variants: @json($product->variants),
                currentIndex: 0,
                wizard: {
                    open: false,
                    lensType: 'Frame Only',
                    rx: { r_sph: '', r_cyl: '', r_axis: '', l_sph: '', l_cyl: '', l_axis: '', pd: '' }
                },
                init() {
                    const i = this.variants.findIndex(v => v.stock > 0);
                    this.currentIndex = i >= 0 ? i : 0;
                },
                get currentVariant() {
                    return this.variants[this.currentIndex] ?? null;
                },
                selectVariant(index) {
                    this.currentIndex = index;
                },
                openWizard() {
                    if (!this.currentVariant || this.currentVariant.stock <= 0) return;
                    this.wizard.lensType = 'Frame Only';
                    this.wizard.rx = { r_sph: '', r_cyl: '', r_axis: '', l_sph: '', l_cyl: '', l_axis: '', pd: '' };
                    this.wizard.open = true;
                },
                orderViaWhatsApp() {
                    const w = this.wizard;
                    const variantName = this.currentVariant?.color_name ?? '';
                    const total = 'Rp' + new Intl.NumberFormat('id-ID').format(EFFECTIVE_PRICE);
                    let text = `Halo ${SHOP_NAME}, saya mau order kacamata via website.\n\n` +
                        `*Produk:* ${PRODUCT_NAME} (Warna: ${variantName})\n` +
                        `*Lensa:* ${w.lensType}\n`;
                    if (w.lensType !== 'Frame Only') {
                        const r = w.rx;
                        const eye = (sph, cyl, axis) => `SPH ${sph || '-'}, CYL ${cyl || '-'}, AXIS ${axis || '-'}`;
                        text += `*Resep:*\n` +
                            `OD (Kanan): ${eye(r.r_sph, r.r_cyl, r.r_axis)}\n` +
                            `OS (Kiri): ${eye(r.l_sph, r.l_cyl, r.l_axis)}\n` +
                            `PD: ${r.pd || '-'} mm\n`;
                    }
                    text += `*Total Harga:* ${total}\n\nMohon info pembayaran dan pengiriman. Terima kasih.`;
                    window.open(`https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(text)}`, '_blank');
                }
            }));
        });
    </script>
</body>
</html>

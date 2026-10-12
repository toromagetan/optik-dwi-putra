<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->shop_name }} - E-Commerce</title>
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
<body class="antialiased text-gray-900" x-data="store()">

    <!-- Navbar -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-4 flex justify-between items-center">
            <a href="/" class="text-2xl font-extrabold tracking-tighter uppercase theme-text">
                {{ $settings->shop_name }}
            </a>
            <!-- Search Bar Pintar -->
            <div class="hidden md:block flex-1 max-w-md mx-8 relative">
                <input type="text" x-model="searchQuery" placeholder="Cari nama atau material kacamata..." class="w-full bg-gray-100 border-transparent focus:bg-white focus:border-lime-500 focus:ring-2 focus:ring-lime-200 rounded-full py-2.5 px-5 pl-12 text-sm font-medium transition shadow-sm outline-none">
                <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div class="flex items-center gap-4">
                <button @click="showBookingModal = true" class="text-sm font-bold theme-bg text-white px-5 py-2 rounded-full shadow-md hover:brightness-110 transition">Buat Janji</button>
                <a href="https://wa.me/{{ $settings->whatsapp_number }}" class="hidden lg:block text-sm font-bold text-gray-900 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full transition">Tanya Admin</a>
            </div>
        </div>
    </header>

    <!-- Banner Slider -->
    @if($banners->count() > 0)
    <section class="max-w-6xl mx-auto px-4 lg:px-8 mt-6 mb-8">
        <div x-data="bannerSlider({{ $banners->count() }})" 
             x-init="start()" 
             @mouseenter="pause()" 
             @mouseleave="start()"
             class="relative w-full rounded-3xl overflow-hidden shadow-lg group bg-gray-100 aspect-[16/7] md:aspect-[21/6]">
             
            <div class="flex transition-transform duration-700 ease-in-out h-full" :style="`transform: translateX(-${current * 100}%)`">
                @foreach($banners as $index => $banner)
                <div class="w-full h-full flex-shrink-0 relative">
                    <a href="{{ $banner->link_url ?? '#' }}" class="block w-full h-full">
                        @if($banner->image_path)
                            <img src="{{ Storage::url($banner->image_path) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                        @else
                            <!-- Dummy Slide Color (Jika belum ada gambar) -->
                            <div class="w-full h-full flex flex-col items-center justify-center text-white" style="background-color: {{ ['#84CC16', '#111827', '#374151'][$index % 3] }}">
                                <h2 class="text-3xl md:text-5xl font-black uppercase tracking-widest text-center px-4">{{ $banner->title ?? 'PROMO SPESIAL' }}</h2>
                                <p class="mt-4 text-sm md:text-base font-medium opacity-80 uppercase tracking-widest">Ketuk Untuk Detail</p>
                            </div>
                        @endif
                    </a>
                </div>
                @endforeach
            </div>

            <!-- Navigasi Panah -->
            <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-900 p-3 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-all backdrop-blur-sm z-10 hidden md:block">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-900 p-3 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-all backdrop-blur-sm z-10 hidden md:block">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <!-- Dots Navigasi Bawah -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <template x-for="i in count" :key="i">
                    <button @click="goTo(i - 1)" 
                            :class="current === (i - 1) ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/80 w-2'" 
                            class="h-2 rounded-full transition-all duration-300"></button>
                </template>
            </div>
        </div>
    </section>
    @else
    <!-- Hero Bawaan (Jika Banner Kosong) -->
    <section class="theme-bg text-white">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-16 md:py-24 text-center">
            <h1 class="text-4xl md:text-6xl font-black tracking-tighter mb-6 uppercase drop-shadow-md">
                {{ $settings->hero_title ?? 'ERGONOMIC DESIGN' }}
            </h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto font-medium opacity-90 drop-shadow-sm">
                {{ $settings->hero_subtitle ?? 'Koleksi frame kacamata premium dengan material pilihan. Free Eye Examination always included.' }}
            </p>
        </div>
    </section>
    @endif

    <!-- Content -->
    <main class="max-w-6xl mx-auto px-4 lg:px-8 py-12">
        
        <!-- Filter Kategori -->
        <div class="flex gap-3 mb-10 overflow-x-auto pb-2 scrollbar-hide">
            <button @click="category = 'all'" :class="category === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-700'" class="px-6 py-2 rounded-full font-semibold text-sm transition whitespace-nowrap">Semua</button>
            @foreach($categories as $cat)
            <button @click="category = '{{ $cat->id }}'" :class="category === '{{ $cat->id }}' ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-700'" class="px-6 py-2 rounded-full font-semibold text-sm transition whitespace-nowrap">{{ $cat->name }}</button>
            @endforeach
        </div>

        <!-- Grid Produk -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($products as $product)
            <!-- Kartu Produk menggunakan state mandiri Alpine -->
            <div x-data="productCard({{ $product->variants->toJson() }})" x-show="(category === 'all' || category === '{{ $product->category_id }}') && (brand === 'all' || brand == '{{ $product->brand_id }}') && '{{ strtolower(addslashes($product->name . ' ' . $product->material)) }}'.includes(searchQuery.toLowerCase())" class="group bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                
                <div class="bg-gray-100 aspect-square rounded-xl mb-4 relative overflow-hidden flex items-center justify-center transition">
                    @if($product->badge)
                    <div class="absolute top-3 left-3 bg-gray-900 text-white text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider z-10 shadow-md">
                        {{ $product->badge }}
                    </div>
                    @endif

                    <!-- Placeholder Gambar - Warnanya ganti sesuai varian! -->
                    <div class="w-full h-full flex items-center justify-center transition-colors duration-500 relative" :style="`background-color: ${currentVariant?.color_hex || '#e5e7eb'}20`">
                        <!-- Ikon Kacamata -->
                        <svg class="w-24 h-24 opacity-80" :style="`color: ${currentVariant?.color_hex || '#9ca3af'}`" fill="currentColor" viewBox="0 0 24 24"><path d="M21.947 9.179a1.001 1.001 0 0 0-.868-.492h-5.26c-1.396 0-2.617.915-3.056 2.232L12 13.2l-.763-2.281a3.228 3.228 0 0 0-3.056-2.232H2.92a1 1 0 0 0-.868.492 1.002 1.002 0 0 0 .153 1.077L4.5 13.5v3a1 1 0 0 0 1 1h4a3.504 3.504 0 0 0 3.5-3.5 1 1 0 0 1 2 0 3.504 3.504 0 0 0 3.5 3.5h4a1 1 0 0 0 1-1v-3l2.295-3.244a1.002 1.002 0 0 0 .152-1.077z"/></svg>
                    </div>
                </div>

                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ $product->material ?? 'Premium' }}</p>
                        <h3 class="font-extrabold text-xl leading-tight">{{ $product->name }}</h3>
                    </div>
                    <div class="text-right ml-2">
                        @if($product->promo_price)
                        <p class="text-xs text-gray-400 line-through">Rp{{ number_format($product->base_price, 0, ',', '.') }}</p>
                        <p class="font-extrabold text-lg text-gray-900">Rp{{ number_format($product->promo_price, 0, ',', '.') }}</p>
                        @else
                        <p class="font-extrabold text-lg text-gray-900">Rp{{ number_format($product->base_price, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Pilihan Warna Varian -->
                <div class="flex gap-2 mt-4" x-show="variants.length > 0">
                    <template x-for="(variant, index) in variants" :key="variant.id">
                        <button @click="selectVariant(index)"
                                :class="{'active': currentIndex === index, 'opacity-30 cursor-not-allowed grayscale': variant.stock <= 0}"
                                class="w-6 h-6 rounded-full shadow-inner color-swatch transition transform hover:scale-110"
                                :style="`background-color: ${variant.color_hex}`"
                                :title="variant.color_name + (variant.stock <= 0 ? ' (Stok Habis)' : '')">
                        <span x-show="variant.stock <= 0" class="absolute inset-0 flex items-center justify-center text-red-600 transform rotate-45 text-[10px] font-black font-sans">|</span>
                        </button>
                    </template>
                </div>
                <p class="text-xs text-gray-500 mt-2 font-medium" x-text="currentVariant?.color_name || 'Tidak ada varian'"></p>

                <!-- Tombol Add to Cart / Pesan -->
                <button @click="currentVariant?.stock > 0 ? openWizard('{{ addslashes($product->name) }}', currentVariant, {{ $product->promo_price ?? $product->base_price }}) : null" 
                        :class="currentVariant?.stock > 0 ? 'hover-theme cursor-pointer' : 'opacity-50 cursor-not-allowed bg-gray-400'"
                        :disabled="currentVariant?.stock <= 0"
                        class="mt-5 w-full bg-gray-900 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-gray-200">
                    <span x-text="currentVariant?.stock <= 0 ? 'Stok Habis' : 'Pilih Lensa & Checkout'"></span>
                </button>
            </div>
            @endforeach
        </div>
    </main>

    <!-- Booking Modal -->
    <div x-show="showBookingModal" 
         class="fixed inset-0 z-[60] overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showBookingModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showBookingModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="showBookingModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                
                <form action="{{ route('book.appointment') }}" method="POST" x-data="{ type: 'in_store' }">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-xl leading-6 font-black text-gray-900" id="modal-title">Buat Janji Periksa Mata</h3>
                            <button type="button" @click="showBookingModal = false" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Nama Lengkap</label>
                                <input type="text" name="customer_name" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-lime-500 focus:border-lime-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Nomor WhatsApp</label>
                                <input type="tel" name="phone" required class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-lime-500 focus:border-lime-500">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700">Tipe Layanan</label>
                                <div class="mt-2 grid grid-cols-2 gap-3">
                                    <label class="border rounded-lg p-3 flex items-center cursor-pointer" :class="type === 'in_store' ? 'border-lime-500 bg-lime-50' : 'border-gray-200'">
                                        <input type="radio" name="type" value="in_store" x-model="type" class="text-lime-600 focus:ring-lime-500 h-4 w-4">
                                        <span class="ml-2 text-sm font-medium text-gray-900">Datang ke Optik</span>
                                    </label>
                                    <label class="border rounded-lg p-3 flex items-center cursor-pointer" :class="type === 'home_visit' ? 'border-lime-500 bg-lime-50' : 'border-gray-200'">
                                        <input type="radio" name="type" value="home_visit" x-model="type" class="text-lime-600 focus:ring-lime-500 h-4 w-4">
                                        <span class="ml-2 text-sm font-medium text-gray-900">Home Visit (Ke Rumah)</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Tanggal Janji Temu</label>
                                <input type="date" name="appointment_date" required min="{{ date('Y-m-d') }}" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-lime-500 focus:border-lime-500">
                            </div>
                            
                            <div x-show="type === 'home_visit'">
                                <label class="block text-sm font-bold text-gray-700">Alamat Lengkap</label>
                                <textarea name="address" rows="2" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-lime-500 focus:border-lime-500"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700">Keluhan / Catatan <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                <textarea name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-lime-500 focus:border-lime-500"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-lime-500 text-base font-medium text-white hover:bg-lime-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Kirim & Konfirmasi WA
                        </button>
                        <button type="button" @click="showBookingModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <!-- Testimonial Section -->
    <section class="bg-gray-900 text-white py-16 md:py-24 mt-8">
        <div class="max-w-6xl mx-auto px-4 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-black uppercase tracking-widest mb-4">Ulasan Pelanggan</h2>
                <p class="text-gray-400 font-medium max-w-2xl mx-auto">Kami berkomitmen memberikan kualitas kacamata dan pelayanan terbaik. Inilah kata mereka yang telah membuktikannya.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($testimonials as $testi)
                <div class="bg-gray-800 p-8 rounded-3xl border border-gray-700 relative hover:-translate-y-2 transition transform duration-300 shadow-2xl">
                    <div class="flex text-yellow-400 mb-4">
                        @for($i=0; $i<$testi->rating; $i++)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endfor
                    </div>
                    <p class="text-gray-300 text-base mb-6 leading-relaxed line-clamp-4 font-medium relative z-10">"{{ $testi->content }}"</p>
                    <p class="font-extrabold text-white text-lg tracking-tight">{{ $testi->customer_name }}</p>
                    <p class="text-xs text-lime-400 font-bold uppercase tracking-widest mt-1">Verified Buyer</p>
                    <svg class="absolute top-6 right-6 w-16 h-16 text-gray-700 opacity-20" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/></svg>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Professional Footer -->
    <footer class="bg-white border-t border-gray-200 pt-20 pb-10">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8 mb-16">
            <!-- Col 1 -->
            <div class="col-span-1 md:col-span-1">
                <a href="/" class="text-2xl font-extrabold tracking-tighter uppercase text-gray-900 mb-6 inline-block">
                    {{ $settings->shop_name }}<span class="theme-text">.</span>
                </a>
                <p class="text-gray-500 text-sm mb-6 leading-relaxed">Solusi penglihatan terbaik dengan koleksi frame premium dan layanan resep mata presisi. Dipercaya oleh ribuan pelanggan di seluruh Indonesia.</p>
                <div class="flex gap-4 text-gray-400">
                    <a href="#" class="hover:theme-text transition transform hover:scale-110"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                    <a href="#" class="hover:theme-text transition transform hover:scale-110"><svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                </div>
            </div>
            
            <!-- Col 2 -->
            <div>
                <h4 class="font-extrabold text-gray-900 mb-6 uppercase tracking-wider text-sm">Layanan & Bantuan</h4>
                <ul class="space-y-4 text-sm font-medium text-gray-500">
                    <li><a href="#" class="hover:text-gray-900 hover:translate-x-1 inline-block transition transform">Cara Membaca Resep Mata</a></li>
                    <li><a href="#" class="hover:text-gray-900 hover:translate-x-1 inline-block transition transform">Panduan Ukuran Frame</a></li>
                    <li><a href="#" class="hover:text-gray-900 hover:translate-x-1 inline-block transition transform">Kebijakan Pengiriman & Retur</a></li>
                    <li><a href="#" class="hover:text-gray-900 hover:translate-x-1 inline-block transition transform flex items-center gap-2">Garansi 6 Bulan <span class="bg-gray-900 text-white text-[10px] px-2 py-0.5 rounded-md">PRO</span></a></li>
                </ul>
            </div>
            
            <!-- Col 3 -->
            <div class="col-span-1 md:col-span-2">
                <h4 class="font-extrabold text-gray-900 mb-6 uppercase tracking-wider text-sm">Informasi Toko</h4>
                <ul class="space-y-5 text-sm font-medium text-gray-600">
                    <li class="flex items-start gap-4">
                        <div class="bg-gray-100 p-2 rounded-lg text-gray-900 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-gray-900 font-bold mb-1">Alamat Optik</span>
                            <span class="leading-relaxed text-gray-500">Jl. Kacamata Utama No.123, Blok A<br>Surabaya, Jawa Timur 60281</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="bg-gray-100 p-2 rounded-lg text-gray-900 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-gray-900 font-bold mb-1">WhatsApp Customer Service</span>
                            <span class="text-gray-500">{{ $settings->whatsapp_number }} (Buka Tiap Hari: 09.00 - 20.00)</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-100 max-w-6xl mx-auto px-4 lg:px-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm font-medium text-gray-400">
            <div>
                &copy; {{ date('Y') }} <span class="text-gray-900 font-bold">{{ $settings->shop_name }}</span>. Hak Cipta Dilindungi.
            </div>
            <div class="flex items-center gap-1">
                <span class="text-xs uppercase tracking-widest">Built with</span>
                <svg class="w-4 h-4 text-red-500 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                <span class="text-xs text-gray-900 font-black tracking-widest uppercase">Optik Blueprint</span>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Ikon -->
    <a href="https://wa.me/{{ $settings->whatsapp_number }}?text=Halo%20Admin%20{{ $settings->shop_name }},%20saya%20mau%20konsultasi%20kacamata." target="_blank" class="fixed bottom-6 right-6 md:bottom-8 md:right-8 z-50 bg-[#25D366] text-white p-4 rounded-full shadow-[0_15px_30px_-5px_rgba(37,211,102,0.4)] hover:-translate-y-2 hover:shadow-[0_20px_35px_-5px_rgba(37,211,102,0.6)] transition-all transform duration-300 flex items-center justify-center group">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.89-4.443 9.89-9.892 0-5.446-4.446-9.892-9.893-9.892-5.452 0-9.892 4.446-9.892 9.893 0 1.99.546 3.78 1.584 5.326l-1.15 4.197 4.279-1.114zm8.682-10.298c-.347-.174-2.052-1.014-2.37-1.13-.318-.115-.55-.174-.783.174-.233.348-.897 1.13-1.1 1.362-.203.232-.405.261-.752.087-.348-.174-1.464-.54-2.79-1.733-1.03-1.03-1.724-2.302-1.927-2.65-.203-.348-.021-.537.153-.711.157-.158.348-.406.522-.609.174-.203.232-.348.348-.58.116-.232.058-.434-.029-.609-.087-.174-.783-1.885-1.072-2.58-.283-.679-.57-.587-.783-.598-.203-.01-.434-.01-.667-.01-.233 0-.609.087-.927.434-.319.348-1.218 1.189-1.218 2.899 0 1.71 1.247 3.363 1.421 3.595.174.232 2.45 3.743 5.937 5.249 3.487 1.506 3.487 1.004 4.068.946.58-.058 1.885-.773 2.146-1.52.261-.747.261-1.387.174-1.52-.087-.133-.319-.217-.666-.391z"/></svg>
        <span class="absolute right-14 bg-gray-900 text-white text-xs font-bold py-1.5 px-3 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none whitespace-nowrap shadow-lg">Chat Admin</span>
    </a>

    <!-- LENS WIZARD MODAL (Checkout) -->
    <div x-show="wizard.open" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]" @click.away="wizard.open = false">
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-gray-900">Kustomisasi Lensa</h2>
                    <p class="text-sm font-medium theme-text" x-text="`${wizard.productName} - ${wizard.variantName}`"></p>
                </div>
                <button @click="wizard.open = false" class="text-gray-400 hover:text-gray-900 p-2 bg-gray-100 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 bg-white">
                <form id="checkoutForm" action="/checkout" method="POST">
                    <!-- Data Tersembunyi -->
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="variant_id" :value="wizard.variantId">
                    <input type="hidden" name="total_price" :value="wizard.price">

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
                                    <input type="text" name="r_sph" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="-1.00">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-500 block mb-1">CYL</label>
                                    <input type="text" name="r_cyl" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="-0.50">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-500 block mb-1">AXIS</label>
                                    <input type="text" name="r_axis" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="180">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-4 gap-4 items-center">
                                <div class="col-span-1 font-extrabold text-sm text-gray-700">Kiri (OS)</div>
                                <div>
                                    <input type="text" name="l_sph" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="-1.00">
                                </div>
                                <div>
                                    <input type="text" name="l_cyl" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="-0.50">
                                </div>
                                <div>
                                    <input type="text" name="l_axis" class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="180">
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <label class="text-xs font-bold text-gray-500 block mb-1">Pupillary Distance (PD) - mm</label>
                                <input type="text" name="pd" class="w-1/3 border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="62">
                            </div>
                        </div>
                    </div>

                    <!-- Data Pelanggan -->
                    <div>
                        <h3 class="font-bold text-sm mb-3 text-gray-900">Detail Pengiriman</h3>
                        <div class="bg-gray-50 p-5 rounded-xl border border-gray-100">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="text-xs font-bold text-gray-500 block mb-1">Nama Lengkap</label>
                                    <input type="text" name="customer_name" required class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none">
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-500 block mb-1">Nomor WhatsApp</label>
                                    <input type="text" name="customer_whatsapp" required class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" placeholder="0812...">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 block mb-1">Alamat Lengkap</label>
                                <textarea name="shipping_address" required class="w-full border-gray-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-gray-900 outline-none" rows="2" placeholder="Nama Jalan, Kec, Kota..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Total & Checkout Btn -->
            <div class="p-6 border-t border-gray-100 bg-white flex justify-between items-center shadow-[0_-10px_20px_-5px_rgba(0,0,0,0.05)]">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Harga</p>
                    <p class="text-2xl font-black text-gray-900" x-text="`Rp${new Intl.NumberFormat('id-ID').format(wizard.price)}`"></p>
                </div>
                <button type="submit" form="checkoutForm" class="theme-bg text-black font-extrabold py-3.5 px-8 rounded-xl transition hover-theme shadow-lg shadow-lime-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Checkout via WA
                </button>
            </div>
        </div>
    </div>

    <!-- Script Logika Frontend Alpine.js -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('store', () => ({
                category: 'all',
                brand: 'all',
                showBookingModal: false,
                searchQuery: '',
                wizard: {
                    open: false,
                    lensType: 'Frame Only',
                    productName: '',
                    variantName: '',
                    variantId: null,
                    price: 0
                },
                openWizard(name, variant, price) {
                    this.wizard.productName = name;
                    this.wizard.variantName = variant.color_name;
                    this.wizard.variantId = variant.id;
                    this.wizard.price = price;
                    this.wizard.open = true;
                }
            }));

            Alpine.data('bannerSlider', (count) => ({
                current: 0,
                count: count,
                timer: null,
                start() {
                    this.timer = setInterval(() => { this.next() }, 4000);
                },
                pause() {
                    clearInterval(this.timer);
                },
                next() {
                    this.current = (this.current === this.count - 1) ? 0 : this.current + 1;
                },
                prev() {
                    this.current = (this.current === 0) ? this.count - 1 : this.current - 1;
                },
                goTo(index) {
                    this.current = index;
                }
            }));

            Alpine.data('productCard', (variants) => ({
                variants: variants || [],
                currentIndex: 0,
                
                get currentVariant() {
                    return this.variants.length > 0 ? this.variants[this.currentIndex] : null;
                },
                
                selectVariant(index) {
                    this.currentIndex = index;
                }
            }));
        });
    </script>
</body>
</html>

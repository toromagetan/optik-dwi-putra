@extends('layouts.admin')
@section('title', $product->exists ? 'Ubah Produk' : 'Tambah Produk')

@section('content')
@php($inputCls = 'mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100')
@php($labelCls = 'block text-sm font-bold text-stone-700')

    <div class="mb-5 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.products.index') }}" class="text-sm font-bold text-stone-500 hover:text-brand-700">← Semua Produk</a>
        @if($product->exists && $product->publication_status === 'published')
            <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="ml-auto rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-bold text-stone-600 transition hover:border-brand-400 hover:text-brand-800">Lihat di Toko ↗</a>
        @endif
    </div>

    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if($product->exists) @method('PUT') @endif
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-extrabold text-brand-950">Informasi Produk</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="{{ $labelCls }}">Nama Produk *</label>
                            <input name="name" value="{{ old('name', $product->name) }}" required class="{{ $inputCls }}" placeholder="cth: Frame Kacamata Titanium Ringan Seri A1">
                            @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="{{ $labelCls }}">SKU *</label>
                                <input name="sku" value="{{ old('sku', $product->sku) }}" required class="{{ $inputCls }}" placeholder="FRM-A1-BLK">
                                @error('sku')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">Slug URL <span class="font-medium text-stone-400">(kosongkan = otomatis)</span></label>
                                <input name="slug" value="{{ old('slug', $product->slug) }}" class="{{ $inputCls }}" placeholder="frame-kacamata-titanium-a1">
                                @error('slug')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="{{ $labelCls }}">Kategori *</label>
                                <select name="category_id" required class="{{ $inputCls }}">
                                    <option value="">— Pilih kategori —</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (nonaktif)' }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">Merek</label>
                                <select name="brand_id" class="{{ $inputCls }}">
                                    <option value="">— Tanpa merek —</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Deskripsi</label>
                            <textarea name="description" rows="6" class="{{ $inputCls }}" placeholder="Material, ukuran, keunggulan, kelengkapan…">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-5">
                <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-extrabold text-brand-950">Harga & Stok</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="{{ $labelCls }}">Harga Jual (Rp) *</label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" max="999999999999" required class="{{ $inputCls }}" placeholder="450000">
                            <p class="mt-1 text-xs text-stone-400">Angka utuh tanpa titik/koma. Perubahan harga tercatat di Audit Log.</p>
                            @error('price')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Stok (pcs) *</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" max="1000000" required class="{{ $inputCls }}" placeholder="10">
                            <p class="mt-1 text-xs text-stone-400">Stok dikelola manual. Stok 0 otomatis tampil “Stok Habis”. Perubahan stok tercatat di Audit Log.</p>
                            @error('stock')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-extrabold text-brand-950">Publikasi</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="{{ $labelCls }}">Status Publikasi</label>
                            <select name="publication_status" class="{{ $inputCls }}">
                                @foreach(\App\Models\Product::PUBLICATION_STATUSES as $status)
                                    <option value="{{ $status }}" @selected(old('publication_status', $product->publication_status) === $status)>{{ ['draft' => 'Draft (belum tampil)', 'published' => 'Published (tampil di toko)', 'archived' => 'Archived (disimpan)'][$status] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Status Ketersediaan</label>
                            <select name="availability_status" class="{{ $inputCls }}">
                                @foreach(\App\Models\Product::AVAILABILITY_STATUSES as $status)
                                    <option value="{{ $status }}" @selected(old('availability_status', $product->availability_status) === $status)>{{ ['available' => 'Tersedia', 'out_of_stock' => 'Stok habis', 'contact_for_availability' => 'Tanya ketersediaan dulu'][$status] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-stone-700">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured)) class="h-4 w-4 rounded border-stone-300 text-brand-700 focus:ring-brand-500">
                            Jadikan produk unggulan (tampil di beranda)
                        </label>
                        <button type="submit" class="w-full rounded-xl bg-brand-700 py-3 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">{{ $product->exists ? 'Simpan Perubahan' : 'Buat Produk' }}</button>
                    </div>
                </section>
            </div>
        </div>
    </form>

    @if($product->exists)
        {{-- ===== Galeri gambar ===== --}}
        <section class="mt-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-extrabold text-brand-950">Galeri Gambar <span class="text-sm font-medium text-stone-400">({{ $product->images->count() }} foto — urutan pertama = foto utama)</span></h2>
            @if($product->images->isNotEmpty())
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($product->images as $image)
                        <figure class="overflow-hidden rounded-xl border border-stone-200">
                            <img src="{{ $image->url() }}" alt="{{ $image->alt_text }}" class="aspect-[4/3] w-full object-cover">
                            <figcaption class="flex items-center justify-between gap-1 bg-stone-50 px-2 py-1.5">
                                <span class="text-[11px] font-bold text-stone-500">{{ $loop->first ? 'UTAMA' : 'Foto '.$loop->iteration }}</span>
                                <span class="flex gap-1">
                                    <form method="POST" action="{{ route('admin.products.images.move', [$product, $image]) }}">@csrf<input type="hidden" name="direction" value="up"><button @disabled($loop->first) class="rounded bg-white px-2 py-1 text-xs font-bold text-stone-600 ring-1 ring-stone-200 disabled:opacity-40" title="Geser ke depan">←</button></form>
                                    <form method="POST" action="{{ route('admin.products.images.move', [$product, $image]) }}">@csrf<input type="hidden" name="direction" value="down"><button @disabled($loop->last) class="rounded bg-white px-2 py-1 text-xs font-bold text-stone-600 ring-1 ring-stone-200 disabled:opacity-40" title="Geser ke belakang">→</button></form>
                                    <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" onsubmit="return confirm('Hapus gambar ini?')">@csrf @method('DELETE')<button class="rounded bg-red-50 px-2 py-1 text-xs font-bold text-red-600 ring-1 ring-red-200">Hapus</button></form>
                                </span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            @else
                <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800 ring-1 ring-amber-200">Produk ini belum memiliki foto. Produk tanpa foto tampil dengan ikon pengganti di toko.</p>
            @endif
            <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" class="mt-5 flex flex-col gap-3 rounded-xl bg-stone-50 p-4 ring-1 ring-stone-200 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label class="{{ $labelCls }}">Unggah Gambar Baru</label>
                    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required class="mt-1.5 block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-700 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-brand-800">
                    <p class="mt-1 text-xs text-stone-400">JPG/PNG/WebP, maks 2 MB. Tipe berkas diverifikasi dari isi berkas.</p>
                </div>
                <div class="flex-1">
                    <label class="{{ $labelCls }}">Teks Alternatif (aksesibilitas/SEO)</label>
                    <input name="alt_text" class="{{ $inputCls }}" placeholder="cth: Frame titanium tampak depan">
                </div>
                <button class="rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-brand-800">Unggah</button>
            </form>
        </section>

        {{-- ===== Tautan kanal ===== --}}
        <section class="mt-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-extrabold text-brand-950">Tautan Kanal Penjualan</h2>
            <p class="mt-1 text-sm text-stone-500">Tautan marketplace tampil di halaman produk & halaman keranjang. Hanya URL http/https yang diterima.</p>

            @if($product->channels->isNotEmpty())
                <div class="mt-4 space-y-3">
                    @foreach($product->channels as $channel)
                        <form method="POST" action="{{ route('admin.products.channels.update', [$product, $channel]) }}" class="flex flex-col gap-2 rounded-xl bg-stone-50 p-3 ring-1 ring-stone-200 lg:flex-row lg:items-center">
                            @csrf @method('PUT')
                            <select name="channel_type" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm font-medium lg:w-44">
                                @foreach(\App\Models\ProductChannel::TYPES as $type => $label)
                                    <option value="{{ $type }}" @selected($channel->channel_type === $type)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input name="label" value="{{ $channel->label }}" placeholder="Label (opsional)" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm lg:w-44">
                            <input name="url" value="{{ $channel->url }}" required placeholder="https://…" class="min-w-0 flex-1 rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm">
                            <label class="flex items-center gap-1.5 text-[13px] font-semibold text-stone-600"><input type="checkbox" name="is_active" value="1" @checked($channel->is_active) class="h-4 w-4 rounded border-stone-300 text-brand-700"> Aktif</label>
                            <span class="flex gap-1.5">
                                <button class="rounded-lg bg-brand-700 px-3 py-2 text-xs font-bold text-white hover:bg-brand-800">Simpan</button>
                            </span>
                        </form>
                        <form method="POST" action="{{ route('admin.products.channels.destroy', [$product, $channel]) }}" onsubmit="return confirm('Hapus tautan kanal ini?')" class="-mt-2 text-right">
                            @csrf @method('DELETE')
                            <button class="text-xs font-bold text-red-500 hover:text-red-700">Hapus tautan {{ $channel->displayLabel() }}</button>
                        </form>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.products.channels.store', $product) }}" class="mt-5 flex flex-col gap-2 rounded-xl border-2 border-dashed border-brand-200 bg-brand-50/50 p-4 lg:flex-row lg:items-center">
                @csrf
                <select name="channel_type" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm font-medium lg:w-44">
                    @foreach(\App\Models\ProductChannel::TYPES as $type => $label)
                        <option value="{{ $type }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input name="label" placeholder="Label (opsional)" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm lg:w-44">
                <input name="url" required placeholder="https://shopee.co.id/produk-anda" class="min-w-0 flex-1 rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm">
                <input type="hidden" name="is_active" value="1">
                <button class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-bold text-white hover:bg-brand-800">+ Tambah Tautan</button>
            </form>
        </section>
    @endif
@endsection

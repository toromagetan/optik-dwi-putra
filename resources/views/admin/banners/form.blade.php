@extends('layouts.admin')
@section('title', $banner->exists ? 'Ubah Banner' : 'Tambah Banner')

@section('content')
@php($inputCls = 'mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100')
@php($labelCls = 'block text-sm font-bold text-stone-700')
    <a href="{{ route('admin.banners.index') }}" class="text-sm font-bold text-stone-500 hover:text-brand-700">← Semua Banner</a>

    <form method="POST" enctype="multipart/form-data" action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" class="mt-4 max-w-2xl rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        @csrf
        @if($banner->exists) @method('PUT') @endif
        <div class="space-y-4">
            <div>
                <label class="{{ $labelCls }}">Gambar Banner <span class="font-medium text-stone-400">(JPG/PNG/WebP, maks 4 MB; disarankan lebar 1680×560)</span></label>
                @if($banner->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($banner->image_path) }}" alt="" class="mt-2 aspect-[3/1] w-full rounded-xl object-cover ring-1 ring-stone-200">
                    <p class="mt-1 text-xs text-stone-500">Gambar saat ini — unggah berkas baru untuk menggantinya.</p>
                @endif
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="{{ $inputCls }}">
                @error('image')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Judul Promo <span class="font-medium text-stone-400">(teks besar di atas gambar)</span></label>
                <input name="title" value="{{ old('title', $banner->title) }}" class="{{ $inputCls }}" placeholder="cth: Promo Akhir Tahun — Diskon hingga 50%">
                @error('title')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Subjudul <span class="font-medium text-stone-400">(teks kecil di bawah judul)</span></label>
                <input name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}" class="{{ $inputCls }}" placeholder="cth: Frame & lensa pilihan, hemat sampai setengah harga">
                @error('subtitle')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Tautan saat banner diklik <span class="font-medium text-stone-400">(boleh kosong)</span></label>
                <input name="link_url" value="{{ old('link_url', $banner->link_url) }}" class="{{ $inputCls }}" placeholder="/katalog atau https://...">
                @error('link_url')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelCls }}">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" min="0" class="{{ $inputCls }}">
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 self-end pb-2 text-sm font-semibold text-stone-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active ?? true)) class="h-4 w-4 rounded border-stone-300 text-brand-700 focus:ring-brand-500">
                    Aktif (tampil di beranda)
                </label>
            </div>
            <button type="submit" class="w-full rounded-xl bg-brand-700 py-3 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">{{ $banner->exists ? 'Simpan Perubahan' : 'Buat Banner' }}</button>
        </div>
    </form>
@endsection

@extends('layouts.admin')
@section('title', $review->exists ? 'Ubah Ulasan' : 'Tambah Ulasan')

@section('content')
@php($inputCls = 'mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100')
@php($labelCls = 'block text-sm font-bold text-stone-700')
    <a href="{{ route('admin.reviews.index') }}" class="text-sm font-bold text-stone-500 hover:text-brand-700">← Semua Ulasan</a>

    <form method="POST" enctype="multipart/form-data" action="{{ $review->exists ? route('admin.reviews.update', $review) : route('admin.reviews.store') }}" class="mt-4 max-w-2xl rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        @csrf
        @if($review->exists) @method('PUT') @endif
        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelCls }}">Nama Pelanggan *</label>
                    <input name="name" value="{{ old('name', $review->name) }}" required class="{{ $inputCls }}" placeholder="cth: Rina Amelia">
                    @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Kota/Asal</label>
                    <input name="location" value="{{ old('location', $review->location) }}" class="{{ $inputCls }}" placeholder="cth: Surabaya">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelCls }}">Sumber Ulasan</label>
                    <select name="source" class="{{ $inputCls }}">
                        <option value="">— pilih —</option>
                        @foreach(\App\Models\Review::SOURCES as $source)
                            <option value="{{ $source }}" @selected(old('source', $review->source) === $source)>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelCls }}">Rating *</label>
                    <select name="rating" class="{{ $inputCls }}">
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected((int) old('rating', $review->rating ?? 5) === $i)>{{ str_repeat('★', $i) }} ({{ $i }})</option>
                        @endfor
                    </select>
                    @error('rating')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="{{ $labelCls }}">Isi Ulasan</label>
                <textarea name="text" rows="4" class="{{ $inputCls }}" placeholder="Tuliskan testimoni pelanggan…">{{ old('text', $review->text) }}</textarea>
                @error('text')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Gambar Tangkapan Layar <span class="font-medium text-stone-400">(opsional — screenshot ulasan dari WhatsApp/Google, JPG/PNG/WebP maks 4 MB)</span></label>
                @if($review->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($review->image_path) }}" alt="" class="mt-2 h-44 w-auto rounded-xl object-cover object-top ring-1 ring-stone-200">
                    <p class="mt-1 text-xs text-stone-500">Gambar saat ini — unggah berkas baru untuk menggantinya.</p>
                @endif
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="{{ $inputCls }}">
                @error('image')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelCls }}">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $review->sort_order ?? 0) }}" min="0" class="{{ $inputCls }}">
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 self-end pb-2 text-sm font-semibold text-stone-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $review->is_active ?? true)) class="h-4 w-4 rounded border-stone-300 text-brand-700 focus:ring-brand-500">
                    Aktif (tampil di beranda)
                </label>
            </div>
            <button type="submit" class="w-full rounded-xl bg-brand-700 py-3 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">{{ $review->exists ? 'Simpan Perubahan' : 'Buat Ulasan' }}</button>
        </div>
    </form>
@endsection

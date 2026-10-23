@extends('layouts.admin')
@section('title', $category->exists ? 'Ubah Kategori' : 'Tambah Kategori')

@section('content')
@php($inputCls = 'mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100')
@php($labelCls = 'block text-sm font-bold text-stone-700')
    <a href="{{ route('admin.categories.index') }}" class="text-sm font-bold text-stone-500 hover:text-brand-700">← Semua Kategori</a>

    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="mt-4 max-w-2xl rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        @csrf
        @if($category->exists) @method('PUT') @endif
        <div class="space-y-4">
            <div>
                <label class="{{ $labelCls }}">Nama Kategori *</label>
                <input name="name" value="{{ old('name', $category->name) }}" required class="{{ $inputCls }}" placeholder="cth: Frame Kacamata">
                @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Slug URL <span class="font-medium text-stone-400">(kosongkan = otomatis)</span></label>
                <input name="slug" value="{{ old('slug', $category->slug) }}" class="{{ $inputCls }}" placeholder="frame-kacamata">
                @error('slug')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Deskripsi</label>
                <textarea name="description" rows="3" class="{{ $inputCls }}">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelCls }}">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0" class="{{ $inputCls }}">
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 self-end pb-2 text-sm font-semibold text-stone-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="h-4 w-4 rounded border-stone-300 text-brand-700 focus:ring-brand-500">
                    Aktif (tampil di toko)
                </label>
            </div>
            <button type="submit" class="w-full rounded-xl bg-brand-700 py-3 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">{{ $category->exists ? 'Simpan Perubahan' : 'Buat Kategori' }}</button>
        </div>
    </form>
@endsection

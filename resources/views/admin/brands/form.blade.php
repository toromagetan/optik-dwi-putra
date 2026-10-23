@extends('layouts.admin')
@section('title', $brand->exists ? 'Ubah Merek' : 'Tambah Merek')

@section('content')
@php($inputCls = 'mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100')
@php($labelCls = 'block text-sm font-bold text-stone-700')
    <a href="{{ route('admin.brands.index') }}" class="text-sm font-bold text-stone-500 hover:text-brand-700">← Semua Merek</a>

    <form method="POST" action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" class="mt-4 max-w-xl rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        @csrf
        @if($brand->exists) @method('PUT') @endif
        <div class="space-y-4">
            <div>
                <label class="{{ $labelCls }}">Nama Merek *</label>
                <input name="name" value="{{ old('name', $brand->name) }}" required class="{{ $inputCls }}" placeholder="cth: DwiVision">
                @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $labelCls }}">Slug URL <span class="font-medium text-stone-400">(kosongkan = otomatis)</span></label>
                <input name="slug" value="{{ old('slug', $brand->slug) }}" class="{{ $inputCls }}" placeholder="dwivision">
                @error('slug')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-stone-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $brand->is_active ?? true)) class="h-4 w-4 rounded border-stone-300 text-brand-700 focus:ring-brand-500">
                Aktif
            </label>
            <button type="submit" class="w-full rounded-xl bg-brand-700 py-3 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">{{ $brand->exists ? 'Simpan Perubahan' : 'Buat Merek' }}</button>
        </div>
    </form>
@endsection

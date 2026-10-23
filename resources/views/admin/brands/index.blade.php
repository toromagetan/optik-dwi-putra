@extends('layouts.admin')
@section('title', 'Merek')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <p class="text-sm text-stone-500">Merek bersifat opsional — produk boleh tanpa merek. Menghapus merek membuat produknya menjadi tanpa merek.</p>
        <a href="{{ route('admin.brands.create') }}" class="shrink-0 rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">+ Tambah Merek</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                    <th class="px-4 py-3 font-bold">Nama</th>
                    <th class="px-4 py-3 font-bold">Slug</th>
                    <th class="px-4 py-3 font-bold">Produk</th>
                    <th class="px-4 py-3 font-bold">Status</th>
                    <th class="px-4 py-3 text-right font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($brands as $brand)
                    <tr class="transition hover:bg-brand-50/40">
                        <td class="px-4 py-3 font-bold text-brand-950">{{ $brand->name }}</td>
                        <td class="px-4 py-3 text-stone-500">{{ $brand->slug }}</td>
                        <td class="px-4 py-3">{{ $brand->products_count }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-extrabold {{ $brand->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-500' }}">{{ $brand->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1.5">
                                <a href="{{ route('admin.brands.edit', $brand) }}" class="rounded-lg bg-brand-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-800">Ubah</a>
                                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Hapus merek ini?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 ring-1 ring-red-200 hover:bg-red-100">Hapus</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-stone-500">Belum ada merek.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $brands->links() }}</div>
@endsection

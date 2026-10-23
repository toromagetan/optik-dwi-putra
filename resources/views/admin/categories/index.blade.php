@extends('layouts.admin')
@section('title', 'Kategori')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <p class="text-sm text-stone-500">Kategori nonaktif tidak tampil di toko. Kategori yang masih dipakai produk tidak dapat dihapus.</p>
        <a href="{{ route('admin.categories.create') }}" class="shrink-0 rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">+ Tambah Kategori</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                    <th class="px-4 py-3 font-bold">Nama</th>
                    <th class="px-4 py-3 font-bold">Slug</th>
                    <th class="px-4 py-3 font-bold">Produk</th>
                    <th class="px-4 py-3 font-bold">Urutan</th>
                    <th class="px-4 py-3 font-bold">Status</th>
                    <th class="px-4 py-3 text-right font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($categories as $category)
                    <tr class="transition hover:bg-brand-50/40">
                        <td class="px-4 py-3 font-bold text-brand-950">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-stone-500">{{ $category->slug }}</td>
                        <td class="px-4 py-3">{{ $category->products_count }}</td>
                        <td class="px-4 py-3">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-extrabold {{ $category->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-500' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1.5">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-lg bg-brand-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-800">Ubah</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini? Kategori yang masih dipakai produk akan ditolak.')">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 ring-1 ring-red-200 hover:bg-red-100">Hapus</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-stone-500">Belum ada kategori.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $categories->links() }}</div>
@endsection

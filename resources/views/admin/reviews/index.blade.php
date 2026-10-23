@extends('layouts.admin')
@section('title', 'Ulasan Pelanggan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-brand-950">Ulasan Pelanggan</h1>
            <p class="mt-0.5 text-sm text-stone-500">Testimoni yang tampil di seksi "Apa Kata Pelanggan" pada beranda — termasuk ulasan berupa gambar tangkapan layar (screenshot chat/Google).</p>
        </div>
        <a href="{{ route('admin.reviews.create') }}" class="rounded-xl bg-brand-700 px-5 py-2.5 text-sm font-extrabold text-white shadow transition hover:bg-brand-800">+ Tambah Ulasan</a>
    </div>

    <div class="mt-5 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                    <th class="px-4 py-3 font-bold">Pelanggan</th>
                    <th class="hidden px-4 py-3 font-bold lg:table-cell">Ulasan</th>
                    <th class="px-4 py-3 font-bold">Rating</th>
                    <th class="px-4 py-3 font-bold">Status</th>
                    <th class="px-4 py-3 font-bold">Urutan</th>
                    <th class="px-4 py-3 text-right font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($reviews as $review)
                    <tr class="align-middle hover:bg-brand-50/40">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($review->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($review->image_path) }}" alt="" class="h-14 w-10 shrink-0 rounded-lg object-cover object-top ring-1 ring-stone-200">
                                @else
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-extrabold text-brand-800">{{ $review->initial() }}</span>
                                @endif
                                <span>
                                    <span class="block font-bold text-brand-950">{{ $review->name }}</span>
                                    <span class="block text-xs text-stone-500">{{ collect([$review->location, $review->source])->filter()->join(' · ') }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="hidden max-w-[340px] px-4 py-3 text-stone-600 lg:table-cell"><span class="line-clamp-2">{{ $review->text ?: '—' }}</span></td>
                        <td class="px-4 py-3 whitespace-nowrap text-gold-500" title="{{ $review->rating }}/5">
                            @for($i = 1; $i <= 5; $i++)<span class="{{ $i <= $review->rating ? '' : 'text-stone-300' }}">★</span>@endfor
                        </td>
                        <td class="px-4 py-3">
                            @if($review->is_active)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Aktif</span>
                            @else
                                <span class="rounded-full bg-stone-200 px-2.5 py-1 text-xs font-bold text-stone-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <span class="font-bold text-stone-700">{{ $review->sort_order }}</span>
                                <form method="POST" action="{{ route('admin.reviews.move', $review) }}">@csrf<input type="hidden" name="direction" value="up"><button class="rounded-lg px-1.5 py-1 text-stone-500 transition hover:bg-stone-100 hover:text-brand-700" title="Naikkan urutan">↑</button></form>
                                <form method="POST" action="{{ route('admin.reviews.move', $review) }}">@csrf<input type="hidden" name="direction" value="down"><button class="rounded-lg px-1.5 py-1 text-stone-500 transition hover:bg-stone-100 hover:text-brand-700" title="Turunkan urutan">↓</button></form>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.reviews.edit', $review) }}" class="rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-800 ring-1 ring-brand-200 transition hover:bg-brand-100">Ubah</a>
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Hapus ulasan ini? Gambarnya ikut terhapus.')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 ring-1 ring-red-200 transition hover:bg-red-100">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-stone-500">Belum ada ulasan. Tambahkan testimoni pelanggan pertama.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

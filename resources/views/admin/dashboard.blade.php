@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php($cards = [
            ['Total Produk', $stats['total'], 'bg-brand-700'],
            ['Terbit (Published)', $stats['published'], 'bg-emerald-600'],
            ['Stok Habis', $stats['out_of_stock'], 'bg-red-600'],
            ['Draft', $stats['draft'], 'bg-amber-500'],
            ['Diarsipkan', $stats['archived'], 'bg-stone-500'],
            ['Kategori', $stats['categories'], 'bg-sky-600'],
            ['Merek', $stats['brands'], 'bg-violet-600'],
        ])
        @foreach($cards as [$label, $value, $color])
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <span class="inline-block h-2.5 w-2.5 rounded-full {{ $color }}"></span>
                <p class="mt-2 text-3xl font-extrabold text-brand-950">{{ $value }}</p>
                <p class="mt-0.5 text-[13px] font-semibold text-stone-500">{{ $label }}</p>
            </div>
        @endforeach
        <a href="{{ route('admin.products.create') }}" class="flex flex-col justify-center rounded-2xl border-2 border-dashed border-brand-300 bg-brand-50 p-5 text-center transition hover:bg-brand-100">
            <p class="text-3xl font-extrabold text-brand-700">+</p>
            <p class="mt-0.5 text-[13px] font-bold text-brand-800">Tambah Produk Baru</p>
        </a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="font-extrabold text-brand-950">Stok Menipis</h2>
                <a href="{{ route('admin.products.index') }}" class="text-[13px] font-bold text-brand-700 hover:text-brand-800">Kelola produk →</a>
            </div>
            @if($lowStock->isNotEmpty())
                <ul class="mt-3 divide-y divide-stone-100">
                    @foreach($lowStock as $product)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <a href="{{ route('admin.products.edit', $product) }}" class="min-w-0">
                                <span class="block truncate text-sm font-bold text-stone-700">{{ $product->name }}</span>
                                <span class="block text-xs text-stone-400">SKU {{ $product->sku }}</span>
                            </a>
                            <span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-extrabold text-amber-700">Sisa {{ $product->stock }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-3 rounded-xl bg-brand-50 px-4 py-3 text-sm font-medium text-brand-800">Semua produk terbit memiliki stok aman (di atas 3 pcs).</p>
            @endif
        </section>

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="font-extrabold text-brand-950">Aktivitas Terakhir</h2>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-[13px] font-bold text-brand-700 hover:text-brand-800">Semua audit log →</a>
            </div>
            @if($recentActivity->isNotEmpty())
                <ul class="mt-3 divide-y divide-stone-100">
                    @foreach($recentActivity as $log)
                        <li class="py-2.5">
                            <p class="text-sm font-semibold text-stone-700">{{ $log->action }} <span class="font-normal text-stone-400">pada {{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</span></p>
                            <p class="text-xs text-stone-400">{{ $log->user?->name ?? 'Sistem' }} · {{ $log->created_at->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-3 text-sm text-stone-500">Belum ada aktivitas tercatat.</p>
            @endif
        </section>
    </div>
@endsection

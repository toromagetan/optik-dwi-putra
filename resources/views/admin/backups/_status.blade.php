@php($map = [
    'success' => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'Sukses'],
    'failed' => ['bg-red-50 text-red-700 ring-red-200', 'Gagal'],
    'skipped' => ['bg-stone-100 text-stone-500 ring-stone-200', 'Dilewati'],
    'pending' => ['bg-amber-50 text-amber-700 ring-amber-200', 'Proses'],
])
@php([$cls, $label] = $map[$status] ?? $map['pending'])
<span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $cls }}">{{ $label }}</span>

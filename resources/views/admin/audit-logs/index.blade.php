@extends('layouts.admin')
@section('title', 'Audit Log')

@section('content')
    <form method="GET" class="mb-5 flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari aksi atau entitas… (cth: product.updated)"
            class="w-full max-w-md rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100">
        <button class="rounded-xl bg-stone-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-stone-900">Cari</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                        <th class="px-4 py-3 font-bold">Waktu</th>
                        <th class="px-4 py-3 font-bold">Pengguna</th>
                        <th class="px-4 py-3 font-bold">Aksi</th>
                        <th class="px-4 py-3 font-bold">Entitas</th>
                        <th class="px-4 py-3 font-bold">Perubahan</th>
                        <th class="px-4 py-3 font-bold">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($logs as $log)
                        <tr class="align-top transition hover:bg-brand-50/40">
                            <td class="whitespace-nowrap px-4 py-3 text-stone-500">{{ $log->created_at->format('d M Y H:i') }}<span class="block text-xs text-stone-400">{{ $log->created_at->diffForHumans() }}</span></td>
                            <td class="px-4 py-3 font-semibold text-stone-700">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-brand-50 px-2.5 py-1 font-mono text-xs font-bold text-brand-800 ring-1 ring-brand-100">{{ $log->action }}</span></td>
                            <td class="px-4 py-3 text-stone-600">{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</td>
                            <td class="max-w-md px-4 py-3">
                                @if($log->changes)
                                    <ul class="space-y-0.5 text-xs text-stone-600">
                                        @foreach($log->changes as $field => $change)
                                            <li><span class="font-bold">{{ $field }}:</span>
                                                @if(is_array($change)) {{ $change[0] ?? '—' }} → <strong>{{ $change[1] ?? '—' }}</strong>
                                                @else {{ is_scalar($change) ? $change : json_encode($change) }} @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-xs text-stone-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-stone-500">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-stone-500">Belum ada catatan audit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5">{{ $logs->links() }}</div>
@endsection

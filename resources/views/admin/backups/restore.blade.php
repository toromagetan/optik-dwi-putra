@extends('layouts.admin')
@section('title', 'Restore Backup')

@section('content')
@php($inputCls = 'mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100')

    <div class="max-w-3xl">
        <section class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm">
            <h2 class="font-extrabold text-red-800">Restore akan MENGGANTI seluruh data saat ini</h2>
            <p class="mt-2 text-sm leading-relaxed text-stone-600">
                Database dan semua foto/file unggahan akan dikembalikan ke keadaan pada arsip di bawah ini.
                Perubahan apa pun yang terjadi setelah arsip ini dibuat akan <strong>hilang</strong>.
                Sebelum restore berjalan, sistem otomatis membuat <strong>backup keselamatan</strong> dari keadaan saat ini,
                sehingga hasil restore masih bisa dibatalkan dengan me-restore backup keselamatan itu.
            </p>

            <dl class="mt-4 space-y-1.5 rounded-xl bg-stone-50 p-4 text-sm text-stone-600 ring-1 ring-stone-200">
                <div class="flex gap-2"><dt class="w-36 font-bold text-stone-700">Arsip</dt><dd class="font-mono text-xs font-semibold">{{ $log->filename }}</dd></div>
                <div class="flex gap-2"><dt class="w-36 font-bold text-stone-700">Dibuat</dt><dd>{{ $log->created_at->format('d M Y H:i') }} ({{ $log->created_at->diffForHumans() }})</dd></div>
                <div class="flex gap-2"><dt class="w-36 font-bold text-stone-700">Ukuran</dt><dd>{{ $log->size_human }}{{ $log->encrypted ? ' — terenkripsi AES-256' : '' }}</dd></div>
                <div class="flex gap-2"><dt class="w-36 font-bold text-stone-700">Sumber</dt><dd>{{ $sourceNote }}</dd></div>
            </dl>

            @if(! $restorePasswordSet)
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                    Password restore belum diatur — restore belum dapat dipakai. Atur dulu di halaman Backup bagian “Password Restore”.
                </div>
                <a href="{{ route('admin.backups.index') }}" class="mt-5 inline-block rounded-xl bg-stone-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-stone-900">Kembali ke Backup</a>
            @else
                <form method="POST" action="{{ route('admin.backups.restore', $log) }}" class="mt-5">
                    @csrf
                    <label class="block text-sm font-bold text-stone-700">Password Restore (khusus owner)</label>
                    <input type="password" name="restore_password" required autocomplete="off" class="{{ $inputCls }}" placeholder="Masukkan password restore">
                    <p class="mt-1 text-xs text-stone-400">Berbeda dari kata sandi login admin. Percobaan dibatasi 5 kali per 10 menit.</p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <button class="rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow hover:bg-red-700">Ya, Restore Sekarang</button>
                        <a href="{{ route('admin.backups.index') }}" class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-stone-700 ring-1 ring-stone-300 hover:bg-stone-50">Batal</a>
                    </div>
                </form>
            @endif
        </section>
    </div>
@endsection

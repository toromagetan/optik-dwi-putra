@extends('layouts.admin')
@section('title', 'Backup')

@section('content')
@php($inputCls = 'mt-1.5 w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-100')
@php($labelCls = 'block text-sm font-bold text-stone-700')

    <p class="mb-5 max-w-3xl text-sm leading-relaxed text-stone-500">Backup berisi dump database dan seluruh foto/file unggahan toko dalam satu arsip terenkripsi. Arsip disimpan di server dan — bila Google Drive dikonfigurasi — diunggah otomatis ke folder Drive. Backup otomatis berjalan setiap hari pukul {{ $scheduleTime }} WIB (membutuhkan cron di server).</p>

    {{-- Status & aksi --}}
    <section class="mb-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-brand-950">Status Backup</h2>
                <dl class="mt-3 space-y-1.5 text-sm text-stone-600">
                    <div class="flex gap-2">
                        <dt class="w-40 font-bold text-stone-700">Google Drive</dt>
                        <dd>
                            @if($driveConfigured)
                                <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-800 ring-1 ring-brand-200">AKTIF — siap unggah</span>
                            @elseif($settings['enabled'])
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800 ring-1 ring-amber-200">AKTIF, BELUM LENGKAP</span>
                                <span class="block text-xs text-stone-400 mt-1">Perlu Folder ID dan berkas kredensial service account.</span>
                            @else
                                <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-bold text-stone-600 ring-1 ring-stone-200">NONAKTIF — hanya lokal</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-40 font-bold text-stone-700">Kredensial</dt>
                        <dd>{{ $saUploaded ? 'Sudah diunggah'.($settings['sa_email'] ? ' ('.$settings['sa_email'].')' : '') : 'Belum diunggah' }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-40 font-bold text-stone-700">Backup terakhir</dt>
                        <dd>
                            @if($lastLog)
                                {{ $lastLog->created_at->format('d M Y H:i') }} — {{ $lastLog->filename }}
                                (lokal: {{ ['success' => 'sukses', 'failed' => 'gagal', 'skipped' => 'dilewati', 'pending' => 'proses'][$lastLog->local_status] ?? $lastLog->local_status }}, Drive: {{ ['success' => 'sukses', 'failed' => 'gagal', 'skipped' => 'dilewati', 'pending' => 'proses'][$lastLog->drive_status] ?? $lastLog->drive_status }})
                            @else
                                Belum pernah ada backup.
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
            <form method="POST" action="{{ route('admin.backups.run') }}">
                @csrf
                <button class="rounded-xl bg-brand-700 px-5 py-3 text-sm font-bold text-white shadow hover:bg-brand-800">Backup Sekarang</button>
            </form>
        </div>
    </section>

    {{-- Pengaturan Drive --}}
    <section class="mb-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="font-extrabold text-brand-950">Pengaturan Google Drive</h2>
        <p class="mt-1 text-sm text-stone-500">Folder tujuan harus berada di <strong>Shared Drive</strong> (Google Workspace) dan dibagikan ke email service account sebagai Editor — service account tidak bisa menyimpan berkas di My Drive pribadi.</p>

        <form method="POST" action="{{ route('admin.backups.settings') }}" enctype="multipart/form-data" class="mt-4">
            @csrf @method('PUT')
            <label class="flex items-center gap-3 text-sm font-bold text-stone-700">
                <input type="checkbox" name="backup_enabled" value="1" @checked(old('backup_enabled', $settings['enabled'])) class="h-4 w-4 rounded border-stone-300 text-brand-700 focus:ring-brand-200">
                Aktifkan unggah backup ke Google Drive
            </label>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelCls }}">Folder ID Google Drive</label>
                    <input type="text" name="backup_drive_folder_id" value="{{ old('backup_drive_folder_id', $settings['folder_id']) }}" class="{{ $inputCls }}" placeholder="cth: 1aBcDeFgHiJkLmNoPqRsTuVwXyZ">
                    <p class="mt-1 text-xs text-stone-400">Bagian terakhir URL folder Drive: drive.google.com/drive/folders/<strong>FOLDER_ID</strong>.</p>
                    @error('backup_drive_folder_id')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Berkas Kredensial Service Account (.json)</label>
                    <input type="file" name="sa_file" accept=".json,application/json" class="mt-1.5 block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-700 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-brand-800">
                    <p class="mt-1 text-xs text-stone-400">Disimpan aman di server (di luar folder publik, izin berkas 0600) dan tidak pernah ditampilkan kembali. Kosongkan bila tidak mengganti.</p>
                    @error('sa_file')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Retensi lokal (arsip terbaru disimpan)</label>
                    <input type="number" name="backup_retention_local" value="{{ old('backup_retention_local', $settings['retention_local']) }}" min="1" max="50" class="{{ $inputCls }}">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Retensi Drive (arsip terbaru disimpan)</label>
                    <input type="number" name="backup_retention_drive" value="{{ old('backup_retention_drive', $settings['retention_drive']) }}" min="1" max="100" class="{{ $inputCls }}">
                </div>
            </div>

            <button class="mt-5 rounded-xl bg-stone-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-stone-900">Simpan Pengaturan Backup</button>
        </form>
    </section>

    {{-- Password restore --}}
    <section class="mb-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="font-extrabold text-brand-950">Password Restore (Khusus Owner)</h2>
        <p class="mt-1 max-w-3xl text-sm text-stone-500">Restore hanya dapat dijalankan dengan password khusus ini — terpisah dari kata sandi login admin, tersimpan sebagai hash (tidak dapat dilihat kembali). Status saat ini:
            @if($restorePasswordSet)
                <strong class="text-brand-700">sudah diatur</strong>.
            @else
                <strong class="text-amber-700">belum diatur — restore belum dapat dipakai</strong>.
            @endif
        </p>

        <form method="POST" action="{{ route('admin.backups.restore-password') }}" class="mt-4">
            @csrf @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                @if($restorePasswordSet)
                    <div>
                        <label class="{{ $labelCls }}">Password restore saat ini</label>
                        <input type="password" name="current_restore_password" required autocomplete="off" class="{{ $inputCls }}">
                        @error('current_restore_password')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                @else
                    <div>
                        <label class="{{ $labelCls }}">Kata sandi akun admin Anda (konfirmasi)</label>
                        <input type="password" name="current_admin_password" required autocomplete="off" class="{{ $inputCls }}">
                        @error('current_admin_password')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endif
                <div>
                    <label class="{{ $labelCls }}">Password restore baru (min. 10 karakter)</label>
                    <input type="password" name="new_restore_password" required autocomplete="new-password" class="{{ $inputCls }}">
                    @error('new_restore_password')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $labelCls }}">Ulangi password restore baru</label>
                    <input type="password" name="new_restore_password_confirmation" required autocomplete="new-password" class="{{ $inputCls }}">
                </div>
            </div>
            <button class="mt-5 rounded-xl bg-stone-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-stone-900">{{ $restorePasswordSet ? 'Ganti Password Restore' : 'Atur Password Restore' }}</button>
        </form>
    </section>

    {{-- Riwayat --}}
    <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                        <th class="px-4 py-3 font-bold">Waktu</th>
                        <th class="px-4 py-3 font-bold">Berkas</th>
                        <th class="px-4 py-3 font-bold">Ukuran</th>
                        <th class="px-4 py-3 font-bold">Lokal</th>
                        <th class="px-4 py-3 font-bold">Drive</th>
                        <th class="px-4 py-3 font-bold">Pemicu</th>
                        <th class="px-4 py-3 font-bold">Oleh</th>
                        <th class="px-4 py-3 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($logs as $log)
                        <tr class="align-top transition hover:bg-brand-50/40">
                            <td class="whitespace-nowrap px-4 py-3 text-stone-500">{{ $log->created_at->format('d M Y H:i') }}<span class="block text-xs text-stone-400">{{ $log->created_at->diffForHumans() }}</span></td>
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-stone-700">{{ $log->filename }}@if($log->encrypted)<span class="block font-sans text-[11px] font-bold text-brand-700">terenkripsi AES-256</span>@endif
                                @if($log->notes)<span class="mt-1 block max-w-xs font-sans text-[11px] font-normal text-stone-400">{{ $log->notes }}</span>@endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-stone-600">{{ $log->size_human }}</td>
                            <td class="px-4 py-3">@include('admin.backups._status', ['status' => $log->local_status])</td>
                            <td class="px-4 py-3">@include('admin.backups._status', ['status' => $log->drive_status])</td>
                            <td class="px-4 py-3 text-stone-600">{{ $log->trigger === 'manual' ? 'Manual' : 'Terjadwal' }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($log->local_status === 'success')
                                    <a href="{{ route('admin.backups.download', $log) }}" class="font-bold text-brand-700 hover:text-brand-800">Unduh</a>
                                    <span class="mx-1 text-stone-300">|</span>
                                    <a href="{{ route('admin.backups.restore.form', $log) }}" class="font-bold text-red-600 hover:text-red-700">Restore</a>
                                @else
                                    <span class="text-stone-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-stone-500">Belum ada riwayat backup. Klik “Backup Sekarang” untuk membuat yang pertama.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-5">{{ $logs->links() }}</div>

    <p class="mt-4 text-xs leading-relaxed text-stone-400">Restore dari panel: klik <strong>Restore</strong> pada baris arsip, lalu masukkan password restore khusus owner. Sistem selalu membuat backup keselamatan keadaan saat ini sebelum restore berjalan. Restore juga dapat dilakukan manual di server dengan <span class="font-mono">php artisan backup:decrypt &lt;berkas&gt;.zip.enc</span>.</p>
@endsection

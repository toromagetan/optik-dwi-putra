<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BackupLog;
use App\Models\StoreSetting;
use App\Services\Backup\BackupRestoreService;
use App\Services\Backup\BackupService;
use App\Services\Backup\RestoreException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class BackupController extends Controller
{
    public function index(BackupService $service, BackupRestoreService $restore)
    {
        return view('admin.backups.index', [
            'logs' => BackupLog::with('user')->latest()->paginate(15),
            'settings' => $service->settings(),
            'driveConfigured' => $service->driveConfigured(),
            'saUploaded' => is_file($service->serviceAccountPath()),
            'lastLog' => BackupLog::latest()->first(),
            'scheduleTime' => (string) config('backup.schedule_time', '02:30'),
            'restorePasswordSet' => $restore->passwordIsSet(),
        ]);
    }

    /** Jalankan backup manual sekarang. */
    public function store(BackupService $service)
    {
        $log = $service->run('manual', auth()->user());

        if ($log->local_status !== 'success') {
            return back()->with('error', 'Backup gagal: '.($log->notes ?? 'kesalahan tidak dikenal.'));
        }

        $pesan = "Backup {$log->filename} berhasil dibuat ({$log->size_human}).";
        $pesan .= $log->drive_status === 'success'
            ? ' Arsip juga terunggah ke Google Drive.'
            : ' Status Drive: '.$log->drive_status.($log->notes ? ' — '.$log->notes : '');

        return back()->with('success', $pesan);
    }

    /** Simpan pengaturan Drive + kredensial service account. */
    public function updateSettings(Request $request, BackupService $service)
    {
        $data = $request->validate([
            'backup_drive_folder_id' => ['nullable', 'string', 'max:128', 'regex:/^[A-Za-z0-9_-]*$/'],
            'backup_retention_local' => ['required', 'integer', 'min:1', 'max:50'],
            'backup_retention_drive' => ['required', 'integer', 'min:1', 'max:100'],
            'sa_file' => ['nullable', 'file', 'mimetypes:application/json,text/plain', 'max:64'],
        ]);

        $changed = [];

        $enabled = $request->boolean('backup_enabled') ? '1' : '0';
        if (StoreSetting::get('backup_enabled') !== $enabled) {
            StoreSetting::set('backup_enabled', $enabled);
            $changed[] = 'backup_enabled';
        }

        $values = [
            'backup_drive_folder_id' => trim((string) ($data['backup_drive_folder_id'] ?? '')) ?: null,
            'backup_retention_local' => (string) $data['backup_retention_local'],
            'backup_retention_drive' => (string) $data['backup_retention_drive'],
        ];
        foreach ($values as $key => $value) {
            if (StoreSetting::get($key) !== $value) {
                StoreSetting::set($key, $value);
                $changed[] = $key;
            }
        }

        if ($request->hasFile('sa_file')) {
            $json = json_decode((string) file_get_contents($request->file('sa_file')->getRealPath()), true);
            if (! is_array($json)
                || ($json['type'] ?? null) !== 'service_account'
                || empty($json['client_email'])
                || empty($json['private_key'])) {
                return back()->withErrors(['sa_file' => 'Berkas bukan JSON service account Google yang valid (butuh type=service_account, client_email, private_key).']);
            }

            $path = $service->serviceAccountPath();
            File::ensureDirectoryExists(dirname($path));
            File::put($path, (string) json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            @chmod($path, 0600);

            StoreSetting::set('backup_sa_email', (string) $json['client_email']);
            $changed[] = 'service_account';
        }

        if ($changed !== []) {
            $anchor = StoreSetting::query()->firstOrCreate(['setting_key' => 'store_name']);
            AuditLog::record('backup.settings_updated', $anchor, ['keys' => $changed]);
        }

        return back()->with('success', 'Pengaturan backup berhasil disimpan.');
    }

    /** Atur / ganti password khusus owner untuk restore. */
    public function updateRestorePassword(Request $request, BackupRestoreService $restore)
    {
        $data = $request->validate([
            'current_restore_password' => ['nullable', 'string'],
            'current_admin_password' => ['nullable', 'string'],
            'new_restore_password' => ['required', 'string', 'min:10', 'max:255', 'confirmed'],
        ]);

        if ($restore->passwordIsSet()) {
            if (! $restore->verifyPassword((string) ($data['current_restore_password'] ?? ''))) {
                return back()->withErrors(['current_restore_password' => 'Password restore saat ini salah.']);
            }
        } else {
            if (! Hash::check((string) ($data['current_admin_password'] ?? ''), (string) auth()->user()->password)) {
                return back()->withErrors(['current_admin_password' => 'Konfirmasi kata sandi akun admin Anda salah.']);
            }
        }

        $restore->setPassword($data['new_restore_password']);

        $anchor = StoreSetting::query()->firstOrCreate(['setting_key' => 'store_name']);
        AuditLog::record('backup.restore_password_updated', $anchor, []);

        return back()->with('success', 'Password restore berhasil disimpan. Simpan di tempat aman — password ini tidak dapat dilihat kembali.');
    }

    /** Halaman konfirmasi restore satu arsip. */
    public function showRestoreForm(BackupLog $backup, BackupRestoreService $restore)
    {
        $localAvailable = is_file(((string) config('backup.directory')).DIRECTORY_SEPARATOR.basename($backup->filename));
        $sourceNote = $localAvailable
            ? 'Berkas lokal di server'
            : ($backup->drive_file_id
                ? 'Akan diunduh dari Google Drive (salinan lokal sudah terhapus retensi)'
                : 'Salinan lokal tidak tersedia di server (terhapus retensi) dan tidak ada salinan Drive — arsip ini tidak dapat di-restore');

        return view('admin.backups.restore', [
            'log' => $backup,
            'restorePasswordSet' => $restore->passwordIsSet(),
            'localAvailable' => $localAvailable,
            'sourceNote' => $sourceNote,
        ]);
    }

    /** Jalankan restore (digerbang password restore + pembatas percobaan). */
    public function restore(Request $request, BackupLog $backup, BackupRestoreService $restore)
    {
        $key = 'backup-restore:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->with('error', 'Terlalu banyak percobaan restore. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.');
        }

        $data = $request->validate([
            'restore_password' => ['required', 'string'],
        ]);

        try {
            $safety = $restore->restore($backup, $data['restore_password'], auth()->user());
        } catch (RestoreException $e) {
            RateLimiter::hit($key, 600);

            return back()->with('error', $e->getMessage());
        }

        RateLimiter::clear($key);

        return redirect()->route('admin.backups.index')
            ->with('success', "Restore dari {$backup->filename} selesai. Backup keselamatan keadaan sebelumnya tersimpan sebagai {$safety->filename} di server.");
    }

    /** Unduh salinan lokal arsip backup. */
    public function download(BackupLog $backup)
    {
        $path = ((string) config('backup.directory')).DIRECTORY_SEPARATOR.basename($backup->filename);

        if ($backup->local_status !== 'success' || ! is_file($path)) {
            return back()->with('error', 'Berkas backup lokal sudah tidak tersedia (mungkin terhapus oleh retensi). Salinan Drive tetap ada bila status Drive sukses.');
        }

        AuditLog::record('backup.downloaded', $backup, ['filename' => $backup->filename]);

        return response()->download($path, $backup->filename);
    }
}

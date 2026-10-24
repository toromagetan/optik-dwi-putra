<?php

namespace App\Services\Backup;

use App\Models\AuditLog;
use App\Models\BackupLog;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use ZipArchive;

/**
 * Restore backup dari panel admin (PRD §11.10) — digerbang PASSWORD
 * KHUSUS OWNER yang terpisah dari kata sandi login admin.
 *
 * Urutan yang aman (pola yang sama dengan Lentera M6):
 * 1. Verifikasi password restore (hash bcrypt di StoreSetting).
 * 2. Pastikan arsip tersedia: berkas lokal, atau unduh dari Drive bila
 *    salinan lokal sudah terhapus retensi.
 * 3. Buat BACKUP KESELAMATAN keadaan saat ini (trigger "pre-restore",
 *    tanpa unggah) — bila langkah ini gagal, restore DIBATALKAN.
 * 4. Dekripsi + ekstrak dengan pagar zip-slip, validasi isi arsip.
 * 5. Impor database, lalu pulihkan berkas unggahan, lalu bersihkan cache.
 * 6. Audit "backup.restored" ditulis SESUDAH restore selesai agar tidak
 *    ikut tergantikan oleh isi dump lama.
 */
class BackupRestoreService
{
    public const SETTING_KEY = 'backup_restore_password_hash';

    public function __construct(
        private readonly BackupService $backups,
        private readonly BackupEncrypter $encrypter,
        private readonly DatabaseImporter $importer,
    ) {}

    public function passwordIsSet(): bool
    {
        return ! empty(StoreSetting::get(self::SETTING_KEY));
    }

    public function setPassword(string $plain): void
    {
        StoreSetting::set(self::SETTING_KEY, Hash::make($plain));
    }

    public function verifyPassword(string $plain): bool
    {
        $hash = StoreSetting::get(self::SETTING_KEY);

        return $hash !== null && $hash !== '' && Hash::check($plain, $hash);
    }

    /**
     * Jalankan restore dari satu catatan backup.
     *
     * @return BackupLog catatan backup keselamatan pra-restore
     *
     * @throws RestoreException pesan aman untuk ditampilkan ke admin
     */
    public function restore(BackupLog $log, string $password, ?User $user = null): BackupLog
    {
        if (! $this->passwordIsSet()) {
            throw new RestoreException('Password restore belum diatur. Atur dulu di Panel Admin → Backup sebelum restore dapat dipakai.');
        }
        if (! $this->verifyPassword($password)) {
            throw new RestoreException('Password restore salah — restore dibatalkan, tidak ada data yang berubah.');
        }
        if ($log->local_status !== 'success') {
            throw new RestoreException('Catatan backup ini tidak berstatus sukses sehingga tidak dapat di-restore.');
        }

        $source = $this->ensureLocalArchive($log);

        // Backup keselamatan keadaan saat ini (wajib berhasil).
        $safety = $this->backups->run('pre-restore', $user, upload: false);
        if ($safety->local_status !== 'success') {
            throw new RestoreException('Backup keselamatan pra-restore gagal dibuat — restore dibatalkan demi keamanan. Sebab: '.($safety->notes ?? 'tidak diketahui'));
        }

        $work = ((string) config('backup.directory')).DIRECTORY_SEPARATOR.'.restore-'.uniqid();
        File::ensureDirectoryExists($work);

        try {
            $zipPath = $work.DIRECTORY_SEPARATOR.'arsip.zip';
            $this->encrypter->decryptFile($source, $zipPath);

            $extractDir = $work.DIRECTORY_SEPARATOR.'isi';
            $this->extractSafely($zipPath, $extractDir);

            if (! is_file($extractDir.DIRECTORY_SEPARATOR.'MANIFEST.txt')) {
                throw new RestoreException('Arsip tidak memiliki MANIFEST.txt — bukan arsip backup Dwi Putra, restore dibatalkan.');
            }

            // Impor database dulu (titik gagal paling mungkin), baru berkas.
            $this->importer->import($extractDir);
            $this->restorePublicFiles($extractDir);
        } catch (RestoreException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RestoreException('Restore gagal di tengah jalan: '.$e->getMessage().' Keadaan awal masih dapat dipulihkan dari backup keselamatan '.$safety->filename.'.');
        } finally {
            File::deleteDirectory($work);
        }

        // Cache pengaturan lama (dari dump) tidak boleh bertahan.
        Artisan::call('cache:clear');

        AuditLog::record('backup.restored', $log, [
            'filename' => $log->filename,
            'safety_backup' => $safety->filename,
        ]);

        return $safety;
    }

    /** Pastikan arsip ada di disk lokal; unduh dari Drive bila perlu. */
    private function ensureLocalArchive(BackupLog $log): string
    {
        $path = ((string) config('backup.directory')).DIRECTORY_SEPARATOR.basename($log->filename);
        if (is_file($path)) {
            return $path;
        }

        if ($log->drive_file_id && $this->backups->driveConfigured()) {
            $client = GoogleDriveClient::fromFile($this->backups->serviceAccountPath());
            $client->downloadTo($log->drive_file_id, $path);
            if (is_file($path)) {
                return $path;
            }
        }

        throw new RestoreException('Berkas arsip tidak ditemukan di server dan tidak ada salinan Drive yang dapat diunduh — restore dibatalkan.');
    }

    /** Ekstrak ZIP dengan pagar zip-slip (entri tidak boleh keluar direktori tujuan). */
    private function extractSafely(string $zipPath, string $extractDir): void
    {
        File::ensureDirectoryExists($extractDir);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new RestoreException('Arsip hasil dekripsi bukan ZIP yang valid — restore dibatalkan.');
        }

        $base = realpath($extractDir) ?: $extractDir;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if (str_contains($name, '..') || str_starts_with($name, '/') || str_starts_with($name, '\\')) {
                $zip->close();
                throw new RestoreException("Arsip berisi entri mencurigakan ({$name}) — restore dibatalkan.");
            }
            $target = $base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $name);
            if (! str_starts_with($target, $base)) {
                $zip->close();
                throw new RestoreException("Arsip berisi entri di luar direktori tujuan ({$name}) — restore dibatalkan.");
            }
        }
        $zip->extractTo($extractDir);
        $zip->close();
    }

    /** Ganti isi storage/app/public dengan isi folder public/ dari arsip. */
    private function restorePublicFiles(string $extractDir): void
    {
        $source = $extractDir.DIRECTORY_SEPARATOR.'public';
        $destination = storage_path('app/public');

        File::ensureDirectoryExists($destination);
        if (! is_dir($source)) {
            return; // Arsip tanpa berkas unggahan: tidak ada yang dipulihkan.
        }

        // Kosongkan tujuan (keadaan lama sudah aman di backup keselamatan),
        // lalu salin isi arsip. Berkas .gitignore repo dipertahankan:
        // zip backup tidak memuat dotfile, jadi tanpa ini restore akan
        // menghapusnya dari checkout.
        $gitignore = is_file($destination.DIRECTORY_SEPARATOR.'.gitignore')
            ? file_get_contents($destination.DIRECTORY_SEPARATOR.'.gitignore')
            : null;
        File::cleanDirectory($destination);
        File::copyDirectory($source, $destination);
        if ($gitignore !== null && $gitignore !== false) {
            file_put_contents($destination.DIRECTORY_SEPARATOR.'.gitignore', $gitignore);
        }
    }
}

<?php

namespace App\Services\Backup;

use App\Models\AuditLog;
use App\Models\BackupLog;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Mesin backup Optik Dwi Putra (PRD §11.9), pola yang sama dengan Artesis:
 *
 * 1. Dump database + seluruh berkas unggahan (storage/app/public) menjadi
 *    satu arsip ZIP, lalu dienkripsi AES-256 (kunci dari env/APP_KEY).
 * 2. Arsip disimpan lokal di storage/app/backups (retensi N terbaru).
 * 3. Bila backup Drive aktif & kredensial lengkap, arsip yang sama
 *    diunggah ke folder Google Drive (retensi M terbaru). Kegagalan
 *    unggah TIDAK menggagalkan backup lokal (gagal-lembut) dan tercatat
 *    di BackupLog + AuditLog.
 */
class BackupService
{
    public function __construct(private readonly BackupEncrypter $encrypter) {}

    /**
     * Pengaturan backup dari panel admin (StoreSetting awalan backup_).
     *
     * @return array{enabled: bool, folder_id: string, retention_local: int, retention_drive: int, sa_email: ?string}
     */
    public function settings(): array
    {
        $defaults = config('backup.defaults');

        return [
            'enabled' => StoreSetting::get('backup_enabled', $defaults['enabled'] ? '1' : '0') === '1',
            'folder_id' => (string) (StoreSetting::get('backup_drive_folder_id', $defaults['folder_id']) ?? ''),
            'retention_local' => max(1, (int) (StoreSetting::get('backup_retention_local', (string) $defaults['retention_local']) ?? $defaults['retention_local'])),
            'retention_drive' => max(1, (int) (StoreSetting::get('backup_retention_drive', (string) $defaults['retention_drive']) ?? $defaults['retention_drive'])),
            'sa_email' => StoreSetting::get('backup_sa_email'),
        ];
    }

    public function serviceAccountPath(): string
    {
        return (string) config('backup.service_account_path');
    }

    /** Drive siap dipakai: unggahan aktif + kredensial tersimpan + folder terisi. */
    public function driveConfigured(): bool
    {
        $settings = $this->settings();

        return $settings['enabled']
            && $settings['folder_id'] !== ''
            && is_file($this->serviceAccountPath());
    }

    /** Jalankan satu siklus backup penuh dan catat hasilnya. */
    public function run(string $trigger = 'manual', ?User $user = null, bool $upload = true): BackupLog
    {
        $log = BackupLog::create([
            'filename' => 'pending',
            'local_status' => 'pending',
            'drive_status' => 'pending',
            'trigger' => $trigger,
            'user_id' => $user?->id,
        ]);

        $notes = [];

        try {
            $finalPath = $this->buildArchive($log);
            $log->filename = basename($finalPath);
            $log->size_bytes = filesize($finalPath) ?: null;
            $log->encrypted = BackupEncrypter::isEncryptedFile($finalPath);
            $log->local_status = 'success';
            $this->pruneLocal();
        } catch (\Throwable $e) {
            $log->local_status = 'failed';
            $log->drive_status = 'skipped';
            $log->notes = 'Backup lokal gagal: '.$e->getMessage();
            $log->save();
            AuditLog::record('backup.failed', $log, ['error' => $e->getMessage()]);

            return $log;
        }

        // --- Unggah ke Google Drive (gagal-lembut) ---
        $settings = $this->settings();
        if (! $upload) {
            $log->drive_status = 'skipped';
            $notes[] = 'Unggah dilewati atas permintaan (--no-upload).';
        } elseif (! $settings['enabled']) {
            $log->drive_status = 'skipped';
            $notes[] = 'Unggah Drive nonaktif di pengaturan backup.';
        } elseif ($settings['folder_id'] === '' || ! is_file($this->serviceAccountPath())) {
            $log->drive_status = 'skipped';
            $notes[] = 'Kredensial service account / Folder ID Drive belum lengkap.';
        } else {
            try {
                $client = GoogleDriveClient::fromFile($this->serviceAccountPath());
                $log->drive_file_id = $client->upload($finalPath, basename($finalPath), $settings['folder_id']);
                $log->drive_status = 'success';
                $this->pruneDrive($client, $settings);
            } catch (\Throwable $e) {
                $log->drive_status = 'failed';
                $notes[] = 'Unggah Drive gagal (backup lokal tetap aman): '.$e->getMessage();
            }
        }

        $log->notes = $notes !== [] ? implode(' ', $notes) : null;
        $log->save();

        AuditLog::record('backup.created', $log, [
            'filename' => $log->filename,
            'local_status' => $log->local_status,
            'drive_status' => $log->drive_status,
        ]);

        return $log;
    }

    /** Bangun arsip ZIP terenkripsi di direktori backup; kembalikan path final. */
    private function buildArchive(BackupLog $log): string
    {
        $directory = (string) config('backup.directory');
        File::ensureDirectoryExists($directory);

        $base = 'dwiputra-backup-'.now()->format('Ymd-His');
        $work = $directory.DIRECTORY_SEPARATOR.'.tmp-'.$base;
        File::ensureDirectoryExists($work);

        try {
            $entries = [];

            $driver = (string) config('database.default');
            if ($driver === 'mysql') {
                $dumpPath = $work.DIRECTORY_SEPARATOR.'database.sql';
                $this->dumpMysql($dumpPath);
                $entries[$dumpPath] = 'database.sql';
            } elseif ($driver === 'sqlite') {
                $dbFile = (string) config('database.connections.sqlite.database');
                if (! is_file($dbFile)) {
                    throw new RuntimeException('Berkas database SQLite tidak ditemukan.');
                }
                $entries[$dbFile] = 'database.sqlite';
            } else {
                throw new RuntimeException("Driver database '{$driver}' belum didukung modul backup.");
            }

            // Berkas unggahan publik (foto produk, banner, logo, dsb.)
            $publicPath = storage_path('app/public');
            if (is_dir($publicPath)) {
                $files = File::allFiles($publicPath);
                foreach ($files as $file) {
                    $entries[$file->getPathname()] = 'public/'.$file->getRelativePathname();
                }
            }

            $manifest = "Optik Dwi Putra — arsip backup\n"
                .'Dibuat: '.now()->toIso8601String()."\n"
                ."Pemicu: {$log->trigger}\n"
                ."Isi: dump database + storage/app/public\n";
            file_put_contents($work.DIRECTORY_SEPARATOR.'MANIFEST.txt', $manifest);
            $entries[$work.DIRECTORY_SEPARATOR.'MANIFEST.txt'] = 'MANIFEST.txt';

            $zipPath = $directory.DIRECTORY_SEPARATOR.$base.'.zip';
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Tidak dapat membuat arsip ZIP backup.');
            }
            foreach ($entries as $path => $name) {
                $zip->addFile($path, $name);
            }
            $zip->close();

            $finalPath = $zipPath.'.enc';
            $this->encrypter->encryptFile($zipPath, $finalPath);
            File::delete($zipPath);

            return $finalPath;
        } finally {
            File::deleteDirectory($work);
        }
    }

    private function dumpMysql(string $dumpPath): void
    {
        $config = config('database.connections.mysql');

        $process = new Process([
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--no-tablespaces',
            '-h', (string) ($config['host'] ?? '127.0.0.1'),
            '-P', (string) ($config['port'] ?? '3306'),
            '-u', (string) ($config['username'] ?? 'root'),
            (string) ($config['database'] ?? ''),
        ], null, ['MYSQL_PWD' => (string) ($config['password'] ?? '')], null, 600);

        $process->run();

        if (! $process->isSuccessful() || $process->getOutput() === '') {
            throw new RuntimeException('mysqldump gagal: '.trim($process->getErrorOutput()));
        }

        file_put_contents($dumpPath, $process->getOutput());
    }

    /** Retensi lokal: sisakan N arsip terbaru, hapus sisanya. */
    private function pruneLocal(): void
    {
        $directory = (string) config('backup.directory');
        $keep = $this->settings()['retention_local'];

        $files = collect(File::files($directory))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'dwiputra-backup-'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        $files->slice($keep)->each(fn ($f) => File::delete($f->getPathname()));
    }

    /** Retensi Drive: sisakan M arsip backup terbaru di folder. */
    private function pruneDrive(GoogleDriveClient $client, array $settings): void
    {
        try {
            $files = collect($client->listFiles($settings['folder_id']))
                ->filter(fn ($f) => str_starts_with((string) ($f['name'] ?? ''), 'dwiputra-backup-'))
                ->values();

            foreach ($files->slice($settings['retention_drive']) as $file) {
                $client->delete((string) $file['id']);
            }
        } catch (\Throwable) {
            // Retensi gagal tidak menggagalkan backup; dicatat pada run berikutnya.
        }
    }
}

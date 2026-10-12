<?php
namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Exception;

class BackupService
{
    protected $mysqlDumpPath = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe';
    protected $mysqlPath = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe';

    public function createBackup()
    {
        $backupId = 'optik-backup-' . date('Ymd-His');
        $tempDir = storage_path('app/temp-backup-' . $backupId);
        File::makeDirectory($tempDir, 0755, true, true);

        try {
            // 1. Database Dump
            $dbName = env('DB_DATABASE', 'optik_dwi_putra');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');
            $sqlPath = $tempDir . '/database.sql';
            
            $passFlag = $dbPass ? "-p\"{$dbPass}\"" : "";
            $cmd = "\"{$this->mysqlDumpPath}\" -u{$dbUser} {$passFlag} {$dbName} > \"{$sqlPath}\"";
            exec($cmd);

            if (!File::exists($sqlPath) || File::size($sqlPath) === 0) {
                throw new Exception("Gagal mengekstrak database. File SQL kosong.");
            }

            // 2. Zip everything (SQL + Public Storage if needed, we keep it simple to SQL for speed & safety)
            $zipPath = $tempDir . '/bundle.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                throw new Exception("Gagal membuat file ZIP.");
            }
            $zip->addFile($sqlPath, 'database.sql');
            
            // Generate Manifest
            $manifest = [
                'backup_id' => $backupId,
                'format_version' => '1.0',
                'timestamp' => now()->toIso8601String(),
                'sha256_sql' => hash_file('sha256', $sqlPath)
            ];
            $zip->addFromString('manifest.json', json_encode($manifest));
            $zip->close();

            // 3. Encrypt with AES-256-GCM
            $key = base64_decode(env('BACKUP_KEY'));
            if (strlen($key) !== 32) throw new Exception("BACKUP_KEY tidak valid (harus 32 byte).");
            
            $iv = random_bytes(12);
            $plaintext = File::get($zipPath);
            $tag = '';
            
            $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
            if ($ciphertext === false) throw new Exception("Enkripsi gagal.");

            // Output format: IV (12) + TAG (16) + CIPHERTEXT
            $finalPath = storage_path("app/backups/{$backupId}.enc");
            File::put($finalPath, $iv . $tag . $ciphertext);

            return $backupId . '.enc';
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    public function restoreBackup($filename)
    {
        $encPath = storage_path("app/backups/{$filename}");
        if (!File::exists($encPath)) throw new Exception("File backup tidak ditemukan.");

        $tempDir = storage_path('app/temp-restore-' . uniqid());
        File::makeDirectory($tempDir, 0755, true, true);

        try {
            // 1. Decrypt
            $data = File::get($encPath);
            if (strlen($data) < 28) throw new Exception("File backup korup atau terlalu kecil.");

            $iv = substr($data, 0, 12);
            $tag = substr($data, 12, 16);
            $ciphertext = substr($data, 28);
            $key = base64_decode(env('BACKUP_KEY'));

            $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
            if ($plaintext === false) throw new Exception("Gagal dekripsi. File mungkin rusak atau BACKUP_KEY salah.");

            $zipPath = $tempDir . '/bundle.zip';
            File::put($zipPath, $plaintext);

            // 2. Unzip & Verify Manifest
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) throw new Exception("File bundel (ZIP) di dalam enkripsi rusak.");
            $zip->extractTo($tempDir);
            $zip->close();

            $manifestPath = $tempDir . '/manifest.json';
            $sqlPath = $tempDir . '/database.sql';
            if (!File::exists($manifestPath) || !File::exists($sqlPath)) throw new Exception("Integritas gagal: manifest.json atau database.sql hilang.");

            $manifest = json_decode(File::get($manifestPath), true);
            $hash = hash_file('sha256', $sqlPath);
            if ($hash !== $manifest['sha256_sql']) throw new Exception("Integritas file SQL rusak (SHA-256 tidak cocok).");

            // 3. Restore Database
            $dbName = env('DB_DATABASE', 'optik_dwi_putra');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');
            
            $passFlag = $dbPass ? "-p\"{$dbPass}\"" : "";
            $cmd = ""{$this->mysqlPath}" -u{$dbUser} {$passFlag} {$dbName} < "{$sqlPath}"";
            
            exec($cmd, $output, $returnVar);
            if ($returnVar !== 0) throw new Exception("Gagal mengimpor database. Kode exit: " . $returnVar);

            return true;
        } finally {
            File::deleteDirectory($tempDir);
        }
    }
}

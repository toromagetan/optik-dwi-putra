<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupEncrypter;
use Illuminate\Console\Command;

class BackupDecrypt extends Command
{
    protected $signature = 'backup:decrypt {file : Path arsip .zip.enc} {--output= : Path hasil .zip (bawaan: tanpa .enc)}';

    protected $description = 'Dekripsi arsip backup terenkripsi menjadi .zip biasa sebagai langkah awal restore manual';

    public function handle(BackupEncrypter $encrypter): int
    {
        $file = (string) $this->argument('file');
        if (! BackupEncrypter::isEncryptedFile($file)) {
            $this->error('Berkas tidak ditemukan atau bukan arsip terenkripsi Dwi Putra.');

            return self::FAILURE;
        }

        $output = (string) ($this->option('output') ?: preg_replace('/\.enc$/', '', $file));
        $encrypter->decryptFile($file, $output);
        $this->info("Berhasil didekripsi ke: {$output}");

        return self::SUCCESS;
    }
}

<?php

namespace App\Services\Backup;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Impor dump database hasil backup ke database aktif.
 *
 * MySQL: memakai klien `mysql` dengan berkas dump sebagai stdin dan kata
 * sandi lewat env MYSQL_PWD (tidak tampil di daftar proses). SQLite:
 * berkas database hasil ekstrak menggantikan berkas aktif.
 *
 * Kelas ini sengaja terpisah agar dapat diganti (mock) pada pengujian —
 * impor sungguhan bersifat destruktif terhadap database uji.
 */
class DatabaseImporter
{
    public function import(string $extractedDir): void
    {
        $driver = (string) config('database.default');

        if ($driver === 'mysql') {
            $dump = $extractedDir.DIRECTORY_SEPARATOR.'database.sql';
            if (! is_file($dump)) {
                throw new RuntimeException('Arsip tidak berisi database.sql — restore dibatalkan.');
            }
            $this->importMysql($dump);

            return;
        }

        if ($driver === 'sqlite') {
            $source = $extractedDir.DIRECTORY_SEPARATOR.'database.sqlite';
            if (! is_file($source)) {
                throw new RuntimeException('Arsip tidak berisi database.sqlite — restore dibatalkan.');
            }
            copy($source, (string) config('database.connections.sqlite.database'));

            return;
        }

        throw new RuntimeException("Driver database '{$driver}' belum didukung untuk restore.");
    }

    private function importMysql(string $dumpPath): void
    {
        $config = config('database.connections.mysql');

        $process = new Process([
            'mysql',
            '-h', (string) ($config['host'] ?? '127.0.0.1'),
            '-P', (string) ($config['port'] ?? '3306'),
            '-u', (string) ($config['username'] ?? 'root'),
            (string) ($config['database'] ?? ''),
        ], null, ['MYSQL_PWD' => (string) ($config['password'] ?? '')], fopen($dumpPath, 'r'), 600);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Impor MySQL gagal: '.trim($process->getErrorOutput()));
        }
    }
}

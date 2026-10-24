<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupService;
use Illuminate\Console\Command;

class BackupRun extends Command
{
    protected $signature = 'backup:run {--no-upload : Lewati unggah ke Google Drive}';

    protected $description = 'Buat arsip backup (database + unggahan), simpan lokal, dan unggah ke Google Drive bila dikonfigurasi';

    public function handle(BackupService $service): int
    {
        $log = $service->run(
            trigger: 'scheduled',
            upload: ! $this->option('no-upload'),
        );

        $this->info("Backup {$log->filename}: lokal={$log->local_status}, drive={$log->drive_status}");
        if ($log->notes) {
            $this->warn($log->notes);
        }

        return $log->local_status === 'success' ? self::SUCCESS : self::FAILURE;
    }
}

<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Services\BackupService;
use Illuminate\Support\Facades\File;

class BackupManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $title = 'Keamanan & Backup';
    protected static string $view = 'filament.pages.backup-manager';

    public $backups = [];

    public function mount()
    {
        $this->loadBackups();
    }

    public function loadBackups()
    {
        $dir = storage_path('app/backups');
        if (!File::exists($dir)) return;

        $files = File::files($dir);
        $this->backups = collect($files)->map(function ($file) {
            return [
                'name' => $file->getFilename(),
                'size' => round($file->getSize() / 1024, 2) . ' KB',
                'time' => date('d M Y, H:i', $file->getMTime())
            ];
        })->sortByDesc('time')->values()->toArray();
    }

    public function createBackup(BackupService $service)
    {
        try {
            $filename = $service->createBackup();
            $this->loadBackups();
            Notification::make()->title('Backup Berhasil!')->body("File {$filename} dienkripsi dengan aman.")->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Backup Gagal!')->body($e->getMessage())->danger()->send();
        }
    }

    public function restoreBackup($filename, BackupService $service)
    {
        try {
            $service->restoreBackup($filename);
            Notification::make()->title('Restore Berhasil!')->body("Database dipulihkan ke titik {$filename}.")->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Restore Gagal!')->body($e->getMessage())->danger()->send();
        }
    }
}

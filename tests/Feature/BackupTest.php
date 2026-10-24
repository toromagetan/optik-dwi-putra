<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BackupLog;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\Backup\BackupEncrypter;
use App\Services\Backup\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    /** @var array<int, string> berkas yang dibuat test di direktori backup */
    private array $createdFiles = [];

    private ?string $saBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Jaga-jaga: pulihkan kredensial SA asli setelah test (bila ada).
        $saPath = app(BackupService::class)->serviceAccountPath();
        if (is_file($saPath)) {
            $this->saBackup = (string) file_get_contents($saPath);
        }
    }

    protected function tearDown(): void
    {
        // Bersihkan artefak berkas yang dibuat test ini.
        $directory = (string) config('backup.directory');
        if (is_dir($directory)) {
            foreach (File::files($directory) as $file) {
                if (str_starts_with($file->getFilename(), 'dwiputra-backup-')) {
                    File::delete($file->getPathname());
                }
            }
        }
        File::delete(storage_path('app/public/backup-test-marker.txt'));

        $saPath = app(BackupService::class)->serviceAccountPath();
        if ($this->saBackup !== null) {
            File::ensureDirectoryExists(dirname($saPath));
            File::put($saPath, $this->saBackup);
        } else {
            File::delete($saPath);
        }

        parent::tearDown();
    }

    /** Buat JSON service account uji dengan kunci RSA asli (agar JWT valid). */
    private function fakeServiceAccountJson(): string
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $pem = '';
        openssl_pkey_export($key, $pem);

        return (string) json_encode([
            'type' => 'service_account',
            'project_id' => 'uji-backup',
            'private_key' => $pem,
            'client_email' => 'backup-uji@uji-backup.iam.gserviceaccount.com',
        ]);
    }

    private function configureDrive(): void
    {
        StoreSetting::set('backup_enabled', '1');
        StoreSetting::set('backup_drive_folder_id', 'folder-uji-123');
        $saPath = app(BackupService::class)->serviceAccountPath();
        File::ensureDirectoryExists(dirname($saPath));
        File::put($saPath, $this->fakeServiceAccountJson());
    }

    public function test_guest_and_non_admin_cannot_access_backup_panel(): void
    {
        $this->get(route('admin.backups.index'))->assertRedirect(route('login'));
        $this->post(route('admin.backups.run'))->assertRedirect(route('login'));

        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)->get(route('admin.backups.index'))->assertForbidden();
    }

    public function test_admin_can_open_backup_page_showing_unconfigured_drive(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Backup Sekarang')
            ->assertSee('NONAKTIF');
    }

    public function test_manual_backup_creates_encrypted_archive_with_database_and_public_files(): void
    {
        File::ensureDirectoryExists(storage_path('app/public'));
        File::put(storage_path('app/public/backup-test-marker.txt'), 'penanda-backup-uji');

        $this->actingAs($this->admin)
            ->post(route('admin.backups.run'))
            ->assertSessionHas('success');

        $log = BackupLog::latest()->first();
        $this->assertNotNull($log);
        $this->assertSame('success', $log->local_status);
        $this->assertSame('skipped', $log->drive_status); // Drive belum dikonfigurasi
        $this->assertTrue($log->encrypted);
        $this->assertSame('manual', $log->trigger);

        $path = ((string) config('backup.directory')).DIRECTORY_SEPARATOR.$log->filename;
        $this->assertFileExists($path);
        $this->assertTrue(BackupEncrypter::isEncryptedFile($path));

        // Dekripsi dan pastikan isi arsip lengkap.
        $zipPath = $path.'.zip';
        app(BackupEncrypter::class)->decryptFile($path, $zipPath);
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($zipPath) === true);
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();
        File::delete($zipPath);

        $this->assertContains('database.sql', $names);
        $this->assertContains('public/backup-test-marker.txt', $names);
        $this->assertContains('MANIFEST.txt', $names);

        $this->assertTrue(AuditLog::where('action', 'backup.created')->exists());
    }

    public function test_backup_uploads_to_drive_when_configured(): void
    {
        $this->configureDrive();

        Http::fake(function ($request) {
            $url = $request->url();
            if (str_contains($url, 'oauth2.googleapis.com')) {
                return Http::response(['access_token' => 'token-uji'], 200);
            }
            if (str_contains($url, 'upload/drive')) {
                return Http::response(['id' => 'drive-file-abc'], 200);
            }

            return Http::response(['files' => []], 200);
        });

        $log = app(BackupService::class)->run('manual', $this->admin);

        $this->assertSame('success', $log->local_status);
        $this->assertSame('success', $log->drive_status);
        $this->assertSame('drive-file-abc', $log->drive_file_id);
        // Permintaan unggah membawa metadata folder tujuan + token OAuth.
        Http::assertSent(fn ($request) => str_contains($request->url(), 'upload/drive')
            && str_contains((string) $request->body(), 'folder-uji-123'));
        Http::assertSent(fn ($request) => str_contains($request->url(), 'oauth2.googleapis.com'));
    }

    public function test_drive_failure_is_soft_and_local_backup_survives(): void
    {
        $this->configureDrive();

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'token-uji'], 200),
            '*' => Http::response(['error' => 'boom'], 500),
        ]);

        $log = app(BackupService::class)->run('manual', $this->admin);

        $this->assertSame('success', $log->local_status);
        $this->assertSame('failed', $log->drive_status);
        $this->assertFileExists(((string) config('backup.directory')).DIRECTORY_SEPARATOR.$log->filename);
    }

    public function test_settings_update_stores_folder_saves_sa_and_audits(): void
    {
        $file = UploadedFile::fake()->createWithContent('sa.json', $this->fakeServiceAccountJson());

        $this->actingAs($this->admin)
            ->put(route('admin.backups.settings'), [
                'backup_enabled' => '1',
                'backup_drive_folder_id' => 'folder-baru-99',
                'backup_retention_local' => 7,
                'backup_retention_drive' => 12,
                'sa_file' => $file,
            ])
            ->assertSessionHas('success');

        $this->assertSame('folder-baru-99', StoreSetting::get('backup_drive_folder_id'));
        $this->assertSame('backup-uji@uji-backup.iam.gserviceaccount.com', StoreSetting::get('backup_sa_email'));
        $this->assertFileExists(app(BackupService::class)->serviceAccountPath());
        $this->assertTrue(AuditLog::where('action', 'backup.settings_updated')->exists());
        $this->assertTrue(app(BackupService::class)->driveConfigured());
    }

    public function test_settings_reject_invalid_service_account_file(): void
    {
        $file = UploadedFile::fake()->createWithContent('sa.json', '{"bukan":"service-account"}');

        $this->actingAs($this->admin)
            ->put(route('admin.backups.settings'), [
                'backup_drive_folder_id' => 'folder-x',
                'backup_retention_local' => 5,
                'backup_retention_drive' => 10,
                'sa_file' => $file,
            ])
            ->assertSessionHasErrors(['sa_file']);

        $this->assertFileDoesNotExist(app(BackupService::class)->serviceAccountPath());
    }

    public function test_artisan_backup_run_command_and_schedule(): void
    {
        $this->artisan('backup:run', ['--no-upload' => true])->assertSuccessful();

        $log = BackupLog::latest()->first();
        $this->assertSame('scheduled', $log->trigger);
        $this->assertSame('success', $log->local_status);
        $this->assertSame('skipped', $log->drive_status);

        $this->artisan('schedule:list')->assertSuccessful()->expectsOutputToContain('backup:run');
    }

    public function test_download_returns_local_archive_and_missing_file_gives_error(): void
    {
        $log = app(BackupService::class)->run('manual', $this->admin);

        $this->actingAs($this->admin)
            ->get(route('admin.backups.download', $log))
            ->assertOk();

        $this->assertTrue(AuditLog::where('action', 'backup.downloaded')->exists());

        // Hapus berkasnya; unduhan berikutnya harus gagal dengan pesan jelas.
        File::delete(((string) config('backup.directory')).DIRECTORY_SEPARATOR.$log->filename);
        $this->actingAs($this->admin)
            ->get(route('admin.backups.download', $log))
            ->assertSessionHas('error');
    }

    public function test_local_retention_keeps_only_configured_number(): void
    {
        StoreSetting::set('backup_retention_local', '3');
        $directory = (string) config('backup.directory');
        File::ensureDirectoryExists($directory);

        // 5 arsip lama (mtime mundur)
        for ($i = 1; $i <= 5; $i++) {
            $p = $directory."/dwiputra-backup-2020010{$i}-000000.zip.enc";
            File::put($p, 'lama-'.$i);
            touch($p, strtotime("2020-01-0{$i}"));
            $this->createdFiles[] = $p;
        }

        app(BackupService::class)->run('scheduled');

        $remaining = collect(File::files($directory))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'dwiputra-backup-'))
            ->count();
        $this->assertSame(3, $remaining);
    }
}

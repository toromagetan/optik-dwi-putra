<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BackupLog;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\Backup\BackupRestoreService;
use App\Services\Backup\BackupService;
use App\Services\Backup\DatabaseImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private MockInterface $importer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Impor database sungguhan bersifat destruktif untuk DB uji —
        // ganti dengan mock; pemulihan BERKAS tetap diuji sungguhan.
        $this->importer = Mockery::mock(DatabaseImporter::class);
        $this->app->instance(DatabaseImporter::class, $this->importer);
    }

    protected function tearDown(): void
    {
        $directory = (string) config('backup.directory');
        if (is_dir($directory)) {
            foreach (File::files($directory) as $file) {
                if (str_starts_with($file->getFilename(), 'dwiputra-backup-')) {
                    File::delete($file->getPathname());
                }
            }
        }
        File::delete(storage_path('app/public/restore-marker.txt'));
        File::delete(storage_path('app/public/restore-extra.txt'));
        Mockery::close();
        parent::tearDown();
    }

    /** Buat backup nyata berisi penanda versi tertentu. */
    private function makeBackup(string $markerContent): BackupLog
    {
        File::ensureDirectoryExists(storage_path('app/public'));
        File::put(storage_path('app/public/restore-marker.txt'), $markerContent);

        return app(BackupService::class)->run('manual', $this->admin);
    }

    private function setRestorePassword(string $plain = 'password-owner-123'): void
    {
        app(BackupRestoreService::class)->setPassword($plain);
    }

    public function test_set_restore_password_first_time_requires_admin_password_confirmation(): void
    {
        $this->actingAs($this->admin)->put(route('admin.backups.restore-password'), [
            'current_admin_password' => 'bukan-password-admin',
            'new_restore_password' => 'password-owner-123',
            'new_restore_password_confirmation' => 'password-owner-123',
        ])->assertSessionHasErrors(['current_admin_password']);

        $this->assertFalse(app(BackupRestoreService::class)->passwordIsSet());

        $this->actingAs($this->admin)->put(route('admin.backups.restore-password'), [
            'current_admin_password' => 'password', // kata sandi bawaan factory
            'new_restore_password' => 'password-owner-123',
            'new_restore_password_confirmation' => 'password-owner-123',
        ])->assertSessionHas('success');

        $stored = StoreSetting::get(BackupRestoreService::SETTING_KEY);
        $this->assertNotSame('password-owner-123', $stored);
        $this->assertTrue(Hash::check('password-owner-123', (string) $stored));
        $this->assertTrue(AuditLog::where('action', 'backup.restore_password_updated')->exists());
    }

    public function test_change_restore_password_requires_current_restore_password(): void
    {
        $this->setRestorePassword('password-lama-123');

        $this->actingAs($this->admin)->put(route('admin.backups.restore-password'), [
            'current_restore_password' => 'salah-total',
            'new_restore_password' => 'password-baru-456',
            'new_restore_password_confirmation' => 'password-baru-456',
        ])->assertSessionHasErrors(['current_restore_password']);

        $this->assertTrue(app(BackupRestoreService::class)->verifyPassword('password-lama-123'));

        $this->actingAs($this->admin)->put(route('admin.backups.restore-password'), [
            'current_restore_password' => 'password-lama-123',
            'new_restore_password' => 'password-baru-456',
            'new_restore_password_confirmation' => 'password-baru-456',
        ])->assertSessionHas('success');

        $this->assertTrue(app(BackupRestoreService::class)->verifyPassword('password-baru-456'));
    }

    public function test_restore_form_is_admin_only_and_warns_when_password_unset(): void
    {
        $log = $this->makeBackup('versi-1');

        $this->get(route('admin.backups.restore.form', $log))->assertRedirect(route('login'));

        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)->get(route('admin.backups.restore.form', $log))->assertForbidden();

        $this->actingAs($this->admin)->get(route('admin.backups.restore.form', $log))
            ->assertOk()
            ->assertSee('Password restore belum diatur');

        // Bila berkas lokal hilang dan tidak ada salinan Drive, label sumber jujur.
        File::delete(((string) config('backup.directory')).DIRECTORY_SEPARATOR.$log->filename);
        $this->actingAs($this->admin)->get(route('admin.backups.restore.form', $log))
            ->assertOk()
            ->assertSee('tidak dapat di-restore');
    }

    public function test_restore_blocked_when_password_not_set(): void
    {
        $log = $this->makeBackup('versi-1');
        File::put(storage_path('app/public/restore-marker.txt'), 'versi-2');

        $this->importer->shouldNotReceive('import');

        $this->actingAs($this->admin)->post(route('admin.backups.restore', $log), [
            'restore_password' => 'apa-saja-12345',
        ])->assertSessionHas('error');

        $this->assertSame('versi-2', File::get(storage_path('app/public/restore-marker.txt')));
    }

    public function test_restore_rejects_wrong_password_and_changes_nothing(): void
    {
        $this->setRestorePassword();
        $log = $this->makeBackup('versi-1');
        File::put(storage_path('app/public/restore-marker.txt'), 'versi-2');

        $this->importer->shouldNotReceive('import');

        $this->actingAs($this->admin)->post(route('admin.backups.restore', $log), [
            'restore_password' => 'password-salah-999',
        ])->assertSessionHas('error');

        $this->assertSame('versi-2', File::get(storage_path('app/public/restore-marker.txt')));
        $this->assertFalse(BackupLog::where('trigger', 'pre-restore')->exists());
    }

    public function test_restore_with_correct_password_restores_files_creates_safety_backup_and_audits(): void
    {
        $this->setRestorePassword();
        $log = $this->makeBackup('versi-1');

        // Keadaan "sekarang" berubah setelah backup dibuat.
        File::put(storage_path('app/public/restore-marker.txt'), 'versi-2');
        File::put(storage_path('app/public/restore-extra.txt'), 'file-baru-setelah-backup');

        $this->importer->shouldReceive('import')->once();

        $this->actingAs($this->admin)->post(route('admin.backups.restore', $log), [
            'restore_password' => 'password-owner-123',
        ])->assertRedirect(route('admin.backups.index'))->assertSessionHas('success');

        // Berkas kembali ke isi arsip; berkas baru sesudah backup ikut hilang.
        $this->assertSame('versi-1', File::get(storage_path('app/public/restore-marker.txt')));
        $this->assertFileDoesNotExist(storage_path('app/public/restore-extra.txt'));

        // .gitignore repo di storage/app/public dipertahankan restore.
        $this->assertFileExists(storage_path('app/public/.gitignore'));

        // Backup keselamatan pra-restore dibuat lebih dulu.
        $safety = BackupLog::where('trigger', 'pre-restore')->latest()->first();
        $this->assertNotNull($safety);
        $this->assertSame('success', $safety->local_status);
        $this->assertFileExists(((string) config('backup.directory')).DIRECTORY_SEPARATOR.$safety->filename);

        $this->assertTrue(AuditLog::where('action', 'backup.restored')->exists());
    }

    public function test_restore_attempts_are_rate_limited(): void
    {
        $this->setRestorePassword();
        $log = $this->makeBackup('versi-1');
        $this->importer->shouldNotReceive('import');

        for ($i = 1; $i <= 5; $i++) {
            $this->actingAs($this->admin)->post(route('admin.backups.restore', $log), [
                'restore_password' => 'salah-'.$i.'-xxxxx',
            ])->assertSessionHas('error');
        }

        // Percobaan ke-6 (bahkan dengan password benar) ditolak pembatas.
        $response = $this->actingAs($this->admin)->post(route('admin.backups.restore', $log), [
            'restore_password' => 'password-owner-123',
        ])->assertSessionHas('error');
        $this->assertStringContainsString('Terlalu banyak percobaan', (string) session('error'));
    }
}

<?php

namespace Tests\Unit;

use App\Services\Backup\BackupEncrypter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BackupEncrypterTest extends TestCase
{
    public function test_encrypt_decrypt_round_trip(): void
    {
        $dir = sys_get_temp_dir().'/dwiputra-enc-'.uniqid();
        mkdir($dir);
        $source = $dir.'/arsip.zip';
        $encrypted = $dir.'/arsip.zip.enc';
        $restored = $dir.'/pulih.zip';

        file_put_contents($source, 'ISI-RAHASIA-'.str_repeat('backup!', 500));

        $encrypter = new BackupEncrypter('kunci-uji-123');
        $encrypter->encryptFile($source, $encrypted);

        $this->assertTrue(BackupEncrypter::isEncryptedFile($encrypted));
        $this->assertStringNotContainsString('ISI-RAHASIA', (string) file_get_contents($encrypted));

        $encrypter->decryptFile($encrypted, $restored);
        $this->assertSame(file_get_contents($source), file_get_contents($restored));

        // Kunci berbeda harus gagal mendekripsi.
        $wrong = new BackupEncrypter('kunci-lain');
        $this->expectException(RuntimeException::class);
        $wrong->decryptFile($encrypted, $dir.'/salah.zip');
    }

    public function test_decrypt_rejects_non_encrypted_file(): void
    {
        $dir = sys_get_temp_dir().'/dwiputra-enc-'.uniqid();
        mkdir($dir);
        $plain = $dir.'/biasa.zip';
        file_put_contents($plain, 'bukan arsip terenkripsi');

        $this->assertFalse(BackupEncrypter::isEncryptedFile($plain));

        $this->expectException(RuntimeException::class);
        (new BackupEncrypter('apa-saja'))->decryptFile($plain, $dir.'/out.zip');
    }
}

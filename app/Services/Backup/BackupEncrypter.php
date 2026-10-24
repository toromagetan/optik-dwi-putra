<?php

namespace App\Services\Backup;

use RuntimeException;

/**
 * Enkripsi arsip backup AES-256-CBC (pola yang sama dengan Artesis).
 *
 * Format berkas: header "DWIPUTRA-ENC-v1" + IV (16 byte) + ciphertext.
 * Kunci efektif = SHA-256 dari kunci yang dikonfigurasi (config
 * backup.encryption_key, fallback APP_KEY). Dekripsi disediakan agar
 * arsip dapat dipulihkan menjadi .zip biasa sebelum restore manual.
 */
class BackupEncrypter
{
    public const HEADER = 'DWIPUTRA-ENC-v1';

    private string $key;

    public function __construct(?string $key = null)
    {
        $raw = $key ?: (string) config('backup.encryption_key') ?: (string) config('app.key');
        if (str_starts_with($raw, 'base64:')) {
            $decoded = base64_decode(substr($raw, 7), true);
            if ($decoded !== false) {
                $raw = $decoded;
            }
        }
        $this->key = hash('sha256', (string) $raw, true);
    }

    /** Enkripsi $source menjadi $destination (menimpa bila ada). */
    public function encryptFile(string $source, string $destination): void
    {
        $plain = file_get_contents($source);
        if ($plain === false) {
            throw new RuntimeException("Tidak dapat membaca berkas sumber enkripsi: {$source}");
        }

        $iv = random_bytes(16);
        $cipher = openssl_encrypt($plain, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            throw new RuntimeException('Enkripsi arsip backup gagal (openssl).');
        }

        file_put_contents($destination, self::HEADER.$iv.$cipher);
    }

    /** Dekripsi $source menjadi $destination; melempar bila format/kunci salah. */
    public function decryptFile(string $source, string $destination): void
    {
        $data = file_get_contents($source);
        if ($data === false || ! str_starts_with($data, self::HEADER)) {
            throw new RuntimeException('Berkas bukan arsip terenkripsi Dwi Putra (header tidak dikenal).');
        }

        $iv = substr($data, strlen(self::HEADER), 16);
        $cipher = substr($data, strlen(self::HEADER) + 16);
        $plain = openssl_decrypt($cipher, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $iv);
        if ($plain === false) {
            throw new RuntimeException('Dekripsi gagal — kunci enkripsi tidak cocok.');
        }

        file_put_contents($destination, $plain);
    }

    public static function isEncryptedFile(string $path): bool
    {
        if (! is_file($path)) {
            return false;
        }
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }
        $head = fread($handle, strlen(self::HEADER));
        fclose($handle);

        return $head === self::HEADER;
    }
}

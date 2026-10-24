<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien Google Drive untuk Service Account (tanpa dependensi tambahan).
 *
 * Alur OAuth2 JWT Bearer: JWT RS256 ditandatangani dengan kunci privat
 * service account, ditukar menjadi access token, lalu dipakai untuk
 * Drive API v3. Folder tujuan HARUS berada di Shared Drive — service
 * account tidak memiliki kuota penyimpanan My Drive pribadi.
 */
class GoogleDriveClient
{
    private ?string $accessToken = null;

    /** @param array{client_email: string, private_key: string} $serviceAccount */
    public function __construct(private readonly array $serviceAccount) {}

    /** Muat kredensial dari berkas JSON service account; validasi bentuknya. */
    public static function fromFile(string $path): self
    {
        if (! is_file($path)) {
            throw new RuntimeException('Berkas kredensial service account tidak ditemukan.');
        }
        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data)
            || ($data['type'] ?? null) !== 'service_account'
            || empty($data['client_email'])
            || empty($data['private_key'])) {
            throw new RuntimeException('Berkas kredensial bukan JSON service account Google yang valid.');
        }

        return new self($data);
    }

    public function serviceAccountEmail(): string
    {
        return (string) ($this->serviceAccount['client_email'] ?? '');
    }

    /** Ambil access token OAuth2 dari JWT service account. */
    public function accessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $now = time();
        $jwt = $this->buildJwt($now, $now + 3600);

        $response = Http::asForm()->timeout(30)->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($response->failed() || ! $response->json('access_token')) {
            throw new RuntimeException('Gagal memperoleh access token Google: '.$response->status().' '.$response->body());
        }

        return $this->accessToken = (string) $response->json('access_token');
    }

    private function buildJwt(int $iat, int $exp): string
    {
        $encode = fn (array $data) => rtrim(strtr(base64_encode((string) json_encode($data)), '+/', '-_'), '=');

        $header = $encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $claims = $encode([
            'iss' => $this->serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $iat,
            'exp' => $exp,
        ]);

        $key = openssl_pkey_get_private((string) $this->serviceAccount['private_key']);
        if ($key === false) {
            throw new RuntimeException('Kunci privat service account tidak dapat dibaca.');
        }
        $signature = '';
        if (! openssl_sign("{$header}.{$claims}", $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Penandatanganan JWT service account gagal.');
        }

        return "{$header}.{$claims}.".rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }

    /** Unggah berkas ke folder Drive; mengembalikan ID berkas di Drive. */
    public function upload(string $path, string $name, string $folderId): string
    {
        $boundary = 'dwiputra_'.bin2hex(random_bytes(8));
        $metadata = json_encode(['name' => $name, 'parents' => [$folderId]]);
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Tidak dapat membaca berkas backup untuk diunggah: {$path}");
        }

        $body = "--{$boundary}\r\n"
            ."Content-Type: application/json; charset=UTF-8\r\n\r\n"
            .$metadata."\r\n"
            ."--{$boundary}\r\n"
            ."Content-Type: application/octet-stream\r\n\r\n"
            .$contents."\r\n"
            ."--{$boundary}--";

        $response = Http::withToken($this->accessToken())
            ->withHeaders(['Content-Type' => "multipart/related; boundary={$boundary}"])
            ->timeout(300)
            ->send('POST', 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&supportsAllDrives=true', [
                'body' => $body,
            ]);

        if ($response->failed() || ! $response->json('id')) {
            throw new RuntimeException('Unggah ke Google Drive gagal: '.$response->status().' '.$response->body());
        }

        return (string) $response->json('id');
    }

    /**
     * Daftar berkas di folder, terbaru dulu.
     *
     * @return array<int, array{id: string, name: string, createdTime: string}>
     */
    public function listFiles(string $folderId): array
    {
        $response = Http::withToken($this->accessToken())
            ->timeout(60)
            ->get('https://www.googleapis.com/drive/v3/files', [
                'q' => "'{$folderId}' in parents and trashed = false",
                'orderBy' => 'createdTime desc',
                'fields' => 'files(id,name,createdTime)',
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal membaca daftar berkas Drive: '.$response->status().' '.$response->body());
        }

        return $response->json('files') ?? [];
    }

    public function delete(string $fileId): void
    {
        $response = Http::withToken($this->accessToken())
            ->timeout(60)
            ->delete("https://www.googleapis.com/drive/v3/files/{$fileId}", [
                'supportsAllDrives' => 'true',
            ]);

        if ($response->failed() && $response->status() !== 404) {
            throw new RuntimeException('Gagal menghapus berkas Drive lama: '.$response->status().' '.$response->body());
        }
    }
}

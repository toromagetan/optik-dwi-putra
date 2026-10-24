<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Google Drive (PRD §11.9)
    |--------------------------------------------------------------------------
    | Pengaturan yang dapat diubah admin (folder ID, retensi, aktif/nonaktif,
    | email service account) disimpan sebagai StoreSetting dengan awalan
    | "backup_". Di sini hanya nilai bawaan + rahasia berbasis env:
    | - BACKUP_ENCRYPTION_KEY : kunci enkripsi arsip (kosong = turunan APP_KEY)
    | - BACKUP_SCHEDULE_TIME  : jam backup otomatis harian (Asia/Jakarta)
    | File kredensial service account disimpan di storage/app/private/
    | gdrive-sa.json (di luar public, tidak pernah ditampilkan kembali).
    */

    'directory' => storage_path('app/backups'),

    'service_account_path' => storage_path('app/private/gdrive-sa.json'),

    'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),

    'schedule_time' => env('BACKUP_SCHEDULE_TIME', '02:30'),

    'defaults' => [
        'enabled' => false,
        'folder_id' => '',
        'retention_local' => 5,
        'retention_drive' => 10,
    ],
];

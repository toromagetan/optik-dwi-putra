<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup otomatis harian (PRD §11.9) — membutuhkan cron server:
// * * * * * cd /var/www/optik-dwi-putra && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('backup:run')
    ->dailyAt((string) config('backup.schedule_time', '02:30'))
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();

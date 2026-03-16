<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule license sync every 6 hours
Schedule::command('license:sync')->everySixHours();

// Auto backup database (jadwal dari Settings, hanya jika checkbox aktif)
$schedule1Enabled = \App\Models\Setting::get('backup_schedule_1_enabled', '1');
$schedule2Enabled = \App\Models\Setting::get('backup_schedule_2_enabled', '1');

if ($schedule1Enabled == '1') {
    $backupTime1 = \App\Models\Setting::get('backup_schedule_1', '12:00');
    Schedule::command('db:backup')
        ->dailyAt($backupTime1)
        ->appendOutputTo(storage_path('logs/backup.log'));
}

if ($schedule2Enabled == '1') {
    $backupTime2 = \App\Models\Setting::get('backup_schedule_2', '23:55');
    Schedule::command('db:backup')
        ->dailyAt($backupTime2)
        ->appendOutputTo(storage_path('logs/backup.log'));
}

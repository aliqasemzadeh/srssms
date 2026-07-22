<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Backup Schedule
|--------------------------------------------------------------------------
| Every night old backups are cleaned up first, then a fresh backup of the
| database and the uploaded files is created on the local disk. Output is
| appended to storage/logs/backup.log for troubleshooting.
*/
Schedule::command('backup:clean', ['--disable-notifications' => true])
    ->dailyAt('01:00')
    ->appendOutputTo(storage_path('logs/backup.log'));

Schedule::command('backup:run', ['--disable-notifications' => true])
    ->dailyAt('01:30')
    ->appendOutputTo(storage_path('logs/backup.log'));

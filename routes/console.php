<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule meeting reminder notifications
Schedule::command('meetings:send-reminders')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Schedule trial subscription expiration check
Schedule::command('subscriptions:expire-trials')
    ->daily()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

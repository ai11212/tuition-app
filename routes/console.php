<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Payment Reminders Module - Daily calculation at 8:00 AM
if (config('reminders.enabled')) {
    Schedule::command('reminders:update')
        ->dailyAt('08:00')
        ->timezone(config('reminders.schedule.timezone', 'UTC'));
}

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run all active warmup plans once per day at 08:00 server time
Schedule::command('warmup:run')->dailyAt('08:00')->withoutOverlapping();

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run every 15 minutes so expired payment deadlines are caught promptly
Schedule::command('applications:expire-deadlines')->everyFifteenMinutes();

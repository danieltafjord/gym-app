<?php

use App\Jobs\ExpireMemberships;
use App\Jobs\SendExpiryReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ExpireMemberships)->dailyAt('02:00');
Schedule::job(new SendExpiryReminders)->dailyAt('08:00');

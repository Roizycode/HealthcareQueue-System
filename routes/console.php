<?php

use App\Jobs\CheckEscalationsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Check for queue escalations every 5 minutes
Schedule::job(new CheckEscalationsJob())->everyFiveMinutes();

// Clean up old completed queues (older than 30 days)
Schedule::command('queue:cleanup')->dailyAt('02:00');

<?php

use App\Jobs\SyncHubSpotContacts;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Jobs
|--------------------------------------------------------------------------
*/

// Sync HubSpot contacts every 15 minutes
Schedule::job(new SyncHubSpotContacts())
    ->everyFifteenMinutes()
    ->withoutOverlapping();

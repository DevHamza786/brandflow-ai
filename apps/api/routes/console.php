<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| BrandFlow / PBOS scheduling spine — batch orchestration + stale recovery.
| Docker: `scheduler` service runs `php artisan schedule:work` (see docker-compose).
*/
Schedule::command('schedule:orchestrate')
    ->everyMinute()
    ->withoutOverlapping(55);

Schedule::command('schedule:recover-stale-queued')
    ->hourly()
    ->withoutOverlapping(45);

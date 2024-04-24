<?php

use App\Jobs\FetchAllianceStandings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule commands
Schedule::job(new FetchAllianceStandings())->downtime()->onOneServer();

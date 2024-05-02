<?php

use App\Jobs\FetchAllianceStandings;
use App\Jobs\FetchFleetMembers;
use App\Jobs\SendPendingFleetInvites;
use App\Models\Fleet;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule commands
Schedule::job(new FetchAllianceStandings())->downtime()->onOneServer();

// Fleet jobs
$fleetExistsClosure = fn () => Fleet::whereTracked()->exists();
Schedule::job(FetchFleetMembers::class)->everyTenSeconds()->when($fleetExistsClosure); // Update fleet members
Schedule::job(SendPendingFleetInvites::class)->everyFiveSeconds()->when($fleetExistsClosure); // Send queued fleet invites

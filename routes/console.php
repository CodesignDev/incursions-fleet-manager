<?php

use App\Jobs\FetchAllianceStandings;
use App\Jobs\FetchFleetInformationForAllFleets;
use App\Jobs\FetchFleetMembersForAllFleets;
use App\Jobs\SendAllPendingFleetInvites;
use App\Models\Fleet;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule commands
Schedule::job(FetchAllianceStandings::class)->downtime()->onOneServer();

// Tracked fleet jobs
Schedule::when(fn () => Fleet::whereTracked()->exists())->group(function () {
    Schedule::job(FetchFleetInformationForAllFleets::class)->everyMinute(); // Update fleet information
    Schedule::job(FetchFleetMembersForAllFleets::class)->everyTenSeconds(); // Update fleet members
    Schedule::job(SendAllPendingFleetInvites::class)->everyFiveSeconds(); // Send queued fleet invites
});

// Untracked fleet jobs
Schedule::job(new FetchFleetInformationForAllFleets(includeUntracked: true))->everyFifteenMinutes()->when(fn () => Fleet::exists());

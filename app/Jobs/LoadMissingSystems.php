<?php

namespace App\Jobs;

use App\Models\FleetMember;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

class LoadMissingSystems implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch the list of solar systems that the system does not know about
        $missingSystems = FleetMember::query()
            ->where(function (Builder $builder) {
                $builder
                    ->whereNotNull('location_id')
                    ->where('location_id', '>', 0);
            })
            ->whereDoesntHave('location')
            ->distinct()
            ->pluck('location_id');

        // Dispatch the jobs to fetch the system data from the SDE / ESI
        Bus::batch(
            $missingSystems->mapInto(FetchSolarSystemInformation::class)
        )->dispatch();
    }
}

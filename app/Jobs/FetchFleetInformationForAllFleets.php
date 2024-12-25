<?php

namespace App\Jobs;

use App\Models\Fleet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchFleetInformationForAllFleets implements ShouldQueue
{
    use Queueable;

    /**
     * Whether to include untracked fleets in the query.
     */
    protected bool $includeUntracked;

    /**
     * Create a new job instance.
     */
    public function __construct(bool $includeUntracked = false)
    {
        $this->includeUntracked = $includeUntracked;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the list of active fleets
        $fleets = Fleet::query()
            ->unless($this->includeUntracked)
            ->whereTracked()
            ->with('boss')
            ->get();

        // Queue up individual jobs to fetch the info for each fleet
        $fleets->mapInto(FetchFleetInformation::class)
            ->each(fn ($job) => dispatch($job));
    }
}

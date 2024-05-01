<?php

namespace App\Jobs;

use App\Models\Fleet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StartFleetTracking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The fleet id.
     */
    protected string $fleet;

    /**
     * Create a new job instance.
     */
    public function __construct(Fleet|string $fleet)
    {
        $this->fleet = $fleet instanceof Fleet ? $fleet->id : $fleet;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Start the fleet tracking daemon.
        // This daemon is in charge of dispatching the relevant jobs for the fleet it looks after.
        // TODO: Create and dispatch process using artisan
    }
}

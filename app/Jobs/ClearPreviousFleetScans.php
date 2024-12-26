<?php

namespace App\Jobs;

use App\Models\FleetScan;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;

class ClearPreviousFleetScans implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The user whose fleet scans are to be cleared.
     */
    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the list of characters that the current user owns
        $characters = $this->user->characters()->pluck('id');

        // Delete all the existing fleet scans that belong to this user's characters
        FleetScan::whereIn('character_id', $characters)->delete();
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new SkipIfBatchCancelled];
    }
}

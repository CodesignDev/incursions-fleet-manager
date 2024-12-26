<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;

class ScanForUserOwnedFleets implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The user who owns the characters that need to be checked.
     */
    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Exit if the batch has been cancelled
        if ($this->batch()?->cancelled()) {
            return;
        }

        // Get the list of valid characters from this user
        $characters = $this->user->characters()
            ->whereWhitelisted()
            ->get();

        // For each character, queue up the character check job that queries for the fleet info
        $this->batch()?->add(
            $characters->mapInto(ScanForCharacterOwnedFleet::class)
        );
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new SkipIfBatchCancelled];
    }
}

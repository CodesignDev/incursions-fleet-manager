<?php /** @noinspection PhpInconsistentReturnPointsInspection */

namespace App\Jobs;

use App\Enums\FleetInviteState;
use App\Models\Fleet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAllPendingFleetInvites implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the list of active fleets
        $fleets = Fleet::query()
            ->whereTracked()
            ->whereHas('invites', fn ($builder) => $builder->whereState(FleetInviteState::PENDING))
            ->get();

        // Queue up the jobs for each fleet
        $fleets->mapInto(SendPendingFleetInvites::class)
            ->each(fn ($job) => dispatch($job));
    }
}

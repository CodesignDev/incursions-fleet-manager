<?php /** @noinspection PhpInconsistentReturnPointsInspection */

namespace App\Jobs;

use App\Enums\FleetInviteState;
use App\Models\Fleet;
use App\Models\FleetInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPendingFleetInvites implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the list of active fleets
        $fleets = Fleet::query()
            ->with([
                'boss',
                'members',
                'invites' => fn ($builder) => $builder->where('state', FleetInviteState::PENDING),
            ])
            ->whereTracked()
            ->get();

        // Loop through each fleet, fetching the list of members for each
        $fleets->each(function (Fleet $fleet) {

            // If there is no fleet boss, attempt to locate the fleet boss
            if (is_null($fleet->boss)) {
                LocateFleetBoss::dispatch($fleet);
                return;
            }

            // Get the list of invites to send
            $invitesToSend = $fleet->invites;

            // If there are no invites, skip
            if ($invitesToSend->isEmpty()) {
                return;
            }

            // Otherwise, filter any fleet members who are already in fleet
            $invitesToSend = $invitesToSend->whereNotIn('character_id', $fleet->members->pluck('character_id'));

            // Loop through the list of invites and send them out using another background job
            $invitesToSend->each(fn(FleetInvite $invite) => SendFleetInvite::dispatch($invite, $fleet));
        });
    }
}

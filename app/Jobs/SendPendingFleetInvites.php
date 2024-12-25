<?php /** @noinspection PhpInconsistentReturnPointsInspection */

namespace App\Jobs;

use App\Enums\FleetInviteState;
use App\Models\FleetInvite;
use Illuminate\Foundation\Queue\Queueable;

class SendPendingFleetInvites extends EsiFleetJob
{
    use Queueable;

    /**
     * The list of relations to be included with the fleet.
     *
     * @var string[]
     */
    protected array $includedRelations = [];

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load the relevant relations for the fleet
        $fleet = $this->fleet->load([
            'boss',
            'members',
            'invites' => fn ($builder) => $builder->where('state', FleetInviteState::PENDING),
        ]);

        // If there is no fleet boss, attempt to locate the fleet boss
        if (blank($fleet->boss)) {
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
        $invitesToSend->each(fn (FleetInvite $invite) => SendFleetInvite::dispatch($invite, $fleet));
    }
}

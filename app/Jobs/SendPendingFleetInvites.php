<?php /** @noinspection PhpInconsistentReturnPointsInspection */

namespace App\Jobs;

use App\Enums\FleetInviteState;
use App\Facades\Esi;
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
                'invites' => fn (Builder $builder) => $builder->where('state', FleetInviteState::PENDING),
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

            // Loop through the list of invites
            $invitesToSend->each(function (FleetInvite $invite) use ($fleet) {

                // Send the invite to the character
                $response = Esi::withCharacter($fleet->boss)
                    ->withUrlParameters(['fleet_id' => $fleet->esi_fleet_id])
                    ->post('/fleets/{fleet_id}/members', [
                        'character_id' => $invite->character_id,
                        'role' => 'squad_member',
                    ])
                    ->throwIfStatus(420)
                    ->throwIfServerError();

                // Perform an action based on the response status of the invite
                switch ($response->status()) {

                    // Invite sent
                    case 204:
                        $invite->update([
                            'state' => FleetInviteState::SENT,
                            'invite_sent_at' => now(),
                        ]);
                        break;

                    // Invite failed
                    case 422:
                        $invite->update(['state' => FleetInviteState::FAILED]);
                        break;

                    // Unable to send due to no access to fleet. Attempt to find the new
                    // fleet boss, and then stop further processing of other invites for
                    // this fleet
                    case 404:
                        LocateFleetBoss::dispatch($fleet);
                        return false;

                    // Any other status code, just stop processing for this fleet
                    default:
                        return false;
                }
            });
        });
    }
}

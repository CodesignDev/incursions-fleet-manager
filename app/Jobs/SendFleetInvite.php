<?php

namespace App\Jobs;

use App\Enums\FleetInviteState;
use App\Facades\Esi;
use App\Models\Fleet;
use App\Models\FleetInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFleetInvite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The invite to be sent.
     */
    protected FleetInvite $invite;

    /**
     * The fleet that the invite is for.
     */
    protected Fleet $fleet;

    /**
     * Create a new job instance.
     */
    public function __construct(FleetInvite $invite, ?Fleet $fleet = null)
    {
        // If not fleet was provided, check if the invite has it stored
        if (is_null($fleet) && ! $invite->relationLoaded('fleet')) {
            $invite->load('fleet.boss');
        }

        // Store the invite and fleet
        $this->fleet = $fleet ?? $invite->getRelation('fleet');
        $this->invite = $invite->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh the stored invite, then check if it is still valid
        if ($this->invite->refresh()->state !== FleetInviteState::PENDING) {
            return;
        }

        // Character to invite
        $character = $this->invite->character_id;

        // Get the fleet and its fleet boss
        $fleetBoss = $this->fleet->relationLoaded('boss')
            ? $this->fleet->getRelation('boss')
            : $this->fleet->load('boss')->getRelation('boss');

        // Check if the member is in fleet (only if the relation is loaded)
        if ($this->fleet->relationLoaded('members') &&
            $this->fleet->members->contains('character_id', $character)) {
            return;
        }

        // Send the invite to the character
        $response = Esi::withCharacter($fleetBoss)
            ->withUrlParameters(['fleet_id' => $this->fleet->esi_fleet_id])
            ->post('/fleets/{fleet_id}/members', [
                'character_id' => $this->invite->character_id,
                'role' => 'squad_member',
            ])
            ->throwIfStatus(420)
            ->throwIfServerError();

        // Perform an action based on the response status of the invite
        switch ($response->status()) {

            // Invite sent
            case 204:
                $this->invite->update([
                    'state' => FleetInviteState::SENT,
                    'invite_sent_at' => now(),
                ]);
                break;

            // Invite failed
            case 422:
                $this->invite->update(['state' => FleetInviteState::FAILED]);
                break;

            // Unable to send due to no access to fleet. Attempt to find the new
            // fleet boss, and the fleet invite will be attempted again
            case 404:
                LocateFleetBoss::dispatch($this->fleet);
                $this->release(5);
                return;

            // Any other status code, throw the http exception
            default:
                $response->throw();
        }
    }
}

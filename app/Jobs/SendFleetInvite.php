<?php

namespace App\Jobs;

use App\Enums\FleetInviteState;
use App\Facades\Esi;
use App\Models\Fleet;
use App\Models\FleetInvite;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class SendFleetInvite extends EsiFleetJob
{
    use Queueable;

    /**
     * The invite to be sent.
     */
    protected FleetInvite $invite;

    /**
     * The list of relations to be included with the fleet.
     *
     * @var string[]
     */
    protected array $includedRelations = ['boss', 'members'];

    /**
     * The list of relations that are to be eager loaded.
     *
     * @var string[]
     */
    protected array $relationsToLoad = ['boss'];

    /**
     * Create a new job instance.
     */
    public function __construct(FleetInvite $invite, ?Fleet $fleet = null)
    {
        // If no fleet was provided, attempt to grab it from the invite
        if (is_null($fleet) && ! $invite->relationLoaded('fleet')) {
            $invite->load('fleet.boss');
        }

        $this->invite = $invite->withoutRelations();

        parent::__construct($fleet ?? $invite->getRelation('fleet'));
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
        $fleetBoss = $this->fleet->getRelation('boss');

        // Check if the member is in fleet (only if the relation is loaded)
        if ($this->fleet->relationLoaded('members') &&
            $this->fleet->members->contains('character_id', $character)) {
            return;
        }

        // Catch all possible errors from ESI
        try {

            // Check if we have a fleet boss
            if (filled($fleetBoss)) {

                // Query the fleet for fleet members
                $response = Esi::withCharacter($fleetBoss)
                    ->withUrlParameters(['fleet_id' => $this->fleet->esi_fleet_id])
                    ->post('/fleets/{fleet_id}/members', [
                        'character_id' => $this->invite->character_id,
                        'role' => 'squad_member',
                    ])
                    ->throwUnlessStatus(fn ($status) => in_array($status, [404, 422]));

                // Check the status of the response
                if ($response->noContent()) { // 204

                    // Update the fleet with the new information
                    $this->invite->update([
                        'state' => FleetInviteState::SENT,
                        'invite_sent_at' => now(),
                    ]);
                    return;
                }

                // Check if the response was a failure
                else if ($response->unprocessableEntity()) { // 422
                    $this->invite->update(['state' => FleetInviteState::FAILED]);
                    return;
                }
            }

            // If we got to this point, that means that the fleet doesn't have a valid boss so attempt
            // to locate one
            $this->locateFleetBoss($this->fleet);
            return;
        }

        // Catch request errors
        catch (RequestException $e) {

            // 401 and 403 errors are usually authentication errors and shouldn't occur
            // in the normal running
            if (in_array($e->response->status(), [401, 403])) {
                return;
            }

            // Throw any 420 and server errors
            if ($e->response->status() === 420 || $e->response->serverError()) {
                throw $e;
            }
        }

        // Catch any connection errors and just skip this fleet
        catch (ConnectionException) {
            return;
        }
    }
}

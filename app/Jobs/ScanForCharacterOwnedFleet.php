<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Character;
use App\Models\Fleet;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Support\Arr;

class ScanForCharacterOwnedFleet implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The character that is being checked to see if they are in a fleet.
     */
    protected Character $character;

    /**
     * Create a new job instance.
     */
    public function __construct(Character $character)
    {
        $this->character = $character->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Skip if the character is blacklisted
        if ($this->character->onBlacklist) {
            return;
        }

        // Check if the system has a fleet where this character is a member
        if (Fleet::whereHas('members', fn (Builder $builder) => $builder->where('character_id', $this->character->id))->exists()) {
            return;
        }

        // Catch any ESI errors
        try {

            // Query ESI for the character's current fleet
            $response = Esi::defaultEsiVersion('dev')
                ->withUrlParameters(['character_id' => $this->character->id])
                ->get('/characters/{character_id}/fleet')
                ->throwUnlessStatus(404);

            // If the response was a 404, just exit
            if ($response->notFound()) {
                return;
            }

            // Get the fleet id and fleet boss id from the response
            $data = $response->json();
            $fleetId = Arr::get($data, 'fleet_id');
            $fleetBoss = Arr::get($data, 'fleet_boss_id');

            $fleet = null;

            // Check if the fleet already exists in the system
            $unknownFleet = Fleet::whereEsiFleetId($fleetId)->doesntExist();

            // Check if the fleet is not known by the system
            if ($unknownFleet) {

                // Check if the fleet boss is valid
                $validFleetBoss = $fleetBoss === $this->character->id;
                if (! $validFleetBoss) {
                    $characters = Character::whereWhitelisted()
                        ->where('user_id', $this->character->user_id)
                        ->pluck('id');

                    $validFleetBoss = $characters->contains($fleetBoss);
                }

                // If the fleet boss is valid, create the fleet
                if ($validFleetBoss) {
                    $fleet = $this->createFleet($fleetId, $fleetBoss);
                }
            }

            // There already is a fleet in the system with this id (but

            // If the fleet is valid, trigger an update of the fleet info and members.
            // This is added to the current batch so that the system can group all of the
            // jobs together
            if (filled($fleet)) {
                $this->batch()?->add([
                    new FetchFleetInformation($fleet),
                    new FetchFleetMembers($fleet),
                ]);
            }
        }

        // Catch response errors
        catch (RequestException) {

        }

        // Catch connection related errors and requeue the job
        catch (ConnectionException) {
            $this->release();
        }
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new SkipIfBatchCancelled];
    }

    /**
     * Create an untracked fleet for this scan.
     */
    private function createFleet(int $fleetId, int $fleetBoss): Fleet
    {
        return tap(
            Fleet::create([
                'esi_fleet_id' => $fleetId,
                'name' => str('Fleet Scan ')->append('#', $fleetId),
                'untracked' => true,
            ]),
            fn (Fleet $fleet) => $fleet->assignFleetBoss($fleetBoss)
        );
    }
}

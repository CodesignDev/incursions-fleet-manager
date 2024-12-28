<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Character;
use App\Models\FleetMember;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;

class FetchFleetMembers extends EsiFleetJob
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the fleet with the correct relations
        $fleet = $this->fleet->load('members');

        // If there is no fleet boss, attempt to locate the fleet boss
        if (is_null($fleet->boss)) {
            LocateFleetBoss::dispatch($fleet);
            return;
        }

        // Catch errors with this esi request
        try {

            // Check if we have a fleet boss
            if (filled($fleet->boss)) {

                // Query the fleet for fleet members
                $response = Esi::withCharacter($fleet->boss)
                    ->withUrlParameters(['fleet_id' => $fleet->esi_fleet_id])
                    ->get('/fleets/{fleet_id}/members')
                    ->throwUnlessStatus(404);

                // Check if the response was a success
                if ($response->successful()) {

                    // Update the fleet with the new information
                    $data = $response->collect();

                    // Get the list of characters that we know are currently in the fleet
                    $currentCharacters = $fleet->members->pluck('character_id');

                    // Find the characters that have joined and those that have left the fleet
                    $charactersWhoJoined = $data->whereNotIn('character_id', $currentCharacters);
                    $charactersWhoLeft = $currentCharacters->diff($data->pluck('character_id'));

                    // Add the new characters to the database
                    $fleet->members()->saveMany(
                        $charactersWhoJoined->map(fn ($character) => new FleetMember([
                            'character_id' => Arr::get($character, 'character_id'),
                            'location_id' => Arr::get($character, 'solar_system_id'),
                            'ship_id' => Arr::get($character, 'ship_type_id'),
                            'joined_at' => Arr::get($character, 'join_time'),
                            'exempt_from_fleet_warp' => ! Arr::get($character, 'takes_fleet_warp'),
                        ]))
                    );

                    // Delete any fleet member records for any character that has left fleet
                    $fleet->members
                        ->whereIn('character_id', $charactersWhoLeft)
                        ->when(
                            fn ($collection) => $collection->isNotEmpty(),
                            fn ($collection) => $collection->toQuery()->withModelScopes()->delete()
                        );

                    // Update all other records with updated locations and ships if required
                    $fleet->members
                        ->whereIn('character_id', $data->pluck('character_id'))
                        ->each(function ($entry) use ($data) {
                            $data = $data->firstWhere('character_id', $entry->character_id);
                            $entry->update([
                                'location_id' => Arr::get($data, 'solar_system_id'),
                                'ship_id' => Arr::get($data, 'ship_type_id'),
                                'joined_at' => Arr::get($data, 'join_time'),
                                'exempt_from_fleet_warp' => ! Arr::get($data, 'takes_fleet_warp'),
                            ]);
                        });

                    // If there is any characters that the system does not know about, try to fetch
                    // the information about those characters from ESI
                    if ($charactersWhoJoined->isNotEmpty()) {

                        // Find the list of possible unknown characters
                        $unknownCharacters = $charactersWhoJoined
                            ->where(fn($character) => Character::query()->where('id', $character)->doesntExist());

                        // Dispatch the relevant jobs for these unknown character(s)
                        Bus::chain([
                            new FetchMissingCharacterInformation($unknownCharacters->all()),
                            new FetchCharacterAffiliation($unknownCharacters->all()),
                            new FetchCharacterOwnerInformation($unknownCharacters->all()),
                        ])->dispatchIf($unknownCharacters->isNotEmpty());
                    }

                    return;
                }
            }

            // If we got to this point, that means that the fleet doesn't have a valid boss so attempt
            // to locate one
            $this->locateFleetBoss($fleet);
            return;
        }

        // Catch request errors
        catch (RequestException $e) {

            // Capture error 401 and 403 and just skip processing this particular fleet
            if (in_array($e->response->status(), [401, 403])) {
                return;
            }

            // If the status code received was a server error (5xx) or 420 then throw the
            // exception. The job middleware will handle the relevant status codes.
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

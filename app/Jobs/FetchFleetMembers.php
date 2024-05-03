<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Fleet;
use App\Models\FleetMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchFleetMembers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the list of active fleets
        $fleets = Fleet::with(['boss', 'members'])->whereTracked()->get();

        // Loop through each fleet, fetching the list of members for each
        $fleets->each(function (Fleet $fleet) {

            // If there is no fleet boss, attempt to locate the fleet boss
            if (is_null($fleet->boss)) {
                LocateFleetBoss::dispatch($fleet);
                return;
            }

            // Catch errors with this esi request
            try {
                // Query the fleet for fleet members
                $response = Esi::withCharacter($fleet->boss)
                    ->withUrlParameters(['fleet_id' => $fleet->esi_fleet_id])
                    ->get('/fleet/{fleet_id}/members')
                    ->throw()
                    ->collect();

                // Get the list of characters that we know are currently in the fleet
                $currentCharacters = $fleet->members->pluck('character_id');

                // Find the characters that have joined and those that have left the fleet
                $charactersWhoJoined = $response->whereNotIn('character_id', $currentCharacters);
                $charactersWhoLeft = $currentCharacters->doesntContain($response->pluck('character_id'));

                // Add the new characters to the database
                $fleet->members()->saveMany(
                    $charactersWhoJoined->map(fn ($character) => new FleetMember([
                        'character_id' => $character['character_id'],
                        'joined_at' => $character['join_time'],
                        'exempt_from_fleet_warp' => $character['takes_fleet_warp'],
                    ]))
                );

                // Delete any fleet member records for any character that has left fleet
                $fleet->members
                    ->whereIn('character_id', $charactersWhoLeft)
                    ->when(
                        fn ($collection) => $collection->isNotEmpty(),
                        fn ($collection) => $collection->toQuery()->withModelScopes()->delete()
                    );
            }
            // Catch request errors
            catch (RequestException $e) {

                // If the response is a 404, then the fleet may have a new boss or has closed.
                // Fire off the locate fleet boss job to try and locate who the fleet is under.
                // If the fleet has closed, then this will automatically be handled for us
                if ($e->response->status() === 404) {
                    LocateFleetBoss::dispatch($fleet);
                    return;
                }

                // If the error returned was a different client error, this will most likely mean
                // that the token was invalid, but as we're running the proxy, this *shouldn't*
                // happen to us. The only other code that we do care about is 420 which means we
                // were esi error limited so throw this up, for the relevant middleware to capture.

                // Capture error 401 and 403 and just skip processing this particular fleet
                if (in_array($e->response->status(), [401, 403])) {
                    return;
                }

                // If the status code received was a server error (5xx) or 420 then throw the
                // exception. The job middleware will handle the relevant status codes.
                if ($e->response->status() === 420 || $e->response->serverError()) {
                    throw;
                }
            }

            // Catch any connection errors and just skip this fleet
            catch (ConnectionException) {
                return;
            }
        });
    }
}

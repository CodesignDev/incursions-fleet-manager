<?php

namespace App\Jobs;

use App\Facades\Esi;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;

class FetchFleetInformation extends EsiFleetJob
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load the relevant relations for the fleet
        $fleet = $this->fleet;

        // If there is no fleet boss, attempt to locate the fleet boss
        if (is_null($fleet->boss)) {
            $this->locateFleetBoss($fleet);
            return;
        }

        // Catch errors with this esi request
        try {

            // Check if we have a fleet boss
            if (filled($fleet->boss)) {

                // Query the fleet for fleet members
                $response = Esi::withCharacter($fleet->boss)
                    ->withUrlParameters(['fleet_id' => $fleet->esi_fleet_id])
                    ->get('/fleets/{fleet_id}')
                    ->throwUnlessStatus(404);

                // Check if the response was a success
                if ($response->successful()) {

                    // Update the fleet with the new information
                    $data = $response->json();
                    $fleet->update([
                        'has_fleet_advert' => Arr::get($data, 'is_registered', false),
                        'free_move_enabled' => Arr::get($data, 'is_free_move', false),
                    ]);

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

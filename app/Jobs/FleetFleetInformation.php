<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Fleet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class FleetFleetInformation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the list of active fleets
        $fleets = Fleet::with(['boss'])->whereTracked()->get();

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
                    ->get('/fleet/{fleet_id}')
                    ->throw()
                    ->json();

                // Update the fleet with the new information
                $fleet->update([
                    'has_fleet_advert' => Arr::get($response, 'is_registered', false),
                    'free_move_enabled' => Arr::get($response, 'is_free_move', false),
                ]);
            } // Catch request errors
            catch (RequestException $e) {

                // Handle 404 errors by attempting to locate the fleet boss
                if ($e->response->status() === 404) {
                    LocateFleetBoss::dispatch($fleet);
                    return;
                }

                // 401 and 403 errors are usually authentication errors and shouldn't occur
                // in the normal running
                if (in_array($e->response->status(), [401, 403])) {
                    return;
                }

                // Throw any 420 and server errors
                if ($e->response->status() === 420 || $e->response->serverError()) {
                    throw $e;
                }
            } // Catch any connection errors and just skip this fleet
            catch (ConnectionException) {
                return;
            }
        });
    }
}

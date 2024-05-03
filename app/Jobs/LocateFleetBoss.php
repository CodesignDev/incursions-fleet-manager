<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Fleet;
use App\Models\FleetMember;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class LocateFleetBoss implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The fleet instance we are querying.
     *
     * @var Fleet|null
     */
    protected ?Fleet $fleet;

    /**
     * Create a new job instance.
     */
    public function __construct(Fleet|string|int $fleet)
    {
        // Save the fleet instance
        $this->fleet = $fleet instanceof Fleet
            ? $fleet->withoutRelations()
            : Fleet::query()
                ->when(
                    is_numeric($fleet),
                    fn(Builder $query) => $query->where('esi_fleet_id', $fleet),
                    fn(Builder $query) => $query->where('id', $fleet)
                )
                ->first();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Wrap in a try/finally, so we can do some post-processing if an exception occurs
        try {

            // If there is no fleet, there is nothing to process
            if (is_null($this->fleet)) {
                return;
            }

            $fleet = $this->fleet;
            $fleetId = $fleet->esi_fleet_id;

            // Load the list of fleet members to attempt to find the fleet boss with
            $fleet->load('members');

            $recentlyTriedCharacters = collect(
                cache()->get('fleet_boss:recently_searched_characters', [])
            );

            // Get the character that is currently the boss of the fleet
            $currentFleetBoss = optional($fleet->members->firstWhere('fleet_boss', true))
                ->character_id;

            // Attempt to query the fleet with the current fleet boss, if it works, then there is
            // nothing to do
            try {
                Esi::withCharacter($currentFleetBoss)
                    ->withUrlParameters(['fleet_id' => $fleetId])
                    ->get('/fleets/{fleet_id}')
                    ->throw();

                // If we got to this point, this means that the fleet is still under the correct
                // fleet boss
                return;
            } // Handle the relevant exceptions
            catch (Exception $e) {
                // We only care about failures that were because of hitting the esi error limit or
                // because of a server error
                if ($e instanceof RequestException && ($e->response->status() === 420 || $e->response->serverError())) {
                    throw;
                }

                // Continue if it was another issue
            }

            // Run in a loop until we find what we need
            while (true) {

                // Pick a random fleet member from the list
                $member = $fleet->members
                    ->whereNotIn('character_id', $recentlyTriedCharacters)
                    ->pluck('character_id')
                    ->random();

                // If this character is the current fleet boss, skip it
                if ($member === $currentFleetBoss) {
                    continue;
                }

                $recentlyTriedCharacters->push($member);

                // Check if this character is in a fleet and if it is the same as the fleet we are
                // querying
                try {
                    $response = Esi::withUrlParameters(['character_id' => $member])
                        ->get('/dev/characters/{character_id}/fleet')
                        ->throw()
                        ->json();

                    // Check the fleet id in the response matches our fleet
                    if (Arr::get($response, 'fleet_id') !== $fleetId) {
                        continue;
                    }

                    // Check the fleet boss
                    if (($fleetBossId = Arr::get($response, 'fleet_boss_id')) === $currentFleetBoss) {
                        continue;
                    }

                    // Flag the relevant fleet member as the boss of the fleet
                    $fleetBoss = $fleet->members->whereIn('character_id', $fleetBossId)->first();
                    if (!is_null($fleetBoss)) {
                        $fleetBoss->update(['fleet_boss' => true]);
                        break;
                    }

                    // Otherwise add the boss as a member of the fleet
                    $fleet->members()->create([
                        'character_id' => $fleetBossId,
                        'fleet_boss' => true,
                    ]);
                } // Catch and handle request exceptions
                catch (RequestException $e) {
                    // Throw server errors and esi error limits up
                    if ($e->response->serverError() && $e->response->status() === 420) {
                        throw;
                    }

                    // This should just be a 404 error so just move to the next character. 401 and
                    // 403 errors shouldn't happen so just silently ignore these for now
                    continue;
                }
            }
        }

        // Run some post-processing actions
        finally {
            // Update the old fleet boss to show as no longer being the boss
            if (isset($currentFleetBoss) && filled($currentFleetBoss)) {
                FleetMember::where('character_id', $currentFleetBoss)->update(['fleet_boss' => false]);
            }

            // Save the list of characters that were queried in case we need to run this again
            if (isset($recentlyTriedCharacters)) {
                cache()->put(
                    'fleet_boss:recently_searched_characters',
                    Collection::wrap($recentlyTriedCharacters)->all(),
                    now()->addMinutes(5)
                );
            }
        }
    }
}

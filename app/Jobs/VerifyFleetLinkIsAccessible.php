<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Helpers\FleetLink;
use App\Models\Character;
use App\Models\Fleet;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Support\Collection;

class VerifyFleetLinkIsAccessible implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The user whose characters we are currently checking the fleet link against.
     */
    protected User $user;

    /**
     * The fleet link that needs to be verified.
     */
    protected string $fleetLink;

    /**
     * A possible character that could be the boss of the fleet.
     */
    protected ?Character $possibleCharacter = null;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $fleetLink)
    {
        $this->user = $user->withoutRelations();
        $this->fleetLink = $fleetLink;
    }

    /**
     * Execute the job.
     *
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public function handle(): void
    {
        // Get the fleet id from the url
        $fleetLink = $this->fleetLink;
        $fleetId = FleetLink::extractFleetIdFromLink($fleetLink);

        // List of characters that have been attempted for this fleet
        $checkedCharacters = Collection::wrap(
            cache()->get('fleet-link-verification:'.$fleetId.':characters', [])
        );

        // Get the list of valid characters from the current user
        /** @var \Illuminate\Database\Eloquent\Collection $characters */
        $characters = $this->user->characters()->whereWhitelisted()->get();

        // Remove any characters that are currently in a fleet
        $fleets = Fleet::with('members')->whereTracked()->get();
        $characters = $characters->reject(function ($character) use ($fleets) {
            return $fleets->contains(
                function ($fleet) use ($character) {
                    return $fleet->members->contains('character_id', $character->id);
                }
            );
        });

        // If the fleet id is already known by the app, just exit since we don't need to check
        // the link
        if (Fleet::whereEsiFleetId($fleetId)->exists()) {
            return;
        }

        try {

            // Loop through each character checking to see if the character can access the fleet
            $characters->each(function (Character $character) use ($fleetLink, $fleetId, $checkedCharacters) {

                // Skip this character if it has already been checked
                if ($checkedCharacters->contains($character->id)) {
                    return;
                }

                // Catch errors with ESI requests
                try {

                    // Make the relevant request(s) to ESI to check if this character is the one that
                    // controls the fleet. Set the character as one that has been checked in case this
                    // needs to run again in case of any errors
                    return ! tap(
                        $this->attemptCharacterCheck($character, $fleetLink, $fleetId),
                        fn () => $checkedCharacters->push($character->id)
                    );
                }

                // Catch all ESI errors
                catch (RequestException $e) {

                    // If there is a token issue (401, 403), assume there is an issue up the chain so move
                    // to the next character
                    if ($e->response->unauthorized() || $e->response->forbidden()) {
                        return;
                    }

                    // Throw all other responses up the chain
                    throw $e;
                }

                // If we got a connection error, try again in a bit
                catch (ConnectionException) {
                    $this->release();
                    return false; // Returning false breaks out of the each() call
                }
            });

            // If we got here, it means we didn't find a character that was in control of the fleet
            // However, if a character was flagged as possibly being in control of the fleet, maybe
            // due to a desync in ESI responses (character could see the fleet but ESI was saying
            // that the character wasn't in the fleet due to ESI cache timings), we will need to
            // recheck that character, so the flag the character to be rechecked and then re-tun the
            // job
            if (! is_null($this->possibleCharacter)) {

                // Allow this character to be checked again
                $checkedCharacters = $checkedCharacters->except($this->possibleCharacter->id);

                // Requeue the job for 15 seconds time
                $this->release(15);
                return;
            }

        } finally {

            // Update the cache with the updated list of characters. This is cached for 5 minutes
            // while the job is running and cleared after it succeeded.
            cache()->put('fleet-link-verification:'.$fleetId.':characters', $checkedCharacters->all(), 300);
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
     * Check the character is the boss of the fleet via ESI.
     *
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    private function attemptCharacterCheck(Character $character, string $fleetLink, int $fleetId): bool
    {
        // Make request to check if this character has access to the fleet link
        $fleetLinkEsiResponse = Esi::withCharacter($character)
            ->get($fleetLink)
            ->throwUnlessStatus(404);

        // If we 404'd, then move to the next character
        if ($fleetLinkEsiResponse->notFound()) {
            return false;
        }

        // Since this character has access to the fleet, check if they are the fleet boss by
        // querying the character fleet endpoint
        $characterEsiResponse = Esi::defaultEsiVersion('dev')
            ->withUrlParameters(['character_id' => $character->id])
            ->get('/characters/{character_id}/fleet')
            ->throwUnlessStatus(404);

        // If this response returns a 404, we can't determine if this character is the boss
        // of the fleet. So mark the character as a possible candidate that we can check at
        // the end.
        if ($characterEsiResponse->notFound()) {
            $this->possibleCharacter = $character;
            return false;
        }

        // Get the data from the response
        $data = $characterEsiResponse->json();

        // If the fleet doesn't match then skip
        if (data_get($data, 'fleet_id') !== $fleetId) {
            return false;
        }

        // Skip if the boss of this fleet doesn't match the current character
        $fleetBoss = data_get($data, 'fleet_boss_id', -1);
        if ($fleetBoss !== $character->id) {
            return false;
        }

        // This character is the boss of the fleet so create the fleet in the system
        $this->createFleet($fleetId, $character);
        return true;
    }

    /**
     * Create the fleet instance in the database.
     */
    private function createFleet(int $fleetId, Character $character): void
    {
        // Create the fleet and assign the current character as the fleet boss
        tap(
            Fleet::create([
                'esi_fleet_id' => $fleetId,
                'untracked' => true,
            ]),
            fn (Fleet $fleet) => $fleet->assignFleetBoss($character)
        );
    }
}

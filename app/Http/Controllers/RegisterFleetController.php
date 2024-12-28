<?php

namespace App\Http\Controllers;

use App\Facades\Esi;
use App\Helpers\FleetLink;
use App\Http\Requests\RegisterFleetRequest;
use App\Models\Fleet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegisterFleetController extends Controller
{
    public function __invoke(RegisterFleetRequest $request): RedirectResponse
    {
        // The resulting fleet
        $fleet = null;

        // Get the validated data
        $data = $request->safe();

        // If the user provided a fleet boss instead of a fleet url, try and find the fleet
        // from the fleet boss character selected
        if ($data->has('fleet_boss')) {

            // Get the fleet boss
            $fleetBoss = $data->input('fleet_boss');

            // Check to see if we have a fleet in the system with the requested fleet boss
            $fleet = Fleet::whereFleetBoss($fleetBoss)->firstOr(function () use ($fleetBoss) {

                // Try to get the fleet that the character is currently in via ESI
                $fleetId = $this->locateFleetFromCharacter($fleetBoss);

                // Create fleet with the fleet id and the fleet boss
                return $this->registerFleet([
                    'fleet_id' => $fleetId,
                    'fleet_boss' => $fleetBoss
                ]);
            });
        }

        // Otherwise if we have a url, try and get the fleet for
        else if ($data->has('url')) {

            // Get the fleet id from the url
            $fleetId = FleetLink::extractFleetIdFromLink(
                $data->input('url')
            );

            // Check to see if we have a fleet in the system for this fleet id
            $fleet = Fleet::whereEsiFleetId($fleetId)->firstOr(function () use ($request, $fleetId) {

                // Try and find the character that owns this fleet
                $fleetBoss = $this->locateFleetFromLink($request->user(), $fleetId);

                // Create fleet with the fleet id and the fleet boss
                return $this->registerFleet([
                    'fleet_id' => $fleetId,
                    'fleet_boss' => $fleetBoss
                ]);
            });
        }

        // If we have no fleet, throw an error
        if (is_null($fleet)) {
            throw ValidationException::withMessages([
                'fleet' => 'Could not register fleet.',
            ]);
        }

        // If we have a fleet, update it to a tracked fleet if required
        if ($fleet->exists) {
            $fleet->update([
                'name' => $data->input('name'),
                'untracked' => false,
            ]);
        }

        return to_route('fleets.show', $fleet)->with([
            'status' => 'Fleet Successfully Registered',
        ]);
    }

    /**
     * Attempt to locate the fleet from a known fleet boss.
     */
    private function locateFleetFromCharacter(int $character): int
    {
        // Make a request to ESI to try and get the fleet details
        $response = rescue(
            fn () => Esi::defaultEsiVersion('dev')
                ->withUrlParameters(['character_id' => $character])
                ->get('/characters/{character_id}/fleet')
                ->json(),
            fn () => ['http_error' => 'Unknown error'],
            report: false // Don't report these errors
        );

        // Test the response and return a validation error if it is not as expected
        $validator = Validator::make($response, [
            'http_error' => 'missing',
            'error' => 'missing',
            'fleet_id' => 'required|int',
            'fleet_boss_id' => 'required|int|in:'.$character,
        ])->stopOnFirstFailure();

        // Validate the response
        if ($validator->failed()) {
            $errors = $validator->errors();

            // Throw a custom validation exception with our own formatted messages
            throw ValidationException::withMessages([
                'fleet' => match (true) {
                    $errors->has('error') => 'Could not register fleet. An ESI error occurred.',
                    $errors->has('fleet_id') => 'The requested character is not in a fleet.',
                    $errors->has('fleet_boss_id') => 'The requested character is not the boss of the fleet.',
                    default => 'Could not register fleet. An unknown error occurred.',
                }
            ]);
        }

        // Return the fleet id for the fleet that was found
        return data_get($response, 'fleet_id');
    }

    /**
     * Attempt to locate the fleet from a known fleet id.
     */
    private function locateFleetFromLink(User $user, int $fleetId): int
    {
        // Get the list of character that belong to the character
        /** @var \Illuminate\Database\Eloquent\Collection $characters */
        $characters = $user->characters()->whereWhitelisted()->get();

        // Find the first character that can access the fleet link
        $fleetBoss = $characters->firstWhere(
            fn ($character) => rescue(function () use ($character, $fleetId) {
                Esi::withCharacter($character)
                    ->withUrlParameters(['fleet_id' => $fleetId])
                    ->get('/fleets/{fleet_id}')
                    ->throw();

                return true;
            }, rescue: false, report: false)
        );

        // If no fleet boss was found, throw a validation error
        if (is_null($fleetBoss)) {
            throw ValidationException::withMessages([
                'fleet' => 'Could not register fleet. Unable to locate fleet boss for the requested fleet.'
            ]);
        }

        return $fleetBoss->id;
    }

    /**
     * Register the fleet in the database.
     */
    private function registerFleet(array $attributes): Fleet
    {
        // Return a blank fleet if no fleet id was provided
        if (! Arr::has($attributes, ['fleet_id', 'fleet_boss'])) {
            return new Fleet();
        }

        $fleetAttributes = [
            'esi_fleet_id' => Arr::get($attributes, 'fleet_id'),
            ...Arr::only($attributes, ['name'])
        ];

        // Create the fleet and assign the fleet boss
        return tap(Fleet::create($fleetAttributes), function (Fleet $fleet) use ($attributes) {
            $fleet->assignFleetBoss(
                Arr::get($attributes, 'fleet_boss')
            );
        });
    }
}

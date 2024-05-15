<?php

namespace App\Http\Controllers;

use App\Facades\Esi;
use App\Http\Requests\RegisterFleetRequest;
use App\Models\Fleet;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegisterFleetController extends Controller
{
    public function __invoke(RegisterFleetRequest $request): RedirectResponse
    {
        $fleet = null;

        // Get the validated data
        $data = $request->safe();

        // If the user provided a fleet boss instead of a fleet url, try and find the fleet
        // from the fleet boss character selected
        if ($data->has('fleet_boss')) {
            $fleetBoss = $data->fleet_boss;

            // Try and find a fleet with this fleet boss (in case it has already been scanned)
            $fleet = Fleet::whereFleetBoss($fleetBoss)->first();

            // If no fleet was found, attempt to find the fleet via ESI
            if (is_null($fleet)) {

                // Send request to ESI to fetch the fleet
                $fleetEsiResponse = rescue(function () use ($fleetBoss) {
                    return Esi::defaultEsiVersion('dev')
                        ->withUrlParameters(['character_id' => $fleetBoss])
                        ->get('/characters/{character_id}/fleet')
                        ->throw()
                        ->json();
                }, function ($e) {
                    if (! $e instanceof RequestException) {
                        return ['http_error' => 'Unknown error'];
                    }

                    // Handle 404 errors
                    if ($e->response->notFound()) {
                        return [];
                    }

                    return $e->response->json();
                });

                // Validate the response
                $fleetEsiValidator = Validator::make($fleetEsiResponse, [
                    'http_error' => 'missing',
                    'error' => 'missing',
                    'fleet_id' => 'required|int',
                    'fleet_boss_id' => [
                        'required',
                        Rule::in([$fleetBoss])
                    ],
                ]);

                // If the esi validation fails, then throw an error
                if ($fleetEsiValidator->stopOnFirstFailure()->failed()) {
                    $errors = $fleetEsiValidator->errors();

                    throw ValidationException::withMessages([
                        'fleet' => match (true) {
                            $errors->has('error') => 'Could not register fleet.',
                            $errors->has('fleet_id') => 'The selected character is not in a fleet.',
                            $errors->has('fleet_boss_id') => 'The selected character is not the boss of the fleet.',
                            default => 'An unknown error occurred.',
                        }
                    ]);
                }

                // Register the fleet
                $fleet = $this->registerFleet([
                    'esi_fleet_id' => $fleetEsiValidator->safe()->fleet_id,
                    'name' => $data->name,
                    'fleet_boss' => $fleetBoss,
                ]);
            }
        }

        // Otherwise get the URL, and register the fleet using this
        else if ($data->has('url')) {
            $fleetId = str($data->url)
                ->match(RegisterFleetRequest::ESI_FLEET_URL_REGEX)
                ->toInteger();

            // Try and find the fleet boss for this fleet
            $characters = $request->user()->characters()->whereWhitelisted()->pluck('id');
            $fleetBoss = null;
            foreach ($characters as $character) {
                $validCharacter = rescue(function () use ($fleetId, $character) {
                    Esi::withCharacter($character)
                        ->withUrlParameters(['fleet_id' => $fleetId])
                        ->throw()
                        ->get('/fleets/{fleet_id}/members');

                    return true;
                }, false);

                if ($validCharacter) {
                    $fleetBoss = $character;
                    break;
                }
            }

            // If we have a fleet boss, register the fleet
            if (! is_null($fleetBoss)) {
                $fleet = $this->registerFleet([
                    'esi_fleet_id' => $fleetId,
                    'name' => $data->name,
                    'fleet_boss' => $fleetBoss,
                ]);
            }
        }

        if (is_null($fleet)) {
            throw ValidationException::withMessages([
                'fleet' => 'Could not register fleet.',
            ]);
        }

        return back(303)->with([
            'status' => 'Fleet Registered',
            'fleet_id' => $fleet->id,
        ]);
    }

    /**
     * Register the fleet in the database.
     */
    private function registerFleet(array $attributes): Fleet
    {
        if (! Arr::has($attributes, ['esi_fleet_id', 'name'])) {
            return new Fleet();
        }

        $fleetAttributes = Arr::only($attributes, ['esi_fleet_id', 'name']);
        if (count($fleetAttributes) <= 0) {
            return new Fleet();
        }

        return tap(Fleet::create($fleetAttributes), function (Fleet $fleet) use ($attributes) {
            $fleet->assignFleetBoss(data_get($attributes, 'fleet_boss'));
        });
    }
}

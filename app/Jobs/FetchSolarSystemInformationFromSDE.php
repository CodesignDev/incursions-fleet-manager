<?php

namespace App\Jobs;

use App\Exceptions\InvalidEveIdRange;
use App\Models\Universe\Constellation;
use App\Models\Universe\Region;
use App\Models\Universe\SolarSystem;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Number;

class FetchSolarSystemInformationFromSDE implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The solar system info to fetch from the SDE / ESI.
     */
    protected int $solarSystemId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $solarSystemId)
    {
        $this->solarSystemId = $solarSystemId;
    }

    /**
     * Execute the job.
     *
     * @throws \App\Exceptions\InvalidEveIdRange
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function handle(): void
    {
        // If the system already exists, skip
        if (blank($this->solarSystemId) || SolarSystem::whereSystemId($this->solarSystemId)->exists()) {
            return;
        }

        // If the ID range is invalid, throw an exception and fail
        if (Number::clamp($this->solarSystemId, 30_000_000, 33_000_000) !== $this->solarSystemId) {
            throw (new InvalidEveIdRange())->withId($this->solarSystemId, ExpectedEveIdRange::AllSolarSystems);
        }

        // Catch any and all http errors
        try {

            // Fetch the system information and name from the SDE
            $systemInformation = Http::sde()
                ->withUrlParameters(['system_id' => $this->solarSystemId])
                ->get('/universe/solarSystems/{system_id}')
                ->throw()
                ->fluent();

            // Fetch the system name
            $systemName = $this->fetchNameFromSde($this->solarSystemId, 'Unknown System #'.$this->solarSystemId);

            // Extract the constellation and region ids from the response
            $constellationId = $systemInformation->value('constellationID');
            $regionId = $systemInformation->value('regionID');

            // Create the solar system entry for the requested system
            SolarSystem::create([
                'system_id' => $systemInformation->value('solarSystemID', $this->solarSystemId),
                'constellation_id' => $constellationId,
                'name' => $systemName,
                'security' => $systemInformation->value('security', 0),
                'radius' => $systemInformation->value('radius'),
            ]);

            // Fetch and create the constellation info if the information doesn't exist in the database
            if (filled($constellationId) && Constellation::whereConstellationId($constellationId)->doesntExist()) {

                // Doesn't exist, so make the request to the SDE
                $constellationInfo = Http::sde()
                    ->withUrlParameters(['constellation_id' => $constellationId])
                    ->get('/universe/constellations/{constellation_id}')
                    ->throw()
                    ->fluent();

                // Fetch the name for the constellation
                $constellationName = $this->fetchNameFromSde($constellationId, 'Unknown Constellation #'.$constellationId);

                // Create the constellation
                Constellation::create([
                    'constellation_id' => $constellationInfo->value('constellationID', $constellationId),
                    'region_id' => $constellationInfo->value('regionID', $regionId),
                    'name' => $constellationName,
                    'radius' => $constellationInfo->value('radius'),
                ]);
            }

            // Create the region info if it doesn't exist in the database
            if (filled($regionId) && Region::whereRegionId($regionId)->doesntExist()) {

                // Doesn't exist, so make the request to the SDE
                $regionInfo = Http::sde()
                    ->withUrlParameters(['region_id' => $regionId])
                    ->get('/universe/regions/{region_id}')
                    ->throw()
                    ->fluent();

                // Fetch the name for the region
                $regionName = $this->fetchNameFromSde($regionId, 'Unknown Region #'.$regionId);

                // Create the region
                Region::create([
                    'region_id' => $regionInfo->value('regionID', $regionId),
                    'name' => $regionName,
                ]);
            }
        }

        // HTTP related errors... handle particular errors
        catch (RequestException $e) {

            // Requeue if we encounter any server errors
            if ($e->response->serverError()) {
                $this->release();
            }

            // If the item is not found (which shouldn't happen), just exit but report the exception
            if ($e->response->notFound()) {
                report($e);
                return;
            }

            // Otherwise throw the exception up
            throw $e;
        }

        // Connection errors... just requeue the job
        catch (ConnectionException) {
            $this->release();
        }
    }

    /**
     * Fetch the name for an item from the SDE
     */
    private function fetchNameFromSde(int $itemId, $default = null)
    {
        return rescue(fn () => Http::sde()
            ->withUrlParameters(['item_id' => $itemId])
            ->get('/inventory/names/{item_id}')
            ->json('itemName', $default), rescue: $default);
    }
}

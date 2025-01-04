<?php

namespace App\Jobs;

use App\Enums\EveIdRange;
use App\Exceptions\InvalidEveIdRange;
use App\Jobs\Middleware\HandleSdeErrors;
use App\Models\Universe\Constellation;
use App\Models\Universe\Region;
use App\Models\Universe\SolarSystem;
use App\Traits\FetchesNamesFromSde;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchSolarSystemInformation implements ShouldQueue
{
    use Batchable, FetchesNamesFromSde, Queueable;

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
        if (EveIdRange::isValidId($this->solarSystemId, EveIdRange::AllSolarSystems)) {
            throw (new InvalidEveIdRange())->withId($this->solarSystemId, EveIdRange::AllSolarSystems);
        }

        // Fetch the system information and name from the SDE
        $systemInfo = Http::sde()
            ->withUrlParameters(['system_id' => $this->solarSystemId])
            ->get('/universe/solarSystems/{system_id}')
            ->throw()
            ->fluent();

        // Fetch the system name
        $systemName = $this->fetchNameFromSde($this->solarSystemId, 'Unknown System #'.$this->solarSystemId);

        // Extract the constellation and region ids from the response
        $constellationId = $systemInfo->value('constellationID');
        $regionId = $systemInfo->value('regionID');

        // Create the solar system entry for the requested system
        tap(SolarSystem::create([
            'id' => $systemInfo->value('solarSystemID', $this->solarSystemId),
            'constellation_id' => $constellationId,
            'name' => $systemName,
            'security' => $systemInfo->value('security', 0),
            'radius' => $systemInfo->value('radius'),
        ]), function (SolarSystem $system) use ($systemInfo) {

            /** @noinspection PhpParamsInspection */
            $system
                ->setPosition(...$systemInfo->value('center', []))
                ->setPosition(...$systemInfo->value('min', []), prefix: 'min')
                ->setPosition(...$systemInfo->value('max', []), prefix: 'max');
        });

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
            tap (Constellation::create([
                'id' => $constellationInfo->value('constellationID', $constellationId),
                'region_id' => $constellationInfo->value('regionID', $regionId),
                'name' => $constellationName,
                'radius' => $constellationInfo->value('radius'),
            ]), function (Constellation $constellation) use ($constellationInfo) {

                /** @noinspection PhpParamsInspection */
                $constellation
                    ->setPosition(...$constellationInfo->value('center', []))
                    ->setPosition(...$constellationInfo->value('min', []), prefix: 'min')
                    ->setPosition(...$constellationInfo->value('max', []), prefix: 'max');
            });
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
            tap(Region::create([
                'id' => $regionInfo->value('regionID', $regionId),
                'name' => $regionName,
            ]), function (Region $region) use ($regionInfo) {

                /** @noinspection PhpParamsInspection */
                $region
                    ->setPosition(...$regionInfo->value('center', []))
                    ->setPosition(...$regionInfo->value('min', []), prefix: 'min')
                    ->setPosition(...$regionInfo->value('max', []), prefix: 'max');
            });
        }
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new HandleSdeErrors];
    }
}

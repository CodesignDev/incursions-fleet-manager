<?php

namespace App\Jobs;

use App\Enums\EveIdRange;
use App\Enums\SolarSystemCelestialType;
use App\Exceptions\InvalidEveIdRange;
use App\Facades\Esi;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class FetchCelestialsForSolarSystem implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The solar system which the celestial info is to be fetched for from the SDE.
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
     */
    public function handle(): void
    {
        // If no solar system was provided, skip
        if (blank($this->solarSystemId)) {
            return;
        }

        // If the ID range is invalid, throw an exception and fail
        if (EveIdRange::isValidId($this->solarSystemId, EveIdRange::AllSolarSystems)) {
            throw (new InvalidEveIdRange())->withId($this->solarSystemId, EveIdRange::AllSolarSystems);
        }

        // Make an initial request to ESI to get the list of celestial ids.
        // We use ESI here instead of the SDE since ESI returns all the IDs at once instead of just
        // the next layer down.
        $celestials = rescue(fn () => transform(
            Esi::public()
                ->withUrlParameters(['system_id' => $this->solarSystemId])
                ->get('/universe/systems/{system_id}')
                ->throw()
                ->fluent(),
            $this->getIdsFromEsiResponse(),
            []
        ), fn () => $this->collateCelestialIdsFromSde());

        // Build a list of celestials to fetch and create jobs for them
        $celestialJobBatch = Bus::batch(
            collect($celestials)
                ->except(['stations', 'has_all_stations'])
                ->flatMap(fn ($group, $key) => Arr::map(
                    Arr::wrap($group),
                    fn ($value) => ['celestial' => $value, 'type' => Str::singular($key)]
                ))
                ->filter(fn ($item) => Arr::has($item, ['celestial', 'type']) && Arr::where($item, 'filled'))
                ->map(fn ($item) => new FetchCelestialInformation(
                    $item['celestial'],
                    SolarSystemCelestialType::from(Str::studly($item['type'])),
                    $this->solarSystemId,
                    $celestials
                ))
        );

        // Build a list of jobs to fetch the npc stations
        $stationJobBatch = collect($celestials)
            ->only('stations')
            ->flatten()
            ->mapInto(FetchNpcStationInformation::class);

        // Dispatch the relevant jobs
        Bus::batch([
            [
                $celestialJobBatch,
                new BuildOrbitalMapForSolarSystem($this->solarSystemId)
            ],
            ...$stationJobBatch,
        ])
            ->withOption('fetch_npc_stations', blank(Arr::get($celestials, 'stations', [])))
            ->dispatch();

    }

    /**
     * Creates a processor that fetches the list of celestials from the solar system esi response.
     */
    private function getIdsFromEsiResponse(): callable
    {
        return function (Fluent $data): array
        {
            // Planet data
            $planetData = $data->collect('planets');

            return collect([
                'star' => $data->value('star_id'),
                'planets' => $planetData->pluck('planet_id'),
                'moons' => $planetData->flatMap(fn ($planet) => data_get($planet, 'moons', [])),
                'asteroid_belts' => $planetData->flatMap(fn ($planet) => data_get($planet, 'asteroid_belts', [])),
                'stations' => $data->value('stations'),
            ])->toArray();
        };
    }

    /**
     * Collate the list of celestial ids by querying the SDE instead of ESI
     */
    private function collateCelestialIdsFromSde(): array
    {
        try {

            // Get the system from the SDE
            $system = Http::sde()
                ->withUrlParameters(['system_id' => $this->solarSystemId])
                ->get('/universe/solarSystems/{system_id}')
                ->fluent();

            // Extract the star and planet data
            $star = $system->get('star.id');
            $planets = $system->collect('planets');

            // For each planet, make a request to the sde to get the moons and belt ids
            $planetData = $planets->map(
                fn ($planet) => rescue(fn () => Http::sde()
                    ->withUrlParameters(['planet_id' => $planet])
                    ->get('/universe/planets/{planet_id}')
                    ->throw()
                    ->collect()
                    ->only(['moons', 'asteroidBelts', 'npcStations']), [])
            );

            return collect([
                'star' => $star,
                'planets' => $planets,
                'moons' => $planetData->flatMap(fn ($planet) => data_get($planet, 'moons', [])),
                'asteroid_belts' => $planetData->flatMap(fn ($planet) => data_get($planet, 'asteroidBelts', [])),

                // This is not the full list of stations from here due to how npc stations are listed in the SDE
                // 'stations' => $planetData->map(fn ($planet) => array_keys(data_get($planet, 'npcStations', []))),
            ])->toArray();
        }

        // Catch connection issues
        catch (ConnectionException | RequestException) {
            return [];
        }
    }
}

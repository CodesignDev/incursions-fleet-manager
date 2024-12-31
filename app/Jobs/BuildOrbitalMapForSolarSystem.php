<?php

namespace App\Jobs;

use App\Enums\EveIdRange;
use App\Exceptions\InvalidEveIdRange;
use App\Facades\Esi;
use App\Models\Universe\Celestial;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class BuildOrbitalMapForSolarSystem implements ShouldQueue
{
    use Queueable;

    /**
     * The solar system that we are building the orbit map for.
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
        // If no solar system was provided, exit
        if (blank($this->solarSystemId)) {
            return;
        }

        // If the ID range is invalid, throw an exception and fail
        if (EveIdRange::isValidId($this->solarSystemId, EveIdRange::AllSolarSystems)) {
            throw (new InvalidEveIdRange())->withId($this->solarSystemId, EveIdRange::AllSolarSystems);
        }

        // Get the ids from ESI for the solar system
        $orbitMap = rescue(
            fn () => transform(
                Esi::public()
                    ->withUrlParameters(['system_id' => $this->solarSystemId])
                    ->get('/universe/systems/{system_id}')
                    ->throw()
                    ->fluent(),
                function (Fluent $response): array {
                    $star = $response->value('star_id');
                    $planetData = $response->collect('planets');

                    return [
                        $star => $planetData->keyBy('planet_id')->select(['moons', 'asteroid_belts'])->toArray()
                    ];
                }
            ),
            rescue: []
        );

        // Loop through each entry in the orbit map to process the star -> planet orbit data
        collect($orbitMap)->each(function ($data, $starId) {

            // Clear any properties on the star entry
            $columnSearchKeys = ['orbital_id', 'celestial_index', 'orbital_index'];

            Celestial::query()
                ->whereCelestialId($starId)
                ->where(fn (Builder $query) => $query->whereNotNull($columnSearchKeys, boolean: 'or'))
                ->update(array_fill_keys($columnSearchKeys, null));

            $data = collect($data);

            // Loop through each planet
            $data->keys()->each(function ($planet, $index) use ($starId) {

                // Data to apply to the planet entry
                $planetData = [
                    'orbital_id' => $starId,
                    'celestial_index' => $index + 1,
                ];

                // Update the planet to set the orbital id to the star and also set the celestial index
                Celestial::query()
                    ->whereCelestialId($planet)
                    ->where(fn (Builder $query) => $query
                        ->where(fn (Builder $query) => $query
                            ->whereNot($planetData)
                            ->orWhereNull(array_keys($planetData))
                        )
                        ->whereNull('orbital_index')
                    )
                    ->update([
                        ...$planetData,
                        'orbital_index' => null
                    ]);
            });

            // Process each of the moons and asteroid belts
            $data->values()->flatten()->each(function ($celestial) use ($data) {

                // Get the planet that this celestial orbits
                $planet = $data
                    ->map(fn ($items) => Arr::flatten($items))
                    ->search(fn ($items) => in_array($celestial, $items, true));

                // Get the celestial indexes for both the planet and the current celestial
                $celestialIndex = $this->getOrbitIndexForCelestial($data, $planet);
                $orbitIndex = $this->getOrbitIndexForCelestial($data, $celestial, $planet);

                // Data to apply to the current celestial entry
                $celestialData = [
                    'orbital_id' => $planet,
                    'celestial_index' => $celestialIndex,
                    'orbital_index' => $orbitIndex,
                ];

                // Update the celestial with the relevant data
                Celestial::query()
                    ->whereCelestialId($celestial)
                    ->where(fn (Builder $query) => $query
                        ->whereNot($celestialData)
                        ->orWhereNull(array_keys($celestialData))
                    )
                    ->update($celestialData);
            });

        });
    }

    /**
     * Get the planet id and celestial type for the current celestial.
     */
    private function getCelestialType(Collection $orbitMap, int $celestial): string
    {
        // Merge the celestial groups together (has to be typed due to it failing to understand the return type)
        /** @var \Illuminate\Support\Collection $groups */
        $groups = $orbitMap->reduce(fn (Collection $mergedGroups, $groups) => $mergedGroups->mergeRecursive($groups), new Collection);

        // Find the type of celestial
        $type = Str::singular($groups->search(fn ($items) => in_array($celestial, $items, true)));

        // Check to see if this is a planet
        if (! $type && $orbitMap->keys()->contains($celestial)) {
            $type = 'planet';
        }

        return $type ?: 'unknown';
    }

    /**
     * Get the orbit index for either the celestials orbit round a planet or the planets orbit around the star.
     */
    private function getOrbitIndexForCelestial(Collection $orbitMap, int $celestial, int $planet = null): int
    {
        // Get the type of celestial
        $type = $this->getCelestialType($orbitMap, $celestial);

        // Fail if invalid data was supplied
        if (! $planet && $type === 'unknown') {
            return -1;
        }

        // If the type is a planet, get the index of the planet in the orbit map
        if ($type === 'planet' && filled($planetIndex = $orbitMap->keys()->search($celestial))) {
            return $planetIndex + 1;
        }

        // Get the orbit of the celestial around the planet
        if (in_array($type, ['moon', 'asteroid_belt'])) {
            $entries = data_get($orbitMap, implode('.', [$planet, Str::plural($type)]), []);

            return filled($orbitIndex = array_search($celestial, $entries, true))
                ? $orbitIndex + 1
                : -1;
        }

        return -1;
    }
}

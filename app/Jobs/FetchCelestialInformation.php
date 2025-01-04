<?php

namespace App\Jobs;

use App\Enums\EveIdRange;
use App\Enums\SolarSystemCelestialType;
use App\Exceptions\InvalidEveIdRange;
use App\Jobs\Middleware\HandleSdeErrors;
use App\Models\Universe\Celestial;
use App\Models\Universe\SolarSystem;
use App\Traits\FetchesNamesFromSde;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class FetchCelestialInformation implements ShouldQueue
{
    use Batchable, FetchesNamesFromSde, Queueable;

    /**
     * The id of the celestial that we are fetching the data for.
     */
    protected int $celestialId;

    /**
     * The type of celestial that is being processed.
     *
     * @var \App\Enums\SolarSystemCelestialType
     */
    protected SolarSystemCelestialType $celestialType;

    /**
     * The solar system that this celestial is for.
     */
    protected int $solarSystemId;

    /**
     * The data of all celestials in the solar system, used to link some celestials to others.
     */
    protected array $celestialData;

    /**
     * The solar system name
     */
    protected ?string $solarSystemName = null;

    /**
     * Create a new job instance.
     */
    public function __construct(int $celestialId, SolarSystemCelestialType $celestialType, int $solarSystemId, array $celestialData)
    {
        $this->celestialId = $celestialId;
        $this->celestialType = $celestialType;
        $this->solarSystemId = $solarSystemId;
        $this->celestialData = $celestialData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // If no celestial or solar system id was passed, exit
        if (blank($this->celestialId) || blank($this->solarSystemId)) {
            return;
        }

        // If the ID range is invalid, throw an exception and fail
        if (EveIdRange::isValidId($this->celestialId, EveIdRange::Celestials)) {
            throw (new InvalidEveIdRange())->withId($this->celestialId, EveIdRange::Celestials);
        }

        // The entity group to query based on the celestial type being fetched
        $entityType = match ($this->celestialType) {
            SolarSystemCelestialType::Star => 'Star',
            SolarSystemCelestialType::Planet => 'Planet',
            SolarSystemCelestialType::Moon => 'Moon',
            SolarSystemCelestialType::AsteroidBelt => 'AsteroidBelt',
        };

        // Query the entity from the SDE
        $data = Http::sde()
            ->withUrlParameters(['celestial_group' => Str::camel(Str::pluralStudly($entityType)), 'celestial_id' => $this->celestialId])
            ->get('/universe/{celestial_group}/{celestial_id}')
            ->throw()
            ->fluent();

        // Assert that the system id returned matches what we are querying
        throw_if($data->has('solarSystemID') && $data->value('solarSystemID') !== $this->solarSystemId);

        // Get the name of the entity
        $name = $this->fetchNameFromSde($this->celestialId, function () use ($data, $entityType) {
            $systemName = $this->getSystemName();

            if ($this->celestialType === SolarSystemCelestialType::Star) {
                return "$systemName - Star";
            }

            $planetName = $this->getPlanetName($data->integer('planetID'));
            if ($this->celestialType === SolarSystemCelestialType::Planet) {
                return $planetName;
            }

            return sprintf('%s - Unknown %s', $planetName, Str::headline($entityType));
        });

        // Create or update the actual celestial entity
        tap(Celestial::updateOrCreate(
            [
                'id' => $this->celestialId,
                'celestial_type' => Str::snake($entityType),
                'system_id' => $this->solarSystemId,
            ],
            [
                'name' => $name,
                'type_id' => $data->value('typeID'),
            ]
        ), function (Celestial $celestial) use ($data) {

            // Set position for everything except stars
            if ($this->celestialType !== SolarSystemCelestialType::Star) {
                $celestial->setPosition(...$data->value('position', []));
            }

            // Set some metadata for the celestial
            $meta = collect([
                'spectral_class' => $data->get('statistics.spectralClass'),
                'radius'         => $data->get('statistics.radius'),
                'orbit_radius'   => $data->get('statistics.orbitRadius'),
            ])->filter(fn ($value) => $value !== null && $value !== '0.0');
            $celestial->setManyMeta($meta->toArray());
        });

        // If the celestial data has npc stations listed, create a job to create them
        if ($data->has('npcStations') && Arr::get($this->batch()?->options, 'fetch_npc_stations', false)) {
            $stationJobs = $data->collect('npcStations')->keys()->mapInto(FetchNpcStationInformation::class);

            $this->batch()?->add($stationJobs);
        }
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new HandleSdeErrors];
    }

    /**
     * Get the name of the solar system, either from the database or from the SDE.
     */
    private function getSystemName(): string
    {
        return $this->solarSystemName ??= transform(
           SolarSystem::whereId($this->solarSystemId)->firstOr('name', function () {
               return $this->fetchNameFromSde($this->solarSystemId, sprintf('Unknown System #%d', $this->solarSystemId));
           }),
           fn ($value) => match (true) {
               $value instanceof SolarSystem => $value->name,
               default => $value
           }
       );
    }

    /**
     * Get the name of the planet.
     *
     * The format is always the system name and then the planet's celestia index as roman numerals.
     */
    private function getPlanetName(int $planetId = 0): string
    {
        // If the planet id is blank, use the celestial id
        if ($this->celestialType === SolarSystemCelestialType::Planet && blank($planetId)) {
            $planetId = $this->celestialId;
        }

        // If the planet is still blank, return an empty string
        if (blank($planetId)) {
            return '';
        }

        // Get the index for the planet
        $planetIndex = array_search($planetId, data_get($this->celestialData, 'planets', []), true) + 1;

        return sprintf('%s %s', $this->getSystemName(), Number::toRomanNumeral($planetIndex));
    }
}

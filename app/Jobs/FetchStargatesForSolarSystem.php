<?php

namespace App\Jobs;

use App\Enums\EveIdRange;
use App\Exceptions\InvalidEveIdRange;
use App\Jobs\Middleware\HandleSdeErrors;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

class FetchStargatesForSolarSystem implements ShouldQueue
{
    use Queueable;

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

        // Make a request to the SDE to get the list of stargates that are in this system.
        $stargates = Http::sde()
            ->withUrlParameters(['system_id' => $this->solarSystemId])
            ->get('/universe/solarSystems/{system_id}')
            ->throw()
            ->collect('stargates');

        // Build a list of celestials to fetch and create jobs for them
        Bus::batch(
            $stargates->mapInto(FetchStargateInformation::class)
        )->dispatch();
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new HandleSdeErrors];
    }
}

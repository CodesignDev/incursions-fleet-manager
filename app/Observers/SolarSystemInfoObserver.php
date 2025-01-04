<?php

namespace App\Observers;

use App\Jobs\FetchCelestialsForSolarSystem;
use App\Jobs\FetchStargatesForSolarSystem;
use App\Models\Universe\SolarSystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;

class SolarSystemInfoObserver implements ShouldQueue
{
    /**
     * Handle the SolarSystem "created" event.
     */
    public function created(SolarSystem $solarSystem): void
    {
        // Create the job that would fetch the relevant information for solar system objects
        Bus::batch([
            new FetchCelestialsForSolarSystem($solarSystem->system_id),
            new FetchStargatesForSolarSystem($solarSystem->system_id),
        ])->dispatch();
    }
}

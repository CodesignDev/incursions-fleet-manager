<?php

namespace App\Observers;

use App\Jobs\FetchCelestialsForSolarSystemFromSde;
use App\Jobs\FetchStargatesForSolarSystemFromSde;
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
            new FetchCelestialsForSolarSystemFromSde($solarSystem->system_id),
            new FetchStargatesForSolarSystemFromSde($solarSystem->system_id),
        ])->dispatch();
    }
}

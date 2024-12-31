<?php

namespace App\Observers;

use App\Jobs\FetchCelestialsForSolarSystemFromSde;
use App\Models\Universe\SolarSystem;
use Illuminate\Contracts\Queue\ShouldQueue;

class SolarSystemInfoObserver implements ShouldQueue
{
    /**
     * Handle the SolarSystem "created" event.
     */
    public function created(SolarSystem $solarSystem): void
    {
        // Create the job that would fetch the relevant information for solar system objects
        dispatch(new FetchCelestialsForSolarSystemFromSde($solarSystem->system_id));
    }
}

<?php

namespace App\Observers;

use App\Jobs\StartFleetTracking;
use App\Models\Fleet;

class FleetObserver
{
    /**
     * Handle the Fleet "created" event.
     */
    public function created(Fleet $fleet): void
    {
        // Skip if the fleet is untracked
        if ($fleet->untracked) {
            return;
        }

        // Otherwise start up the fleet tracker
        StartFleetTracking::dispatch($fleet);
    }

    /**
     * Handle the Fleet "updated" event.
     */
    public function updated(Fleet $fleet): void
    {
        // If the untracked field wasn't changed, exit
        if (! $fleet->wasChanged('untracked')) {
            return;
        }

        // If the fleet was changed from untracked to tracked. Start tracking the fleet.
        if ($fleet->getOriginal('untracked') === true && $fleet->getAttribute('untracked') === false) {
            StartFleetTracking::dispatch($fleet);
        }
    }
}

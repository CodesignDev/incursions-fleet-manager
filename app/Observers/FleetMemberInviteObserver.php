<?php

namespace App\Observers;

use App\Jobs\LinkFleetInviteToMember;
use App\Models\FleetMember;

class FleetMemberInviteObserver
{
    /**
     * Handle the FleetMember "created" event.
     */
    public function created(FleetMember $fleetMember): void
    {
        // Dispatch a job that tries to link up a fleet invite to this fleet member
        LinkFleetInviteToMember::dispatch($fleetMember);
    }
}

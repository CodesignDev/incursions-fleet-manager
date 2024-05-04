<?php

namespace App\Observers;

use App\Enums\FleetInviteState;
use App\Models\FleetInvite;

class FleetInviteStateObserver
{
    /**
     * Handle the FleetInvite "saved" event.
     */
    public function saved(FleetInvite $fleetInvite): void
    {
        // If the state is not one that needs timing out
        /** @var FleetInviteState $state */
        $state = $fleetInvite->state;
        if (! in_array($state, [FleetInviteState::PENDING, FleetInviteState::SENT], true)) {
            return;
        }

        // How long until the invite expires
        $inviteTimeoutSeconds = $state === FleetInviteState::SENT
            ? 30
            : config('waitlist.fleet_invite_timeout', 120);

        // Dispatch the job that times out the invite
        $invite = $fleetInvite->withoutRelations();
        dispatch(function () use ($invite) {
            $invite->refresh();
            if (! in_array($invite->state, [FleetInviteState::PENDING, FleetInviteState::SENT], true)) {
                return;
            }

            $invite->update(['state' => FleetInviteState::TIMED_OUT]);
        })->delay(now()->addSeconds($inviteTimeoutSeconds));
    }
}

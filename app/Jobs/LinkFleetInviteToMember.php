<?php

namespace App\Jobs;

use App\Enums\FleetInviteState;
use App\Enums\FleetMemberJoinedVia;
use App\Models\FleetInvite;
use App\Models\FleetMember;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class LinkFleetInviteToMember implements ShouldQueue
{
    use Queueable;

    /**
     * The fleet member that we are finding an invite for.
     */
    protected FleetMember $fleetMember;

    /**
     * Create a new job instance.
     */
    public function __construct(FleetMember $fleetMember)
    {
        $this->fleetMember = $fleetMember->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // If the fleet member already has the joined via filled in, skip
        if (filled($this->fleetMember->joined_via)) {
            return;
        }

        // Try and locate a fleet invite for this character
        $invite = FleetInvite::query()
            ->where('fleet_id', $this->fleetMember->fleet_id)
            ->where('character_id', $this->fleetMember->character_id)
            ->where('state', FleetInviteState::SENT)
            ->when($this->fleetMember->joined_at, function (Builder $query, $joinedAt) {
                $query->whereBetween('invite_sent_at', [
                    Date::parse($joinedAt)->subMinute(),
                    Date::parse($joinedAt)
                ]);
            })
            ->orderBy('invite_sent_at', 'desc')
            ->first();

        // If a record was found, attach it to the fleet member and update the invite. Updates
        // are wrapped in a transaction to ensure updates are done together
        if ($invite) {
            DB::transaction(function () use ($invite) {
                $this->fleetMember->update([
                    'joined_via' => FleetMemberJoinedVia::INVITE,
                    'invite_id' => $invite->id,
                ]);

                $invite->update([
                    'state' => FleetInviteState::ACCEPTED,
                    'accepted_at' => $this->fleetMember->joined_at,
                ]);
            });
            return;
        }

        // If the fleet has an advert, assume the member came from an advert
        // TODO

        // Otherwise mark as joined via an unknown source
        $this->fleetMember->update(['joined_via' => FleetMemberJoinedVia::UNKNOWN]);
    }
}

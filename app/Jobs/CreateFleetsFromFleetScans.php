<?php

namespace App\Jobs;

use App\Models\Fleet;
use App\Models\FleetScan;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateFleetsFromFleetScans implements ShouldQueue
{
    use Queueable;

    /**
     * The user whose fleet scans to process.
     */
    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the list of characters for this user
        $characters = $this->user->characters()->whereWhitelisted()->get();

        // Get the list of fleet scans that are for these characters where the character is the boss of the fleet
        $scans = FleetScan::query()
            ->whereIsFleetBoss()
            ->whereIn('character_id', $characters->pluck('id'))
            ->get();

        // For each scan, convert them into an untracked fleet
        $scans->each(function (FleetScan $scan) {

            // Create a fleet entry and then add the character as the fleet boss
            tap(Fleet::create([
                'esi_fleet_id' => $scan->fleet_id,
                'untracked' => true,
            ]), function (Fleet $fleet) use ($scan) {
                $fleet->assignFleetBoss($scan->character_id);
            });
        });
    }
}

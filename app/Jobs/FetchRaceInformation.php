<?php

namespace App\Jobs;

use App\Jobs\Middleware\HandleSdeErrors;
use App\Models\SDE\Race;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchRaceInformation implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The id of the race to fetch from the SDE.
     */
    protected int $raceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $raceId)
    {
        $this->raceId = $raceId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Exit if the race exists
        if (blank($this->raceId) || Race::whereId($this->raceId)->exists()) {
            return;
        }

        // Fetch the information from the SDE
        $raceInfo = Http::sde()
            ->withUrlParameters(['race_id' => $this->raceId])
            ->get('/universe/races/{race_id}')
            ->throw()
            ->fluent();

        // Fetch the corvette ship id
        $corvetteShipId = $raceInfo->get('shipTypeID');

        // Create the meta group entry
        $race = Race::create([
            'id' => $raceInfo->get('raceID', $this->raceId),
            'corvette_ship_id' => $corvetteShipId,
            'name' => $raceInfo->get('nameID.en'),
            'description' => $raceInfo->get('descriptionID.en'),
        ]);

        // Fetch the info on the corvette ship type if it doesn't exist
        if ($race->corvette()->doesntExist()) {
            dispatch(new FetchInventoryTypeInformation($corvetteShipId));
        }
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new HandleSdeErrors];
    }
}

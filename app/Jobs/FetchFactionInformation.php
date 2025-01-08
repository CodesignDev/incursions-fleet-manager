<?php

namespace App\Jobs;

use App\Jobs\Middleware\HandleSdeErrors;
use App\Models\SDE\Faction;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class FetchFactionInformation implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The id of the faction to fetch from the SDE.
     */
    protected int $factionId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $factionId)
    {
        $this->factionId = $factionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Exit if the faction exists
        if (blank($this->factionId) || Faction::whereId($this->factionId)->exists()) {
            return;
        }

        // Fetch the information from the SDE
        $factionInfo = Http::sde()
            ->withUrlParameters(['faction_id' => $this->factionId])
            ->get('/universe/factions/{faction_id}')
            ->throw()
            ->fluent();

        // Pull out the corporation ids
        $corporation = $factionInfo->get('corporationID');
        $militiaCorporation = $factionInfo->get('militiaCorporationID');

        // Pull out the home system
        $homeSystem = $factionInfo->get('solarSystemID');

        // Create the meta group entry
        /** @var \App\Models\SDE\Faction $faction */
        $faction = tap(Faction::create([
            'id' => $factionInfo->get('factionID', $this->factionId),
            'corporation_id' => $corporation,
            'militia_corporation_id' => $militiaCorporation,
            'home_system_id' => $homeSystem,
            'name' => $factionInfo->get('nameID.en'),
            'short_description' => $factionInfo->get('shortDescriptionID.en'),
            'description' => $factionInfo->get('descriptionID.en'),
            'size_factor' => $factionInfo->float('sizeFactor'),
            'is_unique' => $factionInfo->boolean('uniqueName'),
        ]), function (Faction $faction) use ($factionInfo) {

            // Populate the list of member races
            $faction->races()->sync($factionInfo->collect('memberRaces'));

        });

        // Get the list of known corporations
        $corporationRelations = ['corporation', 'militia'];
        $knownCorporations = collect($faction->load($corporationRelations)->getRelations())
            ->only($corporationRelations)
            ->pluck('id');

        // Fetch the information on the corporations of this faction that are unknown
        $corporations = collect([$corporation, $militiaCorporation])->filter()->diff($knownCorporations);
        if ($corporations->isNotEmpty()) {
            dispatch(new FetchCorporationInformation($corporations->toArray()));
        }

        // Fetch the information for the solar system if required
        if ($faction->system()->doesntExist()) {
            dispatch(new FetchSolarSystemInformation($homeSystem));
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

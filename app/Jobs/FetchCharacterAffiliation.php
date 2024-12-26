<?php

namespace App\Jobs;

use App\Facades\Esi;
use App\Models\Alliance;
use App\Models\Character;
use App\Models\Corporation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;

class FetchCharacterAffiliation implements ShouldQueue
{
    use Queueable;

    /**
     * List of characters that require affiliation data fetching.
     */
    protected array $characters;

    /**
     * Create a new job instance.
     */
    public function __construct(array|string|int $characters)
    {
        $this->characters = Arr::wrap($characters);
    }

    /**
     * Execute the job.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle(): void
    {
        // If there are no characters then exit
        if (blank($this->characters)) {
            return;
        }

        // Get the list of characters to send in the esi request
        $characters = collect($this->characters)
            ->map(fn ($character) => (int) $character);

        // Make the request to ESI for the affiliation for the list of characters
        $affiliation = Esi::public()
            ->post('/characters/affiliation', $characters)
            ->throw()
            ->collect();

        // Pull the corporation and alliance data out to check if we need to pull any
        // down from ESI
        $corporationIds = $affiliation->pluck('corporation_id')->unique();
        $allianceIds = $affiliation->pluck('alliance_id')->filter()->unique();

        // Check corporations
        $knownCorporations = Corporation::whereIn('id', $corporationIds)->get();
        $missingCorporations = $corporationIds->diff($knownCorporations->pluck('id'));
        if ($missingCorporations->isNotEmpty()) {
            FetchCorporationInformation::dispatch($missingCorporations->all());
        }

        // Check alliances
        $knownAlliances = Alliance::whereIn('id', $allianceIds)->get();
        $missingAlliances = $allianceIds->diff($knownAlliances->pluck('id'));
        if ($missingAlliances->isNotEmpty()) {
            FetchAllianceInformation::dispatch($missingAlliances->all());
        }

        // Force set the corporation id for each requested character(s). If the corporation
        // doesn't exist, the relevant job will have been dispatched to fetch this from ESI
        $affiliationData = $affiliation->pluck('corporation_id', 'character_id');
        Character::whereIn('id', $characters)
            ->get()
            ->each(function (Character $character) use ($affiliationData) {
                $corporationId = $affiliationData->get($character->id, -1);
                if ($corporationId === -1) {
                    return;
                }

                $character
                    ->forceFill(['corporation_id' => $corporationId])
                    ->save();
            });
    }
}

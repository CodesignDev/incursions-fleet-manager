<?php

namespace App\Jobs;

use App\Models\AllianceStandings;
use App\Models\BlacklistedCharacters;
use App\Models\Character;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;

class CheckCharacterBlacklistStatus implements ShouldQueue
{
    use Queueable;

    /**
     * The optional list of characters to check the blacklist status for.
     */
    protected array $characters;

    /**
     * Create a new job instance.
     */
    public function __construct(array|int $characters = [])
    {
        $this->characters = Arr::wrap($characters);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the list of characters to query the status for
        /** @var \Illuminate\Database\Eloquent\Collection $charactersToCheck */
        $charactersToCheck = Character::query()
            ->with('corporation')
            ->whereNotNull('user_id')
            ->when(filled($this->characters))
            ->whereIn('id', $this->characters)
            ->get();

        // Get the full list alliance standings list
        $standings = AllianceStandings::all()
            ->pluck('standing', 'contact_id');

        // Get the current blacklisted characters
        $blacklist = BlacklistedCharacters::all();
        $blacklistEntriesToRemove = new Collection();

        // The threshold that is used for determining if a character can be used on fleets.
        $standingsThreshold = config('waitlist.standings_threshold', 10);

        // Loop through each character in the list and check their status
        $charactersToCheck->each(function ($character) use ($standings, $blacklist, $blacklistEntriesToRemove, $standingsThreshold) {

            // Create a list of ids for the entity in alliance -> corp -> character order
            $characterStanding = collect([
                $character->corporation->alliance_id,
                $character->corporation->id,
                $character->id,
            ]);

            // Get the standing for each entity id
            $characterStanding = $characterStanding
                ->mapWithKeys(fn ($id) => [$id => $standings->get($id)]);

            // EVE processes standings based lowest to highest priority with the lowest priority being given to the
            // alliance and the highest priority to the character. So standings against the character take priority
            // over the standings set against the character's corporation and alliance. A corporation's standing also
            // can override the alliance standings and same with character's personal contacts.
            // Since the IDs are listed in priority order, we filter the list to remove nulls and then take the last
            // value with a valid number and use that as the standing
            $standing = $characterStanding->filter()->last();

            // If the standing value is below our threshold then add the character to the blacklist
            if ($standing < $standingsThreshold) {
                $character->addToBlacklist();
            }

            // Otherwise if the character is on the blacklist, remove them
            else if ($blacklist->contains('character_id', $character->id)) {
                $blacklistEntriesToRemove->push($blacklist->firstWhere('character_id', $character->id));
            }
        });

        // If there are any entries to remove, then remove them in
        $blacklistEntriesToRemove->when(
            fn ($collection) => $collection->isNotEmpty(),
            fn ($collection) => $collection->toQuery()->withModelScopes()->delete()
        );
    }
}

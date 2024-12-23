<?php

namespace App\Http\Controllers;

use App\Enums\WaitlistRemovalReason;
use App\Http\Requests\JoinWaitlistRequest;
use App\Models\Waitlist;
use App\Models\WaitlistCharacterEntry;
use App\Models\WaitlistEntry;
use App\Traits\HasWaitlistCharacterInputFormatters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    use HasWaitlistCharacterInputFormatters;

    public function join(JoinWaitlistRequest $request, Waitlist $waitlist): RedirectResponse
    {
        // Create the waitlist entry for the user
        /** @var WaitlistEntry $entry */
        $entry = $waitlist->entries()->firstOrCreate(['user_id' => $request->user()->id]);

        // Get the formatted request data
        $data = $this->formatCharacterInputArray(
            $request->safe()->input('characters', [])
        );

        // Get the list of character related data
        $characters = $data
            ->map(
                fn($entry) => collect(['character_id' => data_get($entry, 'character')])
                    ->unless($waitlist->has_doctrine)
                    ->merge(['requested_ship' => data_get($entry, 'ships')])
                    ->toArray()
            );

        // If the waitlist has a doctrine, pull the ships from the character data
        $ships = collect(
            $waitlist->has_doctrine ? $data->pluck('ships', 'character') : []
        );

        // Create the character entries
        $entry->characterEntries()->createMany($characters->toArray())

            // Apply any doctrine ship relationships
            ->when($ships->isNotEmpty())
            ->each(function (WaitlistCharacterEntry $entry) use ($ships) {
                $characterShips = $ships->get($entry->character_id, []);
                $entry->ships()->attach(array_unique($characterShips, SORT_REGULAR));
            });

        return back()->with('status', 'Joined waitlist.');
    }

    public function leave(Request $request, Waitlist $waitlist): RedirectResponse
    {
        /** @var WaitlistEntry $entry */
        $entry = $waitlist->entries()->firstWhere(['user_id' => $request->user()->id]);
        if (! $entry) {
            return back();
        }

        // Remove the relevant entries
        $entry->remove($request->user(), WaitlistRemovalReason::SELF_REMOVED);

        $entry->characterEntries->each(function ($characterEntry) use ($request) {
            $characterEntry->remove($request->user(), WaitlistRemovalReason::SELF_REMOVED);
            $characterEntry->doctrineShips()->detach();
        });

        return back()->with('status', 'Removed from waitlist');
    }
}

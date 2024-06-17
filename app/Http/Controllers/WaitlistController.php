<?php

namespace App\Http\Controllers;

use App\Enums\WaitlistRemovalReason;
use App\Http\Requests\JoinWaitlistRequest;
use App\Models\Waitlist;
use App\Models\WaitlistEntry;
use App\Traits\HasWaitlistCharacterInputFormatters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    use HasWaitlistCharacterInputFormatters;

    public function joinWaitlist(JoinWaitlistRequest $request, Waitlist $waitlist): RedirectResponse
    {
        // Create the waitlist entry for the user
        /** @var WaitlistEntry $entry */
        $entry = $waitlist->entries()->firstOrCreate(['user_id' => $request->user()->id]);

        // Get the formatted request data
        $characters = $this->formatCharacterInputArray($request->safe()->characters);

        // Create the character entries
        $entry->characterEntries()->createMany(
            $characters
                ->map(fn ($entry) => [
                    'character_id' => $entry['character'],
                    'requested_ship' => $entry['ship'],
                ])
                ->toArray()
        );

        return back()->with('status', 'Joined waitlist.');
    }

    public function leaveWaitlist(Request $request, Waitlist $waitlist): RedirectResponse
    {
        /** @var WaitlistEntry $entry */
        $entry = $waitlist->entries()->firstWhere(['user_id' => $request->user()->id]);
        if (! $entry) {
            return back();
        }

        // Remove the relevant entries
        $entry->remove($request->user(), WaitlistRemovalReason::SELF_REMOVED);
        $entry->characterEntries->each->remove($request->user(), WaitlistRemovalReason::SELF_REMOVED);

        return back()->with('status', 'Removed from waitlist');
    }
}

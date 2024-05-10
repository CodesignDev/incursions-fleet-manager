<?php

namespace App\Http\Controllers;

use App\Enums\WaitlistRemovalReason;
use App\Http\Requests\JoinWaitlistRequest;
use App\Models\Waitlist;
use App\Models\WaitlistEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WaitlistController extends Controller
{
    public function joinWaitlist(JoinWaitlistRequest $request, Waitlist $waitlist): RedirectResponse
    {
        // Create the waitlist entry for the user
        /** @var WaitlistEntry $entry */
        $entry = $waitlist->entries()->firstOrCreate(['user_id' => $request->user()->id]);

        // Get the formatted request data
        $characters = $this->formatCharacterInputArray($request->safe()->characters);

        // Create the character entries
        $entry->characterEntries()->createMany($characters->toArray());

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

        return back()->with('status', 'Removed from waitlist');
    }

    private function formatCharacterInputArray(array $data): Collection
    {
        return collect($data)
            ->map(fn ($entry) => $this->formatCharacterInput($entry))
            ->filter(fn ($entry) => $entry->get('ships', []) || $entry->get('ship', ''));
    }

    private function formatCharacterInput(array $data): Collection
    {
        $requiredKeys = ['character', 'ship', 'ships'];

        $convertToRelation = function ($value, $key) {
            if (strcasecmp($key, 'character') === 0) {
                $key .= '_id';
            }

            return [$key => $value];
        };

        return collect($data)
            ->only($requiredKeys)
            ->mapWithKeys($convertToRelation);
    }
}

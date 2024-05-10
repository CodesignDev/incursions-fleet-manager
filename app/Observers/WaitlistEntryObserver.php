<?php

namespace App\Observers;

use App\Models\Concerns\EntryIsRemovable;
use App\Models\WaitlistEntry;
use Illuminate\Support\Arr;

class WaitlistEntryObserver
{
    /**
     * Handle the WaitlistEntry "removed" event.
     */
    public function removed(WaitlistEntry $waitlistEntry): void
    {
        // If, for some reason, the trait isn't applied on this entry then don't process anything
        // This should never be the case since it is the trait that fires this event, but this is
        // more of a precaution then anything
        if (! in_array(EntryIsRemovable::class, class_uses_recursive($waitlistEntry), true)) {
            return;
        }

        // Get the removal data from the waitlist entry
        $removalAttributes = Arr::only(
            $waitlistEntry->getChanges(),
            ['removed_at', 'removed_by', 'removal_reason']
        );

        // Get all the character entries currently attached to this entry and propagate the
        // removal related attributes to each entry. Just applying the raw attributes won't
        // trigger the events since we are not calling remove() on each entry since we want
        // the same timestamp to be propagated
        $characters = $waitlistEntry->characterEntries()->get();
        $characters->each->update($removalAttributes);
    }
}

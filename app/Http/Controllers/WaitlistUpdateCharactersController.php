<?php

namespace App\Http\Controllers;

use App\Enums\WaitlistRemovalReason;
use App\Enums\WaitlistUpdateCharacterActionType;
use App\Http\Requests\WaitlistUpdateCharactersRequest;
use App\Models\Waitlist;
use App\Models\WaitlistCharacterShipEntry;
use App\Traits\HasWaitlistCharacterInputFormatters;

class WaitlistUpdateCharactersController extends Controller
{
    use HasWaitlistCharacterInputFormatters;

    /**
     * Handle the incoming request.
     */
    public function __invoke(WaitlistUpdateCharactersRequest $request, Waitlist $waitlist)
    {
        // Does the waitlist have a doctrine attached to it
        $hasDoctrine = $waitlist->has_doctrine;

        /** @var \App\Models\WaitlistEntry $entry */
        $entry = $waitlist->entries()->where(['user_id' => $request->user()->id])->firstOrFail();

        $action = $request->enum('action', WaitlistUpdateCharacterActionType::class);
        $validatedData = $request->validated();

        // Format the input data
        $characterData = $this->formatCharacterInput($validatedData);
        $characterId = data_get($characterData, 'character');
        $requestedShips = data_get($characterData, 'ships');

        switch ($action) {
            case WaitlistUpdateCharacterActionType::ADD:
            case WaitlistUpdateCharacterActionType::UPDATE:
                /** @var \App\Models\WaitlistCharacterEntry $characterEntry */
                $characterEntry = $entry->characterEntries()->updateOrCreate(
                    ['character_id' => $characterId],
                    ['requested_ship' => $hasDoctrine ? '' : $requestedShips]
                );
                if ($hasDoctrine && is_array($requestedShips) && count($requestedShips) > 0) {
                    $characterEntry->ships()->sync($requestedShips);
                }
                break;

            case WaitlistUpdateCharacterActionType::REMOVE:
                /** @var \App\Models\WaitlistCharacterEntry $characterEntry */
                $characterEntry = $entry->characterEntries->firstWhere('character_id', $characterId);
                if (is_null($characterEntry)) {
                    break;
                }

                $characterEntry->remove($request->user(), WaitlistRemovalReason::SELF_REMOVED);
                $characterEntry->ships()->detach();

                // Count how many other character entries and remove the overall entry if there isn't any others
                if ($entry->characterEntries->except($characterEntry->getKey())->isEmpty()) {
                    $entry->remove($request->user(), WaitlistRemovalReason::SELF_REMOVED);
                }

                break;
        }

        return back();
    }
}

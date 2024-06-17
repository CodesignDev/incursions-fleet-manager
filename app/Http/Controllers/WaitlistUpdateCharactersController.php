<?php

namespace App\Http\Controllers;

use App\Enums\WaitlistRemovalReason;
use App\Enums\WaitlistUpdateCharacterActionType;
use App\Http\Requests\WaitlistUpdateCharactersRequest;
use App\Models\Waitlist;
use App\Traits\HasWaitlistCharacterInputFormatters;

class WaitlistUpdateCharactersController extends Controller
{
    use HasWaitlistCharacterInputFormatters;

    public function __invoke(WaitlistUpdateCharactersRequest $request, Waitlist $waitlist)
    {
        /** @var \App\Models\WaitlistEntry $entry */
        $entry = $waitlist->entries()->where(['user_id' => $request->user()->id])->firstOrFail();

        $action = $request->enum('action', WaitlistUpdateCharacterActionType::class);
        $validatedData = $request->validated();

        $characterData = $this->formatCharacterInput($validatedData);
        $characterId = data_get($characterData, 'character');
        $requestedShip = data_get($characterData, 'ship');
        $query = ['character_id' => $characterId];

        switch ($action) {
            case WaitlistUpdateCharacterActionType::ADD:
            case WaitlistUpdateCharacterActionType::UPDATE:
                $method = $action === WaitlistUpdateCharacterActionType::UPDATE ? 'updateOrCreate' : 'firstOrCreate';
                $entry->characterEntries()->{$method}($query, ['requested_ship' => $requestedShip]);
                break;

            case WaitlistUpdateCharacterActionType::REMOVE:
                /** @var \App\Models\WaitlistCharacterEntry $characterEntry */
                $characterEntry = $entry->characterEntries->firstWhere('character_id', $characterId);
                optional($characterEntry)->remove($request->user(), WaitlistRemovalReason::SELF_REMOVED);
                break;
        }

        return back();
    }
}

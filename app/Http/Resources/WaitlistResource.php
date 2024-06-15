<?php

namespace App\Http\Resources;

use App\Models\WaitlistCharacterEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class WaitlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            $this->attributes(['id', 'name']),
            $this->whenLoaded('entries', $this->getWaitlistUserEntryData($request)),
        ];
    }

    protected function getWaitlistUserEntryData(Request $request): callable|MissingValue
    {
        if (! $request->routeIs('*.dashboard')) {
            return new MissingValue;
        }

        return function ($entries) {
            $entry = $entries->first(); // This is already constrained in the controller

            return $this->merge([
                'on_waitlist' => filled($entry),
                'characters' => transform(
                    $entry,
                    fn ($entry) => $entry->characterEntries->mapWithKeys(fn ($character) => [
                        $character->character_id => $character->requested_ship
                    ]),
                    new MissingValue),
            ]);
        };
    }
}

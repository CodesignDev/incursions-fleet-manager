<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Collection;
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

        return function (Collection $entries) use ($request) {
            $entry = $entries->firstWhere('user_id', $request->user()->id);
            $entryPosition = $entries->search($entry);

            $onWaitlist = filled($entry);

            return $this->merge([
                'total_entries' => $this->whenCounted('entries'),
                'on_waitlist' => $onWaitlist,
                'queue_position' => $this->when($onWaitlist, $entryPosition + 1),
                'characters' => transform(
                    $entry,
                    fn ($entry) => $entry->characterEntries->mapWithKeys(fn ($character) => [
                        $character->character_id => [
                            'character' => $character->character_id,
                            'ship' => $character->requested_ship,
                        ],
                    ]),
                    new MissingValue
                ),
            ]);
        };
    }
}

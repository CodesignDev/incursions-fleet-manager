<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\WaitlistCharacterEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FleetWaitlistEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn (User $user) => $user->only(['id', 'name'])),
            'characters' => $this->whenLoaded(
                'characterEntries',
                fn ($entries) => $entries
                    ->map(fn (WaitlistCharacterEntry $entry) => [
                        'character' => $entry->character->only(['id', 'name']),
                        'ships' => $this->whenNotNull(
                            $entry->requested_ship,
                            $entry->ships->select(['id', 'name'])
                        ),
                        'note' => $this->when(false, ''),
                    ])
                    ->toArray()
            ),
            'joined_at' => $this->created_at,
        ];
    }
}

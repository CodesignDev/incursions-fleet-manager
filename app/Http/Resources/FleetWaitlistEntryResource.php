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
            'user' => $this->whenLoaded('user', fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
            ]),
            'characters' => $this->whenLoaded(
                'characterEntries',
                fn ($entries) => $entries->mapWithKeys(fn (WaitlistCharacterEntry $character) => [
                    $character->character_id => [
                        'character' => $character->character_id,
                        'ship' => $character->requested_ship,
                    ],
                ])
            ),
            'joined_at' => $this->created_at,
        ];
    }
}

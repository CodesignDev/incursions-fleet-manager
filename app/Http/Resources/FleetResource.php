<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FleetResource extends JsonResource
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
            'tracked' => transform($this->untracked, fn ($value) => !$value, true),
            'fleet_boss' => $this->whenLoaded('boss', fn ($fleetBoss) => [
                'character' => $fleetBoss->name,
                'user' => $this->when($fleetBoss->relationLoaded('user'), $fleetBoss->user->name),
            ]),
            'member_count' => $this->whenCounted('members'),
        ];
    }
}

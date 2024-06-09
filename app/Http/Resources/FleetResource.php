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
            'locations' => $this->whenLoaded('members', fn($members) => collect($members)
                ->groupBy('location_id')
                ->sortByDesc(fn ($members) => $members->map->count())
                ->map(function ($members, $location) {
                    if (blank($location)) {
                        $location = 'unknown';
                    }

                    return [
                        'solar_system_id' => $location ?? 'unknown',
                        'solar_system_name' => 'TBC',
                        'count' => $members->count(),
                    ];
                })
                ->values()),
            'member_count' => $this->whenCounted('members'),
        ];
    }
}

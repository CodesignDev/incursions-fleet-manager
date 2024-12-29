<?php

namespace App\Http\Resources;

use App\Enums\FleetStatus;
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
            'status' => $this->whenNotNull($this->status, FleetStatus::UNKNOWN),
            'tracked' => $this->transform($this->untracked, fn ($value) => !$value, true),
            'fleet_boss' => $this->whenLoaded('boss', fn ($fleetBoss) => [
                'character' => $fleetBoss->name,
                'user' => $this->when($fleetBoss->relationLoaded('user'), $fleetBoss->user->name),
            ]),
            'comms' => $this->whenLoaded('comms', fn ($comms) => [
                'label' => $comms->label,
                'url' => $this->whenNotNull($comms->url, ''),
            ]),
            'locations' => $this->whenLoaded('members', fn ($members) => collect($members)
                ->groupBy('location_id')
                ->sortByDesc(fn ($members) => $members->count())
                ->map(fn($members, $locationId) => [
                    'solar_system_id' => $locationId ?: -1,
                    'solar_system_name' => optional($members->first()->location)->name ?: 'Unknown',
                    'count' => $members->count()
                ])
                ->values()),
            'member_count' => $this->whenCounted('members'),
        ];
    }
}

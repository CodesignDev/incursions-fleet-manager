<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class FleetWaitlistResource extends WaitlistResource
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
            'doctrine' => $this->whenLoaded('doctrine', fn ($doctrine) => $doctrine->only(['id', 'name'])),
            'entries' => $this->whenLoaded('entries', FleetWaitlistEntryResource::collection(...)),
        ];
    }
}

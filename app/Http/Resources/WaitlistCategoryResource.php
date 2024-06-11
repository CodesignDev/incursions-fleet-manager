<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaitlistCategoryResource extends JsonResource
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
            $this->whenLoaded('fleets', fn ($fleets) => $this->merge([
                'fleets' => FleetResource::collection($fleets)
            ])),
            $this->whenLoaded('waitlists', fn ($waitlists) => $this->merge([
                'waitlists' => WaitlistResource::collection($waitlists),
            ]))
        ];
    }
}

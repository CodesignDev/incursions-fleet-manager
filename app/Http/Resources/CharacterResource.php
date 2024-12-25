<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class CharacterResource extends JsonResource
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

            $this->whenLoaded('corporation', fn ($corporation) => $this->merge(
                collect(['corporation' => $corporation])
                    ->when(
                        $corporation->relationLoaded('alliance'),
                        fn ($data) => $data->merge('alliance', $corporation->alliance)
                    )
                    ->when(
                        $this->resource->relationLoaded('alliance'),
                        fn ($data) => $data->merge('alliance', $this->resource->alliance)
                    )
            )),
        ];
    }
}

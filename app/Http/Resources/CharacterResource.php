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

            $this->mergeWhen($this->resource->relationLoaded('corporation'), function () {
                $corporation = $this->resource->corporation;
                return collect(['corporation' => $corporation->name])
                    ->when(
                        $corporation->relationLoaded('alliance') || $this->resource->relationLoaded('alliance'),
                        function ($items) use ($corporation) {
                            $alliance = $corporation->getRelation('alliance') ?? $this->resource->getRelation('alliance');

                            return $items->merge(['alliance' => optional($alliance)->name]);
                        }
                    )
                    ->filter();
            }),
        ];
    }
}

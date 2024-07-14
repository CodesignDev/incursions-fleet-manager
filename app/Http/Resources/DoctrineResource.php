<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class DoctrineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Collate all ship groups together
        $groups = $this->whenLoaded(
            'groups',
            fn ($groups) => $groups->map(fn ($group) => [
                'name' => $group->name,
                'order' => $group->display_order,
                'ships' => $group->ships->select('id', 'name'),
            ]),
            []
        );

        // Collate all ships together
        $allShips = $this->whenLoaded('ships', fn ($ships) => $ships->select('id', 'name'), []);

        // Filter the list of all ships down to ones that are not attached to a group
        $groupedShipIds = collect($groups)->flatMap(fn ($group) => Arr::pluck($group['ships'], 'id'));
        $ungroupedShips = collect($allShips)->reject(fn ($ship) => $groupedShipIds->contains($ship['id']));

        return [
            $this->attributes(['id', 'name']),
            'ships' => collect($groups)->concat($ungroupedShips->toArray()),
        ];
    }
}

<?php

namespace App\Jobs;

use App\Jobs\Middleware\HandleSdeErrors;
use App\Models\SDE\InventoryCategory;
use App\Models\SDE\InventoryGroup;
use App\Models\SDE\InventoryType;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

class FetchInventoryTypeInformation implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The id of the type to fetch information for.
     */
    protected int $typeId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $typeId)
    {
        $this->typeId = $typeId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // If no type id was provided, skip
        if (blank($this->typeId)) {
            return;
        }

        // Fetch the type information and name from the SDE
        $typeInfo = Http::sde()
            ->withUrlParameters(['type_id' => $this->typeId])
            ->get('/universe/types/{type_id}')
            ->throw()
            ->fluent();

        // Fetch the packaged volume for the type
        $typePackagedVolume = rescue(fn () => Http::sde()
            ->withUrlParameters(['type_id' => $this->typeId])
            ->get('/universe/repackagedVolumes/{type_id}')
            ->throw()
            ->object(), 0);

        // Pull some of the relationship values
        $group = $typeInfo->get('groupID');
        $category = null;
        $metaGroup = $typeInfo->get('metaGroupID');
        $marketGroup = $typeInfo->get('marketGroupID');
        $faction = $typeInfo->get('factionID');
        $race = $typeInfo->get('raceID');

        // Create the entry for the stargate
        /** @var \App\Models\SDE\InventoryType $inventoryType */
        $inventoryType = tap(InventoryType::updateOrCreate(
            [
                'id' => $typeInfo->get('typeID', $this->typeId),
            ],
            [
                'group_id' => $group,
                'meta_group_id' => $metaGroup,
                'market_group_id' => $marketGroup,
                'faction_id' => $faction,
                'race_id' => $race,

                'name' => $typeInfo->get('name.en', 'Type #'.$this->typeId),
                'description' => $typeInfo->get('description.en'),
                'published' => $typeInfo->get('published', true),
                'volume' => $typeInfo->get('volume', 1),
                'packaged_volume' => $typePackagedVolume,
            ]
        ), function (InventoryType $type) use ($typeInfo) {


        });

        // Check to see if we need to fetch the inventory group
        if ($group && InventoryGroup::whereId($group)->doesntExist()) {

            // Fetch the information fom the SDE for the group
            $groupInfo = Http::sde()
                ->withUrlParameters(['group_id' => $group])
                ->get('/universe/groups/{group_id}')
                ->throw()
                ->fluent();

            // Category of the group
            $category = $groupInfo->get('categoryID');

            InventoryGroup::updateOrCreate([
                'id' => $groupInfo->get('groupID'),
            ], [
                'category_id' => $category,
                'name' => $groupInfo->get('name.en'),
                'published' => $groupInfo->get('published'),
            ]);
        }

        // Create the inventory category if we need to
        if ($category && InventoryCategory::whereId($category)->doesntExist()) {

            // Fetch the information fom the SDE for the category
            $categoryInfo = Http::sde()
                ->withUrlParameters(['category_id' => $category])
                ->get('/universe/categories/{category_id}')
                ->throw()
                ->fluent();

            InventoryCategory::updateOrCreate([
                'id' => $categoryInfo->get('categoryID'),
            ], [
                'name' => $categoryInfo->get('name.en'),
                'published' => $categoryInfo->get('published'),
            ]);
        }

        // Dispatch some jobs based on the info from the type
        $jobs = collect()
            ->when(
                $metaGroup && $inventoryType->metaGroup()->doesntExist(),
                fn ($jobs) => $jobs->push(new FetchMetaGroupInformation($metaGroup))
            )
            ->when(
                $marketGroup && $inventoryType->marketGroup()->doesntExist(),
                fn ($jobs) => $jobs->push(new FetchMarketGroupInformation($marketGroup))
            )
            ->when(
                $faction && $inventoryType->faction()->doesntExist(),
                fn ($jobs) => $jobs->push(new FetchFactionInformation($faction))
            )
            ->when(
                $race && $inventoryType->race()->doesntExist(),
                fn ($jobs) => $jobs->push(new FetchRaceInformation($race))
            )
            ->push([
                new FetchInventoryTypeVariationInformation($this->typeId),
            ])
            ->filter();

        // Dispatch the jobs to the queue
        Bus::batch($jobs)->dispatchIf($jobs->isNotEmpty());
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new HandleSdeErrors];
    }
}

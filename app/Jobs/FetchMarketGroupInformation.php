<?php

namespace App\Jobs;

use App\Jobs\Middleware\HandleSdeErrors;
use App\Models\SDE\MarketGroup;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Fluent;

class FetchMarketGroupInformation implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * The id of the market group to fetch from the SDE.
     */
    protected int $marketGroupId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $marketGroupId)
    {
        $this->marketGroupId = $marketGroupId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Exit if the market group already exists
        if (blank($this->marketGroupId) || MarketGroup::whereId($this->marketGroupId)->exists()) {
            return;
        }

        $marketGroupId = $this->marketGroupId;

        // List of market groups that need to be created
        /** @var \Illuminate\Support\Collection<int, \Illuminate\Support\Fluent> $marketGroups */
        $marketGroups = collect();

        // Loop through to fetch the market group and its parent group until we've hit the root
        while (filleD($marketGroupId)) {

            // Fetch the required information from the SDE
            $marketGroupInfo = Http::sde()
                ->withUrlParameters(['market_group_id' => $marketGroupId])
                ->get('/markets/groups/{market_group_id}')
                ->throw()
                ->fluent();

            // Fetch the parent market group from the data
            $marketGroupId = $marketGroupInfo->value('parentGroupID');

            // Add the data into the list of groups
            $marketGroups->push($marketGroupInfo);
        }

        $marketGroups = $marketGroups
            ->keyBy(fn ($marketGroup) => $marketGroup->get('marketGroupID'))
            ->pipe(function (Collection $collection) {
                $groupIds = $collection->keys();

                $existingGroups = MarketGroup::whereIn('id', $groupIds)->pluck('id');

                return $collection->except($existingGroups);
            });

        // Loop through each of the market groups in reverse order and add them to the database
        $marketGroups->reverse()->each(function (Fluent $marketGroup) {
            MarketGroup::create([
                'id' => $marketGroup->get('marketGroupID'),
                'parent_id' => $marketGroup->get('parentGroupID'),
                'name' => $marketGroup->get('nameID.en'),
                'description' => $marketGroup->get('descriptionID.en'),
            ]);
        });
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new HandleSdeErrors];
    }
}

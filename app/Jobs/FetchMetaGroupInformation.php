<?php

namespace App\Jobs;

use App\Jobs\Middleware\HandleSdeErrors;
use App\Models\SDE\MetaGroup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchMetaGroupInformation implements ShouldQueue
{
    use Queueable;

    /**
     * The id of the meta group to fetch from the SDE.
     */
    protected int $metaGroupId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $metaGroupId)
    {
        $this->metaGroupId = $metaGroupId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Exit if the meta group exists
        if (blank($this->metaGroupId) || MetaGroup::whereId($this->metaGroupId)->exists()) {
            return;
        }

        // Fetch the information from the SDE
        $metaGroupInfo = Http::sde()
            ->withUrlParameters(['meta_group_id' => $this->metaGroupId])
            ->get('/universe/metaGroups/{meta_group_id}')
            ->throw()
            ->fluent();

        // Create the meta group entry
        MetaGroup::create([
            'id' => $metaGroupInfo->get('metaGroupID', $this->metaGroupId),
            'name' => $metaGroupInfo->get('nameID.en'),
            'description' => $metaGroupInfo->get('descriptionID.en'),
        ]);
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new HandleSdeErrors];
    }
}

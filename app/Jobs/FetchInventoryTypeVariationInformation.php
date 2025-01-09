<?php

namespace App\Jobs;

use App\Jobs\Middleware\HandleSdeErrors;
use App\Models\SDE\InventoryType;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Fluent;

class FetchInventoryTypeVariationInformation implements ShouldQueue
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

        // Fetch the variant information and name from the SDE
        $typeVariationsInfo = rescue(
            fn () => Http::sde()
                ->withUrlParameters(['type_id' => $this->typeId])
                ->get('/universe/typeVariations/{type_id}')
                ->throw()
                ->fluent(),
            rescue: new Fluent
        );

        // If there is no base type, just exit
        if ($typeVariationsInfo->isNotFilled('base')) {
            return;
        }

        // Get the base type and list of variations
        $baseType = $typeVariationsInfo->get('base');
        $variations = $typeVariationsInfo->collect('variations');

        // If the requested type is not in the list of variations, then exit
        if ($variations->contains($this->typeId)) {
            return;
        }

        // Fetch the information on the base type (if the current type isn't the base type)
        if ($this->typeId !== $baseType) {
            dispatch(new FetchInventoryTypeInformation($baseType));
        }

        // Get the current type
        /** @var InventoryType $type */
        $type = InventoryType::firstWhere('id', $this->typeId);

        // If no type is present, skip
        if (is_null($type)) {
            return;
        }

        // Populate the variation data for this type
        $type->variation()->create([
            'base_type_id' => $baseType,
            'meta_group_id' => $type->meta_group_id,
            // 'meta_level' TODO: To be added, once dogma information has been fetched for this type
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

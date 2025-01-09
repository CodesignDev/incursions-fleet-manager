<?php

namespace App\Models\SDE\Concerns;

use App\Models\SDE\InventoryTypeVariation;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

trait HasVariations
{
    use HasRelationships;

    /**
     * The variants for this model.
     */
    public function variants(): HasManyDeep
    {
        return $this->hasManyDeep(
            static::class,
            [InventoryTypeVariation::class.' as type_variation_link', InventoryTypeVariation::class],
            ['type_id', 'base_type_id', 'id'],
            [null, 'base_type_id', 'type_id']
        );
    }

    /**
     * The variations for this model.
     */
    public function variations(): HasManyDeep
    {
        return $this->hasManyDeep(
            InventoryTypeVariation::class,
            [InventoryTypeVariation::class.' as type_variation_link'],
            ['type_id', 'base_type_id'],
            [null, 'base_type_id']
        );
    }

    /**
     * The variation entry for this model.
     */
    public function variation(): HasOne
    {
        return $this->hasOne(InventoryTypeVariation::class, 'type_id');
    }
}

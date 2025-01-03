<?php

namespace App\Models\SDE\Inventory;

use App\Models\Concerns\HasPrefixedKey;
use App\Models\Concerns\IsSdeModel;
use App\Models\SDE\Concerns\HasPublishedFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Znck\Eloquent\Relations\BelongsToThrough as BelongsToThroughRelation;
use Znck\Eloquent\Traits\BelongsToThrough;

class InventoryType extends Model
{
    use BelongsToThrough, HasPrefixedKey, HasPublishedFlag, IsSdeModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type_id',
        'group_id',
        'meta_group_id',
        'market_group_id',
        'faction_id',
        'race_id',
        'name',
        'description',
        'mass',
        'volume',
        'packaged_volume',
        'capacity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'mass' => 'float',
            'volume' => 'float',
            'packaged_volume' => 'float',
        ];
    }

    /**
     * Get the meta attributes that should be cast.
     *
     * @return array<string, string>
     * @noinspection PhpUnused
     */
    protected function metaCasts(): array
    {
        return [
            'use_base_price' => 'boolean',
            'anchored' => 'boolean',
            'anchorable' => 'boolean',
            'fittable_non_singleton' => 'boolean',
        ];
    }

    /**
     * The group this type belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(InventoryGroup::class, 'group_id');
    }

    /**
     * The category of this type.
     */
    public function category(): BelongsToThroughRelation
    {
        return $this->belongsToThrough(
            InventoryCategory::class,
            InventoryGroup::class,
            foreignKeyLookup: [
                InventoryCategory::class => 'category_id',
                InventoryGroup::class => 'group_id',
            ]
        );
    }
}

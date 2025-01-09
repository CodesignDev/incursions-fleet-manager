<?php

namespace App\Models\SDE;

use App\Models\Concerns\IsSdeModel;
use App\Models\SDE\Concerns\HasPublishedFlag;
use App\Models\SDE\Concerns\HasVariations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Znck\Eloquent\Relations\BelongsToThrough as BelongsToThroughRelation;
use Znck\Eloquent\Traits\BelongsToThrough;

class InventoryType extends Model
{
    use BelongsToThrough, HasPublishedFlag, HasVariations, IsSdeModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
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

    /**
     * The meta group this type belongs in.
     */
    public function metaGroup(): BelongsTo
    {
        return $this->belongsTo(MetaGroup::class, 'meta_group_id');
    }

    /**
     * The market group that this type is listed in.
     */
    public function marketGroup(): BelongsTo
    {
        return $this->belongsTo(MarketGroup::class, 'market_group_id');
    }

    /**
     * The faction that this type is a part of.
     */
    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class, 'faction_id');
    }

    /**
     * The race that this type is a prt of.
     */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class, 'race_id');
    }
}

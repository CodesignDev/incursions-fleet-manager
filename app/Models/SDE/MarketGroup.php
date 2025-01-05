<?php

namespace App\Models\SDE;

use App\Models\Concerns\IsSdeModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\HasManyOfDescendants;

class MarketGroup extends Model
{
    use HasRecursiveRelationships, IsSdeModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'parent_id',
        'name',
        'description',
    ];

    /**
     * The types that are part of this market group.
     */
    public function types(): HasMany
    {
        return $this->hasMany(InventoryType::class, 'market_group_id');
    }

    /**
     * All types that are part of this market group of its descendants.
     */
    public function allTypes(): HasManyOfDescendants
    {
        return $this->hasManyOfDescendantsAndSelf(InventoryType::class, 'market_group_id');
    }
}

<?php

namespace App\Models\SDE\Inventory;

use App\Models\Concerns\HasPrefixedKey;
use App\Models\Concerns\IsSdeModel;
use App\Models\SDE\Concerns\HasPublishedFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    use HasPrefixedKey, HasPublishedFlag, IsSdeModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
    ];

    /**
     * The type groups that are part of this category.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(InventoryGroup::class, 'category_id');
    }
}

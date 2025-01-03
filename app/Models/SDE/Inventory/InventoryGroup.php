<?php

namespace App\Models\SDE\Inventory;

use App\Models\Concerns\HasPrefixedKey;
use App\Models\Concerns\IsSdeModel;
use App\Models\SDE\Concerns\HasPublishedFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plank\Metable\Metable;

class InventoryGroup extends Model
{
    use HasPrefixedKey, HasPublishedFlag, IsSdeModel, Metable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'category_id',
        'name',
    ];

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
     * The types that are in this group.
     */
    public function types(): HasMany
    {
        return $this->hasMany(InventoryType::class, 'group_id');
    }

    /**
     * The category of this group.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }
}

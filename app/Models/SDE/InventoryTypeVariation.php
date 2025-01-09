<?php

namespace App\Models\SDE;

use App\Models\Concerns\IsSdeModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class InventoryTypeVariation extends Model
{
    use HasTableAlias, IsSdeModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type_id',
        'base_type_id',
        'meta_group_id',
        'meta_level',
    ];

    /**
     * Apply the required global scopes to the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('order_variants', function (Builder $builder) {
            $builder
                ->orderBy('meta_group_id')
                ->orderByRaw('CASE WHEN meta_level IS NULL THEN 0 ELSE 1 END asc')
                ->orderBy('meta_level');
        });
    }
}

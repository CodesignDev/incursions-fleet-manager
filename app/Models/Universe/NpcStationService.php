<?php

namespace App\Models\Universe;

use App\Models\Concerns\IsSdeUniverseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class NpcStationService extends Model
{
    use HasRelationships, IsSdeUniverseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'description',
    ];

    /**
     * The stations that have this service.
     */
    public function stations(): HasManyDeep
    {
        return $this->hasManyDeep(
            NpcStation::class,
            [NpcStationOperationServices::class, NpcStationOperation::class],
            ['service_id', 'id', 'operation_id'],
            [null, 'operation_id', null]
        );
    }

    /**
     * The station operations that run this service.
     */
    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(
            NpcStationOperation::class,
            NpcStationOperationServices::class,
            foreignPivotKey: 'service_id',
            relatedPivotKey: 'operation_id',
        )->withTimestamps();
    }
}

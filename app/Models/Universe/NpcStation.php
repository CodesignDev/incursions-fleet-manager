<?php

namespace App\Models\Universe;

use App\Models\Concerns\HasInventoryType;
use App\Models\Concerns\IsSdeUniverseModel;
use App\Models\Corporation;
use App\Models\Universe\Concerns\HasPositionalData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plank\Metable\Metable;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class NpcStation extends Model
{
    use HasInventoryType, HasPositionalData, HasRelationships, IsSdeUniverseModel, Metable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'system_id',
        'type_id',
        'operation_id',
        'corporation_id',
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
            'office_rental_cost' => 'integer',
        ];
    }

    /**
     * The solar system that this station is in.
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class, 'system_id');
    }

    /**
     * The corporation that owns this station.
     */
    public function corporation(): BelongsTo
    {
        return $this->belongsTo(Corporation::class);
    }

    /**
     * The operation this station performs.
     */
    public function operation(): BelongsTo
    {
        return $this->belongsTo(NpcStationOperation::class);
    }

    /**
     * The services that this npc station runs.
     */
    public function services(): HasManyDeep
    {
        return $this->hasManyDeep(
            NpcStationService::class,
            [NpcStationOperation::class, NpcStationOperationServices::class],
            ['operation_id', 'operation_id', 'service_id'],
            ['operation_id', 'operation_id', 'service_id']
        );
    }
}

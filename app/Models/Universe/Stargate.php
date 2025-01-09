<?php

namespace App\Models\Universe;

use App\Models\Concerns\HasInventoryType;
use App\Models\Concerns\IsSdeUniverseModel;
use App\Models\Universe\Concerns\HasPositionalData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class Stargate extends Model
{
    use HasInventoryType, HasPositionalData, HasTableAlias, IsSdeUniverseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'system_id',
        'type_id',
        'name',
    ];

    /**
     * The system that this stargate is in.
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class);
    }

    /**
     * The destination stargate that this one is linked to.
     */
    public function destination(): HasOneThrough
    {
        return $this->hasOneThrough(
            self::class,
            StargateConnection::class,
            firstKey: 'source_stargate_id',
            secondKey: 'id',
            localKey: 'id',
            secondLocalKey: 'destination_stargate_id'
        );
    }

    /**
     * The stargate connection.
     */
    public function connection(): HasOne
    {
        return $this->hasOne(StargateConnection::class, 'source_stargate_id');
    }
}

<?php

namespace App\Models\Universe;

use App\Models\Concerns\IsSdeUniverseModel;
use App\Models\Universe\Concerns\HasPositionalData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Znck\Eloquent\Relations\BelongsToThrough as BelongsToThroughRelation;
use Znck\Eloquent\Traits\BelongsToThrough;

class SolarSystem extends Model
{
    use BelongsToThrough, HasPositionalData, HasRelationships, IsSdeUniverseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'system_id',
        'constellation_id',
        'name',
        'security',
        'radius',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'security' => 'float',
        ];
    }

    /**
     * The constellation this system is a part of.
     */
    public function constellation(): BelongsTo
    {
        return $this->belongsTo(Constellation::class);
    }

    /**
     * The region this system is a part of.
     */
    public function region(): BelongsToThroughRelation
    {
        return $this->belongsToThrough(
            Region::class,
            Constellation::class,
            foreignKeyLookup: [
                Region::class => 'region_id',
                Constellation::class => 'constellation_id',
            ]
        );
    }

    /**
     * The stargates that are in this solar system.
     */
    public function stargates(): HasMany
    {
        return $this->hasMany(Stargate::class, 'system_id');
    }

    /**
     * The system connections that are connected to this solar system through stargates.
     */
    public function connections(): HasManyDeep
    {
        return $this->hasManyDeep(
            self::class,
            [Stargate::class.' as source_stargate', StargateConnection::class, Stargate::class.' as destination_stargate'],
            ['system_id', 'source_stargate_id', 'id', 'id'],
            ['id', 'id', 'destination_stargate_id', 'system_id']
        );
    }

    /**
     * The star of the solar system.
     */
    public function star(): HasOne
    {
        return $this->hasOne(Star::class, 'system_id');
    }

    /**
     * The list of planets that are part of this solar system.
     */
    public function planets(): HasMany
    {
        return $this->hasMany(Planet::class, 'system_id')
            ->orderByPlanet();
    }

    /**
     * The list of moons that are part of this solar system.
     */
    public function moons(): HasMany
    {
        return $this->hasMany(Moon::class, 'system_id')
            ->orderByCelestial();
    }

    /**
     * The list of asteroid belts that are part of this solar system.
     */
    public function asteroidBelts(): HasMany
    {
        return $this->hasMany(AsteroidBelt::class, 'system_id')
            ->orderByCelestial();
    }

    /**
     * The list of asteroid belts that are part of this solar system. (Alias)
     */
    public function belts(): HasMany
    {
        return $this->asteroidBelts();
    }
}

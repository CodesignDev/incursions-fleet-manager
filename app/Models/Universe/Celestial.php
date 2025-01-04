<?php

namespace App\Models\Universe;

use App\Models\Concerns\HasInventoryType;
use App\Models\Concerns\IsSdeUniverseModel;
use App\Models\Universe\Concerns\HasPositionalData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parental\HasChildren;
use Plank\Metable\Metable;

class Celestial extends Model
{
    use HasChildren, HasInventoryType, HasPositionalData, IsSdeUniverseModel, Metable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'system_id',
        'orbital_id',
        'type_id',
        'celestial_type',
        'name',
        'celestial_index',
        'orbital_index',
    ];

    /**
     * The name of the inheritance column.
     *
     * @var string
     *
     * @noinspection PhpUnused
     */
    protected string $childColumn = 'celestial_type';

    /**
     * Get the meta attributes that should be cast.
     *
     * @return array<string, string>
     * @noinspection PhpUnused
     */
    protected function metaCasts(): array
    {
        return [
            'radius' => 'integer',
            'orbital_radius' => 'integer',
        ];
    }

    /**
     * The mappings for the child classes.
     *
     * @noinspection PhpUnused
     */
    protected function childTypes(): array
    {
        return [
            'star' => Star::class,
            'planet' => Planet::class,
            'moon' => Moon::class,
            'asteroid_belt' => AsteroidBelt::class,
        ];
    }

    /**
     * Scope a query to sort by the celestial index.
     */
    public function scopeOrderByPlanet(Builder $builder, string $direction = 'asc'): void
    {
        $builder->orderBy('celestial_index', $direction);
    }

    /**
     * Scope a query to sort by the celestial index and the orbital index.
     */
    public function scopeOrderByCelestial(Builder $builder, string $direction = 'asc'): void
    {
        $builder->orderByPlanet($direction)->orderBy('orbital_index', $direction);
    }

    /**
     * The parent system that this celestial is a part of.
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class);
    }

    /**
     * The underlying item type of the celestial.
     */
    // TODO
}

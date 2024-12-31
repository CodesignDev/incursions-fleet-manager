<?php

namespace App\Models\Universe;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Parental\HasParent;

class Planet extends Celestial
{
    use HasParent;

    /**
     * The moons that are linked to this planet.
     */
    public function moons(): HasMany
    {
        return $this->hasMany(Moon::class, 'orbital_id', 'celestial_id');
    }

    /**
     * The asteroid belts that are linked to this planet.
     */
    public function asteroidBelts(): HasMany
    {
        return $this->hasMany(AsteroidBelt::class, 'orbital_id', 'celestial_id');
    }

    /**
     * Alias for the asteroid belt relationship.
     */
    public function belts(): HasMany
    {
        return $this->asteroidBelts();
    }
}

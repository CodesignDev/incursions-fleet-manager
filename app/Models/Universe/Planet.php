<?php

namespace App\Models\Universe;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Parental\HasParent;

class Planet extends Celestial
{
    use HasParent;

    /**
     * The moons that orbit this planet.
     */
    public function moons(): HasMany
    {
        return $this->hasMany(Moon::class, 'orbital_id', 'celestial_id')
            ->orderBy('orbital_index');
    }

    /**
     * The asteroid belts that orbit this planet.
     */
    public function asteroidBelts(): HasMany
    {
        return $this->hasMany(AsteroidBelt::class, 'orbital_id', 'celestial_id')
            ->orderBy('orbital_index');
    }

    /**
     * The asteroid belts that orbit this planet. (Alias)
     */
    public function belts(): HasMany
    {
        return $this->asteroidBelts();
    }
}

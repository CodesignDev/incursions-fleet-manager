<?php

namespace App\Models\Universe\Concerns;

use App\Models\Universe\Planet;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait OrbitsPlanet
{
    /**
     * The parent system that this entity orbits.
     */
    public function planet(): BelongsTo
    {
        return $this->belongsTo(Planet::class, 'orbital_id', 'celestial_id');
    }
}

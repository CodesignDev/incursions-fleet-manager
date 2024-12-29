<?php

namespace App\Models\Universe;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Region extends Model
{
    //

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'region_id',
        'name',
    ];

    /**
     * The constellations that are a part of this region.
     */
    public function constellations(): HasMany
    {
        return $this->hasMany(Constellation::class);
    }

    /**
     * The systems that are a part of this region.
     */
    public function systems(): HasManyThrough
    {
        return $this
            ->through($this->constellations())
            ->has(fn ($constellation) => $constellation->systems());
    }
}

<?php

namespace App\Models\Universe;

use App\Models\Concerns\IsSdeUniverseModel;
use App\Models\Universe\Concerns\HasPositionalData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Region extends Model
{
    use HasPositionalData, IsSdeUniverseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
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

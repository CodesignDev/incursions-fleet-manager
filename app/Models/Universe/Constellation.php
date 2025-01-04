<?php

namespace App\Models\Universe;

use App\Models\Concerns\IsSdeUniverseModel;
use App\Models\Universe\Concerns\HasPositionalData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Constellation extends Model
{
    use HasPositionalData, IsSdeUniverseModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'region_id',
        'name',
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
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * The systems that are a part of this constellation.
     */
    public function systems(): HasMany
    {
        return $this->hasMany(SolarSystem::class);
    }
}

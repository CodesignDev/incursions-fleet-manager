<?php

namespace App\Models\SDE;

use App\Models\Concerns\IsSdeModel;
use App\Models\Corporation;
use App\Models\Universe\SolarSystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Faction extends Model
{
    use IsSdeModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'corporation_id',
        'militia_corporation_id',
        'home_system_id',
        'name',
        'short_description',
        'description',
        'size_factor',
        'is_unique',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'size_factor' => 'float',
            'is_unique' => 'boolean',
        ];
    }

    /**
     * The list of races that are a member of this faction.
     */
    public function races(): BelongsToMany
    {
        return $this->belongsToMany(
            Race::class,
            FactionMemberRaces::class,
            foreignPivotKey: 'faction_id',
            relatedPivotKey: 'race_id',
        )->withTimestamps();
    }

    /**
     * The corporation that represents this faction.
     */
    public function corporation(): BelongsTo
    {
        return $this->belongsTo(Corporation::class, 'corporation_id');
    }

    /**
     * The corporation that represents this faction's militia.
     */
    public function militia(): BelongsTo
    {
        return $this->belongsTo(Corporation::class, 'militia_corporation_id');
    }

    /**
     * The system that is the faction's home system.
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class, 'home_system_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetScan extends Model
{
    /** @use HasFactory<\Database\Factories\FleetScanFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'character_id',
        'fleet_id',
        'fleet_boss_id',
    ];

    /**
     * The character this fleet scan belongs to.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Whether the current character is the fleet boss.
     */
    public function isFleetBoss(): Attribute
    {
        return Attribute::get(
            fn () => $this->character_id === $this->fleet_boss_id
        );
    }
}

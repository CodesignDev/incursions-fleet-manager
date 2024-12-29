<?php

namespace App\Models;

use App\Enums\FleetMemberJoinedVia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetMember extends Model
{
    use HasUuids, SoftDeletes;

    public const DELETED_AT = 'left_at';

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'location',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'fleet_boss' => false,
        'exempt_from_fleet_warp' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'character_id',
        'location_id',
        'ship_id',
        'fleet_boss',
        'exempt_from_fleet_warp',
        'joined_via',
        'invite_id',
        'joined_at',
    ];

    /**
     * Get the attributes that should be cast.
     */
    public function casts(): array
    {
        return [
            'fleet_boss' => 'boolean',
            'exempt_from_fleet_warp' => 'boolean',
            'joined_via' => FleetMemberJoinedVia::class,
            'joined_at' => 'datetime',
        ];
    }

    /**
     * The fleet this member is a part of.
     */
    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    /**
     * The character that is in fleet.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * The system which this fleet member is currently at.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Universe\SolarSystem::class, 'location_id');
    }
}

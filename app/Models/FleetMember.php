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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'character_id',
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
}

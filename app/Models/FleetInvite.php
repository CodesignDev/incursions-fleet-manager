<?php

namespace App\Models;

use App\Enums\FleetInviteState;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetInvite extends Model
{
    use HasUuids, SoftDeletes;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'state' => FleetInviteState::PENDING,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'character_id',
        'invited_by_id',
        'state',
        'invite_sent_at',
        'accepted_at',
    ];

    /**
     * Get the attributes that should be cast.
     */
    public function casts(): array
    {
        return [
            'state' => FleetInviteState::class,
            'invite_sent_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * The fleet this invite is for.
     */
    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    /**
     * The character that this invite is being sent to.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * The user who invited the character.
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }
}

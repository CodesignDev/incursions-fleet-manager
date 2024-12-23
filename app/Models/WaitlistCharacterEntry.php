<?php

namespace App\Models;

use App\Models\Concerns\EntryIsRemovable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Znck\Eloquent\Relations\BelongsToThrough as BelongsToThroughRelation;
use Znck\Eloquent\Traits\BelongsToThrough;

class WaitlistCharacterEntry extends Model
{
    use BelongsToThrough, EntryIsRemovable, HasFactory, HasUuids;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'character',
        'ships',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'character_id',
        'requested_ship',
    ];

    /**
     * The entry that this character belongs to.
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(WaitlistEntry::class);
    }

    /**
     * The waitlist that this character has joined.
     */
    public function waitlist(): BelongsToThroughRelation
    {
        return $this->belongsToThrough(Waitlist::class, WaitlistEntry::class);
    }

    /**
     * The character that this character entry is associated with.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * The doctrine ships that this character is associated with.
     */
    public function ships(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                DoctrineShip::class,
                WaitlistCharacterShipEntry::class,
                'entry_id',
                'ship_id'
            )
            ->withTimestamps();
    }

    /**
     * Alias for the ships relation.
     */
    public function doctrineShips(): BelongsToMany
    {
        return $this->ships();
    }
}

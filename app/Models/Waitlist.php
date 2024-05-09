<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Waitlist extends Model
{
    use HasFactory, HasRelationships, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'active',
    ];

    /**
     * The fleets this waitlist is linked to.
     */
    public function fleets(): BelongsToMany
    {
        return $this->morphToMany(Fleet::class, 'fleet', WaitlistFleetLink::class)
            ->using(WaitlistFleetLink::class);
    }

    /**
     * The user entries for this waitlist.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(WaitlistEntry::class);
    }

    /**
     * The list of all entries that have been on the waitlist.
     */
    public function allEntries(): HasMany
    {
        return $this->entries()
            ->withoutRemoved();
    }

    /**
     * The list of users currently on the waitlist.
     */
    public function users(): HasManyDeep
    {
        return $this->hasManyDeep(
            User::class,
            [WaitlistEntry::class],
            [null, 'id'],
            [null, 'user_id']
        );
    }

    /**
     * The list of characters currently on the waitlist.
     */
    public function characters(): HasManyDeep
    {
        return $this->hasManyDeep(
            Character::class,
            [WaitlistEntry::class, WaitlistCharacterEntry::class],
            [null, null, 'id'],
            [null, null, 'character_id']
        );
    }
}

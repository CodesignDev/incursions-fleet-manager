<?php

namespace App\Models;

use App\Models\Concerns\EntryIsRemovable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class WaitlistEntry extends Model
{
    use EntryIsRemovable, HasFactory, HasRelationships, HasUuids;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'characterEntries',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * The waitlist this entry is a part of.
     */
    public function waitlist(): BelongsTo
    {
        return $this->belongsTo(Waitlist::class);
    }

    /**
     * The user this entry is for.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The characters that are attached to this entry.
     */
    public function characters(): HasManyDeep
    {
        return $this->hasManyDeep(
            Character::class,
            [WaitlistCharacterEntry::class],
            [null, 'id'],
            [null, 'character_id']
        );
    }

    /**
     * The character entries that are linked to this entry.
     */
    public function characterEntries(): HasMany
    {
        return $this->hasMany(WaitlistCharacterEntry::class);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\CanBeBlacklisted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Znck\Eloquent\Relations\BelongsToThrough as BelongsToThroughRelation;
use Znck\Eloquent\Traits\BelongsToThrough;

class Character extends Model
{
    use BelongsToThrough, CanBeBlacklisted, HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
     * Scope which wraps around the blacklist exists check
     */
    public function scopeWhereWhitelisted(Builder $builder): void
    {
        $builder->whereDoesntHave('blacklist');
    }

    /**
     * The user that owns this character.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The corporation that this character is a member of.
     */
    public function corporation(): BelongsTo
    {
        return $this->belongsTo(Corporation::class);
    }

    /**
     * The alliance that this character is a member of.
     */
    public function alliance(): BelongsToThroughRelation
    {
        return $this->belongsToThrough(Alliance::class, Corporation::class);
    }

    /**
     * Whether the character is on the blacklist.
     */
    public function blacklist(): HasOne
    {
        return $this->hasOne(BlacklistedCharacters::class);
    }
}

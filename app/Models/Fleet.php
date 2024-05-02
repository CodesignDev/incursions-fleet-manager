<?php

namespace App\Models;

use App\Models\Concerns\FleetCanBeClosed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fleet extends Model
{
    use FleetCanBeClosed, HasFactory, HasUuids, SoftDeletes;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'untracked' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'esi_fleet_id',
        'name',
        'untracked',
    ];

    public function scopeWhereTracked(Builder $query): void
    {
        $query->whereNull('untracked')->orWhere('untracked', false);
    }

    /**
     * The list of members in this fleet.
     */
    public function members(): HasMany
    {
        return $this->hasMany(FleetMember::class);
    }

    /**
     * The list of members who have been in this fleet.
     */
    public function allFleetMembers(): HasMany
    {
        return $this->members()->withTrashed();
    }

    /**
     * The current fleet boss.
     */
    public function boss(): HasOneThrough
    {
        // Use a custom HasOne relation here to effectively create a reversed BelongsTo relation that
        // can work with the through() helper
        $relation = $this
            ->through($this->members())
            ->has(fn ($member) => $member->hasOne(Character::class, 'id', 'character_id'));

        // Apply a custom filter to the relation
        $relation->where('fleet_members.fleet_boss', true);

        return $relation;
    }
}

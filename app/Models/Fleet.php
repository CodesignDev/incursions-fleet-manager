<?php

namespace App\Models;

use App\Enums\FleetStatus;
use App\Models\Concerns\FleetCanBeClosed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Fleet extends Model
{
    use FleetCanBeClosed, HasFactory, HasUuids, SoftDeletes;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'comms',
    ];

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
        'status',
        'untracked',
        'has_fleet_advert',
        'free_move_enabled',
    ];

    /**
     * Get the attributes that should be cast.
     */
    public function casts(): array
    {
        return [
            'status' => FleetStatus::class,
            'untracked' => 'boolean',
            'has_fleet_advert' => 'boolean',
            'free_move_enabled' => 'boolean',
        ];
    }

    /**
     * Filter the query to online include tracked fleets.
     */
    public function scopeWhereTracked(Builder $query): void
    {
        $query->whereNull('untracked')->orWhere('untracked', false);
    }

    /**
     * Filter the query to include fleets where the specified character(s) are the fleet boss.
     */
    public function scopeWhereFleetBoss(Builder $query, array|int $fleetBoss): void
    {
        $query->whereHas('members', function ($builder) use ($fleetBoss) {
            $builder->where('fleet_boss', true)->whereIn('character_id', Arr::wrap($fleetBoss));
        });
    }

    /**
     * Assign the requested character as the boss of the fleet.
     */
    public function assignFleetBoss(Character|int $character): self
    {
        return tap($this, function() use ($character) {
            $this->members()->updateOrCreate(
                ['character_id' => $character instanceof Model ? $character->getKey() : $character],
                ['fleet_boss' => true]
            );
        });
    }

    /**
     * The category that this fleet belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)
            ->withDefault(['name' => 'Unknown']);
    }

    /**
     * The comms channel that this fleet is using.
     */
    public function comms(): BelongsTo
    {
        return $this->belongsTo(CommsChannel::class, 'comms_channel_id')
            ->withInactive()
            ->withDefault([
                'name' => 'No Comms Channel',
                'label' => 'No Comms Channel',
                'url' => '',
                'active' => false,
            ]);
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
     * The list of invites for the fleet.
     */
    public function invites(): HasMany
    {
        return $this->hasMany(FleetInvite::class);
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
            ->has(fn ($member) => $member->hasOne(Character::class, 'id', 'character_id'))
            ->one();

        // Apply a custom filter to the relation
        $relation->where('fleet_members.fleet_boss', true);

        // Force load the user relation
        $relation->with('user');

        return $relation;
    }

    /**
     * The waitlists that are currently linked to this fleet.
     */
    public function waitlists(): MorphToMany
    {
        return $this->morphToMany(Waitlist::class, 'fleet', WaitlistFleetLink::class)
            ->using(WaitlistFleetLink::class);
    }
}

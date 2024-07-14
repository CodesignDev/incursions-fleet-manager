<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctrine extends Model
{
    use CascadeSoftDeletes, HasFactory, HasUuids, SoftDeletes;

    /**
     * The relations to eager load on every query.
     *
     * @var string[]
     */
    protected $with = [
        'groups.ships',
        'ships',
    ];

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
     * The relationships to cascade soft deletes to.
     *
     * @var string[]
     */
    protected $cascadeDeletes = [
        'groups',
        'ships',
    ];

    /**
     * The waitlists that have this doctrine attached.
     */
    public function waitlists(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }

    /**
     * The list of ships that are part of the doctrine.
     */
    public function ships(): HasMany
    {
        return $this->hasMany(DoctrineShip::class);
    }

    /**
     * The list of ship groups that are part of this doctrine.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(DoctrineShipGroup::class);
    }
}

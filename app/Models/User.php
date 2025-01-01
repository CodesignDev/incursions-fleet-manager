<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes {
        HasRoles::roles as private spatieRoles;
        HasRoles::permissions as private spatiePermissions;
    }

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
        'username',
    ];

    /**
     * @inerhitDoc
     */
    public function roles(): BelongsToMany
    {
        return $this->spatieRoles()->withTimestamps();
    }

    /**
     * @inerhitDoc
     */
    public function permissions(): BelongsToMany
    {
        return $this->spatiePermissions()->withTimestamps();
    }

    /**
     * The gice groups the user is a member of.
     */
    public function giceGroups(): BelongsToMany
    {
        return $this
            ->belongsToMany(GiceGroup::class, GiceGroupMember::class)
            ->withPivot('is_primary_group')
            ->as('affiliation')
            ->withTimestamps();
    }

    /**
     * The list of characters that this user owns.
     */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /**
     * @{inheritDoc}
     */
    public function getRememberTokenName(): ?string
    {
        return null;
    }
}

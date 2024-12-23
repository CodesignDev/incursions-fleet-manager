<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GiceGroup extends Model
{
    use HasFactory, SoftDeletes;

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
     * The users who are members of this group.
     */
    public function users(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, GiceGroupMember::class)
            ->withPivot('is_primary_group')
            ->as('affiliation')
            ->withTimestamps();
    }

    /**
     * The roles that are linked to this group.
     */
    public function roles(): BelongsToMany
    {
        return $this
            ->belongsToMany(Role::class, ManagedGroupRole::class, 'group_id')
            ->withPivot('auto_remove_role')
            ->as('managed_role')
            ->withTimestamps();
    }
}

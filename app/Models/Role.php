<?php

namespace App\Models;

use App\Models\Concerns\RoleIsManaged;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use RoleIsManaged;

    /**
     * @inerhitDoc
     */
    public function permissions(): BelongsToMany
    {
        return parent::permissions()->withTimestamps();
    }

    /**
     * @inerhitDoc
     */
    public function users(): BelongsToMany
    {
        return parent::users()->withTimestamps();
    }

    /**
     * The Gice groups that auto-assign this role.
     */
    public function groups(): BelongsToMany
    {
        return $this
            ->belongsToMany(GiceGroup::class, ManagedGroupRole::class, relatedPivotKey: 'group_id')
            ->withPivot(['prevent_manual_assignment', 'auto_remove_role'])
            ->as('managed_role')
            ->withTimestamps();
    }
}

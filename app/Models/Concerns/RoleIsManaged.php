<?php

namespace App\Models\Concerns;

use App\Models\ManagedGroupRole;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait RoleIsManaged
{
    /**
     * Name of the managed roles relation
     */
    private string $relationName = 'managedRoles';

    /**
     * The managed roles that look after this role.
     */
    public function managedRoles(): HasMany
    {
        return $this->hasMany(ManagedGroupRole::class);
    }

    /**
     * Is this role auto-managed by any Gice groups?
     */
    public function isManagedRole(): bool
    {
        // Try and use the relation itself if it is already loaded
        if ($this->relationLoaded($this->relationName)) {
            return $this->getRelation($this->relationName)->isNotEmpty();
        }

        // Otherwise query against the relationship
        return $this->managedRoles()->exists();
    }

    /**
     * Should the role be removed from a user when they are no longer in any of the groups that manage it.
     */
    public function shouldBeAutoRemoved(): bool
    {
        $column = 'auto_remove_role';

        // Try and use the loaded relation if it is already loaded
        if ($this->relationLoaded($this->relationName)) {
            return $this->getRelation($this->relationName)->contains($column, true);
        }

        return $this->managedRoles()->where($column, true)->exists();
    }

    /**
     * Can this be role be manually assigned to a user.
     */
    public function allowManualAssignment(): bool
    {
        $column = 'prevent_manual_assignment';

        // Check if the relation has been loaded, and if it has, check if there is entries that have the
        // relevant flag set
        if ($this->relationLoaded($this->relationName)) {
            if ($this->getRelation($this->relationName)->contains($column, true)) {
                return false;
            }
        }

        // Otherwise query the database for the required flag
        else if ($this->managedRoles()->where($column, true)->exists()) {
            return false;
        }

        return true;
    }
}

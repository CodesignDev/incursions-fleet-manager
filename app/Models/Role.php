<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
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
}

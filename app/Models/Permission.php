<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * @inerhitDoc
     */
    public function roles(): BelongsToMany
    {
        return parent::roles()->withTimestamps();
    }

    /**
     * @inerhitDoc
     */
    public function users(): BelongsToMany
    {
        return parent::users()->withTimestamps();
    }
}

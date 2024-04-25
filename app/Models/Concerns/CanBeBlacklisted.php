<?php

namespace App\Models\Concerns;

use App\Models\BlacklistedCharacters;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait CanBeBlacklisted
{
    /**
     * Whether the current entity is on the blacklist.
     */
    public function onBlacklist(): Attribute
    {
        return Attribute::get(
            fn () => $this->blacklist()->exists()
        );
    }

    /**
     * Add the character to the blacklist.
     */
    public function addToBlacklist(): void
    {
        if ($this->blacklist()->doesntExist()) {
            $this->blacklist()->create();
        }
    }

    /**
     * The blacklist relation.
     */
    public function blacklist(): HasOne
    {
        return $this->hasOne(BlacklistedCharacters::class);
    }
}

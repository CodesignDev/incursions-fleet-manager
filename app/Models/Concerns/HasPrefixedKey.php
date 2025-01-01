<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasPrefixedKey
{
    /**
     * The prefix to add to the model's table.
     */
    protected string $keyPrefix;

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName(): string
    {
        // If the prefix has been set, use this instead
        if (isset($this->keyPrefix) && Str::trim($this->keyPrefix) !== '') {
            return $this->keyPrefix.'_id';
        }

        // Set primary key
        return Str::afterLast(Str::snake(class_basename($this)), '_').'_id';
    }
}

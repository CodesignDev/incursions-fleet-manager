<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait IsSdeUniverseModel
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table ?? Str::of(class_basename($this))
            ->pluralStudly()
            ->prepend('Universe')
            ->snake();
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName(): string
    {
        return Str::of(class_basename($this))
            ->snake()
            ->afterLast('_')
            ->append('_id');
    }
}

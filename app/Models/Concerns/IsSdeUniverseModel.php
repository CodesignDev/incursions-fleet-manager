<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Parental\HasParent;

trait IsSdeUniverseModel
{
    use HasSdeUniverseTable;

    /**
     * Initialize the trait.
     *
     * @return void
     */
    protected function initializeIsSdeUniverseModel(): void
    {
        // Set primary key
        if ($this->getKeyName() === 'id') {
            $this->primaryKey = Str::afterLast(Str::snake(class_basename($this)), '_').'_id';
        }
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
}

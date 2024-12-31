<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Parental\HasParent;

trait IsSdeUniverseModel
{
    /**
     * Initialize the trait.
     *
     * @return void
     */
    protected function initializeIsSdeUniverseModel(): void
    {
        // Class name
        $class = class_basename($this);

        // Compatibility with Parental
        if (in_array(HasParent::class, class_uses_recursive($this), false) && method_exists($this, 'getParentClass')) {
            $class = class_basename($this->getParentClass() ?? get_class($this));
        }

        // Set the table name
        if (! isset($this->table)) {
            $this->table = Str::snake('Universe'.Str::pluralStudly($class));
        }

        // Set primary key
        if ($this->getKeyName() === 'id') {
            $this->primaryKey = Str::afterLast(Str::snake($class), '_').'_id';
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

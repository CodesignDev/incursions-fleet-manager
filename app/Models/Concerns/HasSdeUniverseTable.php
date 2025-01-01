<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Parental\HasParent;

trait HasSdeUniverseTable
{
    /**
     * Initialize the trait.
     *
     * @return void
     */
    protected function initializeHasSdeUniverseTable(): void
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
    }
}

<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Parental\HasParent;

trait HasPrefixedTable
{
    /**
     * The prefix to add to the model's table.
     */
    protected string $tablePrefix;

    /**
     * Initialize the trait.
     *
     * @return void
     */
    protected function initializeHasPrefixedTable(): void
    {
        // Class name
        $class = class_basename($this);

        // Compatibility with Parental
        if (in_array(HasParent::class, class_uses_recursive($this), false) && method_exists($this, 'getParentClass')) {
            $class = class_basename($this->getParentClass() ?? get_class($this));
        }

        // Set the table name
        if (! isset($this->table) && isset($this->tablePrefix) && Str::trim($this->tablePrefix) !== '') {
            $this->table = Str::snake($this->tablePrefix.'_'.Str::pluralStudly($class));
        }
    }
}

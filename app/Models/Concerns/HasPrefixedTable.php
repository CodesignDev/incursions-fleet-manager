<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
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
        // Class name and the list of traits used by the class
        $class = class_basename($this);
        $classTraits = class_uses_recursive($this);

        // Compatibility with Parental
        if (in_array(HasParent::class, $classTraits, true) && method_exists($this, 'getParentClass')) {
            $class = class_basename($this->getParentClass() ?? get_class($this));
        }

        // Set the table name
        if (! isset($this->table) && isset($this->tablePrefix) && Str::trim($this->tablePrefix) !== '') {
            $table = Str::snake($this->tablePrefix.Str::pluralStudly($class));

            // If the model is a pivot, make the table name singular
            if (in_array(AsPivot::class, $classTraits, true)) {
                $table = Str::singular($table);
            }

            $this->setTable($table);
        }
    }
}

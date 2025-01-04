<?php

namespace App\Models\Concerns;

trait IsSdeUniverseModel
{
    use HasPrefixedTable;

    /**
     * Initialize the trait.
     */
    protected function initializeIsSdeUniverseModel(): void
    {
        // Set the table prefix for all tables that have this trait
        if (! isset($this->tablePrefix)) {
            $this->tablePrefix = 'universe';
        }

        // Set the IDs to be non-incremental since all SDE related data is static.
        // Hence, the SDE name :P
        $this->incrementing = false;
    }
}

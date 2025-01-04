<?php

namespace App\Models\Concerns;

trait IsSdeModel
{
    use HasPrefixedTable;

    /**
     * Initialize the trait.
     */
    protected function initializeIsSdeModel(): void
    {
        // Set the table prefix for all tables that have this trait
        if (! isset($this->tablePrefix)) {
            $this->tablePrefix = 'sde';
        }

        // Disable guarding on all SDE models
        if ($this->getGuarded() === ['*']) {
            $this->guard([]);
        }

        // Set the IDs to be non-incremental since all SDE related data is static.
        // Hence, the SDE name :P
        $this->incrementing = false;
    }
}

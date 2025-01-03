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
        if (! isset($this->tablePrefix)) {
            $this->tablePrefix = 'sde';
        }

        // Set the IDs to be non-incremental since all SDE related data is static.
        // Hence, the SDE name :P
        $this->incrementing = false;
    }
}

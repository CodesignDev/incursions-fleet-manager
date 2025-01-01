<?php

namespace App\Models\Concerns;

trait IsSdeModel
{
    use HasPrefixedTable;

    /**
     * The prefix to add to the model's table.
     */
    protected string $tablePrefix = 'sde';

    /**
     * Initialize the trait.
     */
    protected function initializeIsSdeModel(): void
    {
        // Set the IDs to be non-incremental since all SDE related data is static.
        // Hence, the SDE name :P
        $this->incrementing = false;
    }
}

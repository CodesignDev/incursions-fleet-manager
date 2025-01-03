<?php

namespace App\Models\Concerns;

trait IsSdeUniverseModel
{
    use IsSdeModel;

    /**
     * Initialize the trait.
     */
    protected function initializeIsSdeUniverseModel(): void
    {
        $this->tablePrefix = 'universe';
    }
}

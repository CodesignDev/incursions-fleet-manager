<?php

namespace App\Models\Concerns;

trait IsSdeUniverseModel
{
    use IsSdeModel;

    /**
     * The prefix to add to the model's table.
     */
    protected string $tablePrefix = 'universe';
}

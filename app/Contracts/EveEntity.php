<?php

namespace App\Contracts;

interface EveEntity
{
    /**
     * Get the ID for this EVE entity.
     */
    public function getEveEntityId(): int;
}

<?php

namespace App\Models\Concerns;

trait IsEveEntity
{
    public function getEveEntityId(): int
    {
        return $this->getAttribute($this->getEntityIdColumn());
    }

    public function getEntityIdColumn(): string
    {
        return defined(static::class.'::ENTITY_ID') ? static::ENTITY_ID : 'id';
    }
}

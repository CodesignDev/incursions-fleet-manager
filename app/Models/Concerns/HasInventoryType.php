<?php

namespace App\Models\Concerns;

use App\Models\SDE\InventoryType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasInventoryType
{
    /**
     * Initialize the trait.
     */
    public function initializeHasPublishedFlag(): void
    {
        $this->mergeFillable([
            $this->getTypeIdColumn(),
        ]);
    }

    /**
     * The base type that this item is.
     */
    public function entityType(): BelongsTo
    {
        return $this->belongsTo(InventoryType::class, $this->getTypeIdColumn());
    }

    /**
     * Get the name of the "type_id" column.
     *
     * @return string
     */
    public function getTypeIdColumn(): string
    {
        return defined(static::class.'::TYPE_RELATIONSHIP_COLUMN') ? static::TYPE_RELATIONSHIP_COLUMN : 'type_id';
    }
}

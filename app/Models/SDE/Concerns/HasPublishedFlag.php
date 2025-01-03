<?php

namespace App\Models\SDE\Concerns;

trait HasPublishedFlag
{
    /**
     * Initialize the trait.
     */
    public function initializeHasPublishedFlag(): void
    {
        $this->mergeFillable([
            $this->getPublishedColumn(),
        ]);

        $this->mergeCasts([
            $this->getPublishedColumn() => 'boolean',
        ]);
    }

    /**
     * Get the name of the "published" column.
     *
     * @return string
     */
    public function getPublishedColumn(): string
    {
        return defined(static::class.'::PUBLISHED_COLUMN') ? static::PUBLISHED_COLUMN : 'published';
    }

    /**
     * Get the fully qualified "published" column.
     *
     * @return string
     */
    public function getQualifiedPublishedColumn(): string
    {
        return $this->qualifyColumn($this->getPublishedColumn());
    }
}

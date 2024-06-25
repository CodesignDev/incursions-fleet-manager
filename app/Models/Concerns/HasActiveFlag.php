<?php

namespace App\Models\Concerns;

use App\Models\Scopes\ActiveFlagScope;

trait HasActiveFlag
{
    /**
     * Boot the has active flag trait for a model.
     */
    public static function bootHasActiveFlag(): void
    {
        static::addGlobalScope(ActiveFlagScope::class);
    }

    /**
     * Initialize the unlisted fleet trait for an instance.
     */
    public function initializeHasActiveFlag(): void
    {
        $this->mergeFillable([
            'active'
        ]);

        $this->mergeCasts([
            'active' => 'boolean',
        ]);
    }

}

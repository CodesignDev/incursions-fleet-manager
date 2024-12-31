<?php

namespace App\Models\Universe;

use Parental\HasParent;

class Star extends Celestial
{
    use HasParent;

    /**
     * Get the meta attributes that should be cast.
     *
     * @return array<string, string>
     * @noinspection PhpUnused
     */
    protected function metaCasts(): array
    {
        return array_merge(
            ['spectral_class' => 'string'],
            parent::metaCasts()
        );
    }
}

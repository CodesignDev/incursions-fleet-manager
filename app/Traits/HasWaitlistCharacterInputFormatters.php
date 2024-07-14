<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait HasWaitlistCharacterInputFormatters
{
    protected function formatCharacterInputArray(array $data): Collection
    {
        return collect($data)
            ->map(fn ($entry) => $this->formatCharacterInput($entry))
            ->filter(fn ($entry) => data_get($entry, 'ships'));
    }

    protected function formatCharacterInput(array $data): array
    {
        return Arr::only($data, ['character', 'ships']);
    }
}

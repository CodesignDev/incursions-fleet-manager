<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait FetchesNamesFromSde
{
    /**
     * Fetch the name for an item from the SDE
     */
    protected function fetchNameFromSde(int $itemId, $default = null)
    {
        return rescue(fn () => Http::sde()
            ->withUrlParameters(['item_id' => $itemId])
            ->get('/inventory/names/{item_id}')
            ->json('itemName', $default), rescue: value($default));
    }
}

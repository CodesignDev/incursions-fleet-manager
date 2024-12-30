<?php

namespace App\Models\Universe\Concerns;

use App\Models\Universe\PositionData;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasPositionalData
{
    /**
     * The position data for this solar system.
     */
    public function position(): MorphOne
    {
        return $this->morphOne(PositionData::class, 'positional');
    }

    /**
     * Set the position data for this entity.
     *
     * @return static
     */
    public function setPosition(int $x, int $y, int $z, string $prefix = '')
    {
        $data = collect(compact('x', 'y', 'z'))->filter();

        // Apply the prefix if needed
        if (! blank($prefix)) {
            $data = $data->mapWithKeys(function ($value, $key) use ($prefix) {
                $key = (string) str($key)->prepend($prefix, ' ')->camel();
                return [$key => $value];
            });
        }

        // Store the positional data for the entity
        $this->position()->updateOrCreate([], $data->toArray());

        return $this;
    }
}

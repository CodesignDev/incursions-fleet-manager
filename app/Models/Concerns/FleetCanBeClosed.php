<?php

namespace App\Models\Concerns;

use App\Models\Scopes\ClosedFleetScope;

trait FleetCanBeClosed
{
    /**
     * Boot the unlisted fleet trait for a model.
     */
    public static function bootFleetCanBeClosed(): void
    {
        static::addGlobalScope(ClosedFleetScope::class);
    }

    /**
     * Initialize the unlisted fleet trait for an instance.
     */
    public function initializeFleetCanBeClosed(): void
    {
        $this->mergeCasts([
            'closed_at' => 'datetime',
        ]);
    }

    /**
     * Close the specified fleet.
     */
    public function closeFleet(): bool
    {
        // If the model doesn't exist, don't attempt to close the fleet
        if (! $this->exists) {
            return false;
        }

        $time = $this->freshTimestamp();

        // Set the relevant fields
        $this->closed_at = $time;

        return $this->save();
    }

    /**
     * Close the specified fleet model instance without raising any events.
     */
    public function closeFleetQuietly(): bool
    {
        return static::withoutEvents(fn () => $this->closeFleet());
    }

    /**
     * Determine if the specified fleet is closed.
     */
    public function closed(): bool
    {
        return ! is_null($this->closed_at);
    }
}

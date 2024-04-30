<?php

namespace App\Models\Concerns;

use App\Models\Scopes\UnlistedFleetScope;

trait FleetCanBeUnlisted
{
    /**
     * Boot the unlisted fleet trait for a model.
     */
    public static function bootFleetCanBeUnlisted(): void
    {
        static::addGlobalScope(UnlistedFleetScope::class);
    }

    /**
     * Initialize the unlisted fleet trait for an instance.
     */
    public function initializeFleetCanBeUnlisted(): void
    {
        $this->mergeFillable([
            'unlisted'
        ]);

        $this->mergeCasts([
            'unlisted' => 'boolean',
            'listed_at' => 'datetime',
        ]);
    }

    /**
     * Make the specified fleet model instance available to be viewed.
     */
    public function listFleet(): bool
    {
        // If the model doesn't exist, don't attempt to list the fleet
        if (! $this->exists) {
            return false;
        }

        $time = $this->freshTimestamp();

        // Set the relevant fields
        $this->unlisted = false;
        $this->listed_at = $time;

        return $this->save();
    }

    /**
     * List the specified fleet model instance without raising any events.
     */
    public function listFleetQuietly(): bool
    {
        return static::withoutEvents(fn () => $this->listFleet());
    }

    /**
     * Unlist the specified fleet model instance.
     */
    public function unlistFleet(): bool
    {
        // If the model doesn't exist, don't attempt to list the fleet
        if (! $this->exists) {
            return false;
        }

        // Clear the relevant fields
        $this->unlisted = true;
        $this->listed_at = null;

        return $this->save();
    }

    /**
     * Unlist the specified fleet model instance without raising any events.
     */
    public function unlistFleetQuietly(): bool
    {
        return static::withoutEvents(fn () => $this->unlistFleet());
    }

    /**
     * Determine if the model instance is currently unlisted.
     */
    public function isUnlisted(): bool
    {
        return is_null($this->unlisted) || $this->unlisted === true;
    }
}

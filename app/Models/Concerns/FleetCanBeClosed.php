<?php

namespace App\Models\Concerns;

use App\Models\Scopes\ClosedFleetScope;

trait FleetCanBeClosed
{
    /**
     * Boot the fleet can be closed trait for a model.
     */
    public static function bootFleetCanBeClosed(): void
    {
        static::addGlobalScope(ClosedFleetScope::class);
    }

    /**
     * Initialize the fleet can be closed trait for an instance.
     */
    public function initializeFleetCanBeClosed(): void
    {
        $this->mergeCasts([$this->getClosedAtColumn() => 'datetime']);
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
        $this->{$this->getClosedAtColumn()} = $time;

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
        return ! is_null(
            $this->{$this->getClosedAtColumn()}
        );
    }

    /**
     * Get the name of the "closed at" column.
     */
    public function getClosedAtColumn(): string
    {
        return defined(static::class.'::CLOSED_AT') ? static::CLOSED_AT : 'closed_at';
    }

    /**
     * Get the fully qualified "closed at" column.
     */
    public function getQualifiedClosedAtColumn(): string
    {
        return $this->qualifyColumn($this->getClosedAtColumn());
    }
}

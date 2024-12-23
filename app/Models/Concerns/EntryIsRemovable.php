<?php

namespace App\Models\Concerns;

use App\Enums\WaitlistRemovalReason;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

trait EntryIsRemovable
{
    use SoftDeletes;

    /**
     * Initialize the entry is removable trait for an instance.
     */
    public function initializeEntryIsRemovable(): void
    {
        $this->mergeCasts([
            'removal_reason' => WaitlistRemovalReason::class,
        ]);
    }

    /**
     * Include removed entries in the query.
     */
    public function scopeWithRemoved(Builder $builder, bool $withRemoved = true): void
    {
        $builder->withTrashed($withRemoved);
    }

    /**
     * Exclude removed entries from the query.
     */
    public function scopeWithoutRemoved(Builder $builder): void
    {
        $builder->withoutTrashed();
    }

    /**
     * Only query removed entries.
     */
    public function scopeOnlyRemoved(Builder $builder): void
    {
        $builder->onlyTrashed();
    }

    /**
     * Remove the entry and apply relevant data for the removal.
     */
    public function remove(?User $removedBy = null, ?WaitlistRemovalReason $removalReason = null): bool
    {
        // If the query doesn't exist, just exit
        if (! $this->exists) {
            return false;
        }

        // Set columns to update, starting with "removed_at"
        $time = $this->freshTimestamp();
        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        // Update the extra columns
        $columns = array_merge($columns, [
            'removed_by' => optional($removedBy)->id,
            'removal_reason' => $removalReason,
        ]);

        // Save the data
        $result = $this->forceFill($columns)->update();

        $this->fireModelEvent('removed', false);

        return $result;
    }

    /**
     * Remove the entry without raising any events.
     */
    public function removeQuietly(?User $removedBy = null, ?WaitlistRemovalReason $removalReason = null): bool
    {
        return static::withoutEvents(fn () => $this->remove($removedBy, $removalReason));
    }

    /**
     * Determine if the model instance has been removed.
     */
    public function removed(): bool
    {
        return $this->trashed();
    }

    /**
     * The column which is being used as the soft deleted column.
     */
    public function getDeletedAtColumn(): string
    {
        return 'removed_at';
    }
}

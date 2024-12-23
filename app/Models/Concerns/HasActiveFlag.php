<?php

namespace App\Models\Concerns;

use App\Models\Scopes\ActiveFlagScope;
use Illuminate\Database\Eloquent\Model;

trait HasActiveFlag
{
    /**
     * The default state for the active flag.
     */
    protected bool $defaultActiveState = true;

    /**
     * Boot the has active flag trait for a model.
     */
    public static function bootHasActiveFlag(): void
    {
        static::addGlobalScope(ActiveFlagScope::class);

        // Register a closure which sets the default state of the active field if it hasn't already been set
        static::saving(function (Model $model) {
            $column = $this->getActiveFlagColumn();
            if ($model->hasAttribute($column) && $model->getAttribute($column) !== null) {
                return;
            }

            $defaultState = $this->getDefaultActiveState();
            if (! is_null($defaultState)) {
                $model->setAttribute($column, $defaultState);
            }
        });
    }

    /**
     * Initialize the has active flag trait for an instance.
     */
    public function initializeHasActiveFlag(): void
    {
        $this->mergeFillable([$this->getActiveFlagColumn()]);

        $this->mergeCasts([$this->getActiveFlagColumn() => 'boolean']);
    }

    /**
     * Get the default state of the active flag.
     */
    protected function getDefaultActiveState(): bool|null
    {
        return $this->defaultActiveState;
    }

    /**
     * Get the name of the "active" column.
     */
    public function getActiveFlagColumn(): string
    {
        return defined(static::class.'::ACTIVE_FLAG_COLUMN') ? static::ACTIVE_FLAG_COLUMN : 'active';
    }

    /**
     * Get the fully qualified "deleted at" column.
     */
    public function getQualifiedActiveFlagColumn(): string
    {
        return $this->qualifyColumn($this->getActiveFlagColumn());
    }
}

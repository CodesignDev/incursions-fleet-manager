<?php

namespace App\Models\Concerns;

use App\Models\Scopes\OrderedScope;
use Illuminate\Database\Eloquent\Model;

trait IsOrdered
{
    /**
     * Boot the has active flag trait for a model.
     */
    public static function bootIsOrdered(): void
    {
        static::addGlobalScope(OrderedScope::class);

        // Register a closure which sets the default value of the "order" field if it hasn't already been set
        static::saving(function (Model $model) {
            if (! $model->shouldAddOrderOnSave()) {
                return;
            }

            $column = $this->getOrderColumn();
            if ($model->hasAttribute($column) && $model->getAttribute($column) !== null) {
                return;
            }

            $lastOrder = max(static::query()->max($this->getQualifiedOrderColumn()), 0) ?? 0;
            $model->setAttribute($column, $lastOrder + 1);
        });
    }

    /**
     * Initialize the unlisted fleet trait for an instance.
     */
    public function initializeIsOrdered(): void
    {
        $this->mergeFillable([$this->getOrderColumn()]);
    }

    /**
     * The sort direction to apply to the order column.
     */
    public function getOrderColumnDirection(): string
    {
        return property_exists($this, 'orderSortDirection') ? $this->orderSortDirection : 'asc';
    }

    /**
     * Whether to add the next order value on model save.
     */
    protected function shouldAddOrderOnSave(): bool
    {
        return property_exists($this, 'addOrderOnSave') ? $this->addOrderOnSave : true;
    }

    /**
     * Get the name of the "order" column.
     */
    public function getOrderColumn(): string
    {
        return defined(static::class.'::ORDER_COLUMN') ? static::ORDER_COLUMN : 'order';
    }

    /**
     * Get the fully qualified "order" column.
     */
    public function getQualifiedOrderColumn(): string
    {
        return $this->qualifyColumn($this->getOrderColumn());
    }
}

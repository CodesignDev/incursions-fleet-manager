<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ClosedFleetScope implements Scope
{
    /**
     * All the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected array $extensions = ['WithClosedFleets', 'WithoutClosedFleets', 'OnlyClosedFleets'];

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNull('closed_at');
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the with-trashed extension to the builder.
     */
    protected function addWithClosedFleets(Builder $builder): void
    {
        $builder->macro('withClosedFleets', function (Builder $builder, $withClosedFleets = true) {
            if (! $withClosedFleets) {
                return $builder->withoutClosedFleets();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-trashed extension to the builder.
     */
    protected function addWithoutClosedFleets(Builder $builder): void
    {
        $builder->macro('withoutClosedFleets', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNull('closed_at');

            return $builder;
        });
    }

    /**
     * Add the only-trashed extension to the builder.
     */
    protected function addOnlyClosedFleets(Builder $builder): void
    {
        $builder->macro('onlyClosedFleets', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNotNull('closed_at');

            return $builder;
        });
    }
}

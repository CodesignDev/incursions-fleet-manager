<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveFlagScope implements Scope
{
    /**
     * All the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected array $extensions = ['WithInactive', 'WithoutInactive', 'OnlyInactive'];

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getQualifiedActiveFlagColumn(), true);
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
     * Add the with-inactive extension to the builder.
     */
    protected function addWithInactive(Builder $builder): void
    {
        $builder->macro('withInactive', function (Builder $builder, $withInactive = true) {
            if (! $withInactive) {
                return $builder->withoutInactive();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-trashed extension to the builder.
     */
    protected function addWithoutInactive(Builder $builder): void
    {
        $builder->macro('withoutInactive', function (Builder $builder) {
            $builder->withoutGlobalScope($this)->where(
                $builder->getModel()->getActiveFlagColumn(),
                true
            );

            return $builder;
        });
    }

    /**
     * Add the only-trashed extension to the builder.
     */
    protected function addOnlyInactive(Builder $builder): void
    {
        $builder->macro('onlyInactive', function (Builder $builder) {
            $builder->withoutGlobalScope($this)->where(
                $builder->getModel()->getActiveFlagColumn(),
                0
            );

            return $builder;
        });
    }
}

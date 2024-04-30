<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UnlistedFleetScope implements Scope
{
    /**
     * All the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected array $extensions = ['WithUnlisted', 'WithoutUnlisted', 'OnlyUnlisted'];

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('unlisted', false);
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
    protected function WithUnlisted(Builder $builder): void
    {
        $builder->macro('withUnlisted', function (Builder $builder, $withUnlisted = true) {
            if (! $withUnlisted) {
                return $builder->withoutUnlisted();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-trashed extension to the builder.
     */
    protected function addWithoutUnlisted(Builder $builder): void
    {
        $builder->macro('withoutUnlisted', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->where('unlisted', false);

            return $builder;
        });
    }

    /**
     * Add the only-trashed extension to the builder.
     */
    protected function addOnlyUnlisted(Builder $builder): void
    {
        $builder->macro('onlyUnlisted', function (Builder $builder) {
            $builder->withoutGlobalScope($this)->where(function (Builder $query) {
                $query->whereNull('unlisted')
                    ->orWhere('unlisted', true);
            });

            return $builder;
        });
    }
}

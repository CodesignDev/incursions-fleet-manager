<?php

namespace App\Macros;

use Closure;
use Illuminate\Database\Schema\ColumnDefinition;

/** @mixin \Illuminate\Database\Schema\Blueprint */
class BlueprintMixin
{
    public function staticId(): Closure
    {
        return function (string $column = 'id'): ColumnDefinition {
            return $this->unsignedBigInteger($column)->primary();
        };
    }

    public function uuidId(): Closure
    {
        return function (string $column = 'id'): ColumnDefinition {
            return $this->uuid($column)->primary();
        };
    }

    public function positionCoordinates(): Closure
    {
        return function ($prefix = '', $nullable = false, $only = []) {
            $positionalColumns = collect(['x', 'y', 'z'])
                ->unless(blank($only))
                ->intersect($only)
                ->all();

            foreach ($positionalColumns as $column) {
                $columnName = str($column)
                    ->unless(blank($prefix))
                    ->prepend($prefix, ' ')
                    ->camel();

                $this->bigInteger($columnName)->nullable($nullable);
            }
        };
    }
}

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
            return $this->uuid('id')->primary();
        };
    }
}

<?php

namespace App\Macros;

use Closure;
use Inertia\LazyProp;

/** @mixin \Inertia\ResponseFactory */
class InertiaMixin
{
    /**
     * @return \Closure
     */
    public function lazyIf(): Closure
    {
        return function (Closure|bool $value, callable $callback): callable|LazyProp {
            return value($value) ? $this->lazy($callback) : $callback;
        };
    }

    /**
     * @return \Closure
     */
    public function lazyUnless(): Closure
    {
        return function (Closure|bool $value, callable $callback): callable|LazyProp {
            /** @var \Inertia\ResponseFactory $this */
            return $this->lazyIf(! value($value), $callback);
        };
    }
}

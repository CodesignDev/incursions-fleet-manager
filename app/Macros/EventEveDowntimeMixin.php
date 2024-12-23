<?php

namespace App\Macros;

use Closure;
use Illuminate\Console\Scheduling\Event;

/** @mixin \Illuminate\Console\Scheduling\Event */
class EventEveDowntimeMixin
{
    public function downtime(): Closure
    {
        return function (): Event {
            return $this->timezone('UTC')->daily()->at('11:00');
        };
    }

    public function atDowntime(): Closure
    {
        return $this->downtime();
    }

    public function eveDowntime(): Closure
    {
        return $this->downtime();
    }
}

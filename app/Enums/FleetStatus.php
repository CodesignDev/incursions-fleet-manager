<?php

namespace App\Enums;

enum FleetStatus: string
{
    case FORMING = 'forming';
    case RUNNING = 'running';
    case ON_BREAK = 'on-break';
    case DOCKING = 'docking';
    case STANDING_DOWN = 'standing-down';
    case UNKNOWN = 'unknown';
}

<?php

namespace App\Enums;

use ArchTech\Enums\From;

enum SolarSystemCelestialType
{
    use From;

    case Star;
    case Planet;
    case Moon;
    case AsteroidBelt;
}

<?php

namespace App\Models\Universe;

use App\Models\Universe\Concerns\OrbitsPlanet;
use Parental\HasParent;

class Moon extends Celestial
{
    use HasParent, OrbitsPlanet;
}

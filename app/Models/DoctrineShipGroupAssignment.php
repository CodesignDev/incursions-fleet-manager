<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DoctrineShipGroupAssignment extends Pivot
{
    use HasUuids;
}

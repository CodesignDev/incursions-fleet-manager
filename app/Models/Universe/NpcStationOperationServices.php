<?php

namespace App\Models\Universe;

use App\Models\Concerns\IsSdeUniverseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class NpcStationOperationServices extends Pivot
{
    use HasUuids, IsSdeUniverseModel;
}

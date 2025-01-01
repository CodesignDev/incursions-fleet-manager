<?php

namespace App\Models\Universe;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class NpcStationOperationServices extends Pivot
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'universe_npc_station_operation_services';
}

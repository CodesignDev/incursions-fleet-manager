<?php

namespace App\Models\SDE;

use App\Models\Concerns\IsSdeModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FactionMemberRaces extends Pivot
{
    use HasUuids, IsSdeModel;
}

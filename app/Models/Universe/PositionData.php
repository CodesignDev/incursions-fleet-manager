<?php

namespace App\Models\Universe;

use App\Models\Concerns\IsSdeUniverseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PositionData extends Model
{
    use HasUuids, IsSdeUniverseModel;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * The entity that this position data belongs to.
     */
    public function positional(): MorphTo
    {
        return $this->morphTo();
    }
}

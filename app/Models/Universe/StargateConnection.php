<?php

namespace App\Models\Universe;

use App\Models\Concerns\IsSdeUniverseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StargateConnection extends Model
{
    use HasUuids, IsSdeUniverseModel;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * The source stargate.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Stargate::class);
    }

    /**
     * The destination stargate.
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Stargate::class);
    }
}

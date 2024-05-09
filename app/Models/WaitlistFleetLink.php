<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WaitlistFleetLink extends MorphPivot
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'waitlist_fleet_links';

    /**
     * The fleet this is attached to.
     */
    public function fleet(): MorphTo
    {
        return $this->morphTo(Fleet::class);
    }

    /**
     * The waitlist that is linked to the fleet.
     */
    public function waitlist(): BelongsTo
    {
        return $this->belongsTo(Waitlist::class);
    }
}

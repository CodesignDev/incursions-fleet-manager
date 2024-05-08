<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WaitlistFleetLink extends Model
{
    use HasUuids;

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

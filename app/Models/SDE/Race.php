<?php

namespace App\Models\SDE;

use App\Models\Concerns\IsSdeModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Race extends Model
{
    use IsSdeModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'corvette_ship_id',
        'name',
        'description',
    ];

    /**
     * The ship type of the corvette for this race.
     */
    public function corvette(): BelongsTo
    {
        return $this->belongsTo(InventoryType::class, 'corvette_ship_id');
    }
}

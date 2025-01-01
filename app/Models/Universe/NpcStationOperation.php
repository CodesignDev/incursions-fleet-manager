<?php

namespace App\Models\Universe;

use App\Models\Concerns\HasPrefixedKey;
use App\Models\Concerns\IsSdeUniverseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Plank\Metable\Metable;

class NpcStationOperation extends Model
{
    use HasPrefixedKey, IsSdeUniverseModel, Metable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'operation_id',
        'name',
        'description',
    ];

    /**
     * The services that this station operation operates..
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            NpcStationService::class,
            NpcStationOperationServices::class,
            foreignPivotKey: 'operation_id',
            relatedPivotKey: 'service_id',
        )->withTimestamps();
    }
}

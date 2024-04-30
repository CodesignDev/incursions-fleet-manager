<?php

namespace App\Models;

use App\Models\Concerns\FleetCanBeClosed;
use App\Models\Concerns\FleetCanBeUnlisted;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fleet extends Model
{
    use FleetCanBeClosed, FleetCanBeUnlisted, HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'esi_fleet_id',
        'name',
    ];
}

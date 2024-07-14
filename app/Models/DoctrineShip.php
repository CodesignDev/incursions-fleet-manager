<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctrineShip extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The doctrine this ship is a part of.
     */
    public function doctrine(): BelongsTo
    {
        return $this->belongsTo(Doctrine::class);
    }

    /**
     * The ship group(s) this ship is grouped under.
     */
    public function groups(): BelongsToMany
    {
        return $this
            ->belongsToMany(DoctrineShipGroup::class, DoctrineShipGroupAssignment::class)
            ->withTimestamps();
    }
}

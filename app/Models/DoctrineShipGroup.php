<?php

namespace App\Models;

use App\Models\Concerns\IsOrdered;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctrineShipGroup extends Model
{
    use HasFactory, HasUuids, IsOrdered, SoftDeletes;

    public const ORDER_COLUMN = 'display_order';


    protected bool $addOrderOnSave = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The doctrine this group is linked to.
     */
    public function doctrine(): BelongsTo
    {
        return $this->belongsTo(Doctrine::class);
    }

    /**
     * The ship(s) that are a member of this group.
     */
    public function ships(): BelongsToMany
    {
        return $this
            ->belongsToMany(DoctrineShip::class, DoctrineShipGroupAssignment::class)
            ->withTimestamps();
    }
}

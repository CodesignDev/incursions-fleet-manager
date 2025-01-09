<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nevadskiy\Position\HasPosition;

class DoctrineShipGroup extends Model
{
    use HasFactory, HasPosition, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the name of the "position" column.
     */
    public function getPositionColumn(): string
    {
        return 'display_order';
    }

    /**
     * Get attributes for grouping positions.
     */
    public function groupPositionBy(): array
    {
        return ['doctrine_id'];
    }

    /**
     * Determine if the order by position should be applied always.
     */
    public function alwaysOrderByPosition(): bool
    {
        return true;
    }

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

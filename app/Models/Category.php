<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nevadskiy\Position\HasPosition;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Category extends Model
{
    use HasPosition, HasRelationships, HasUuids, SoftDeletes;

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
        return 'order';
    }

    /**
     * Determine if the order by position should be applied always.
     */
    public function alwaysOrderByPosition(): bool
    {
        return true;
    }

    /**
     * The fleets that are listed under this category.
     */
    public function fleets(): HasMany
    {
        return $this->hasMany(Fleet::class);
    }

    /**
     * The waitlists that effectively fall under this category.
     */
    public function waitlists(): HasManyThrough
    {
        return $this
            ->hasManyDeepFromRelations(
                $this->fleets(),
                fn () => (new Fleet)->waitlists()
            )
            ->where(function ($query) {
                $query->where('fleets.untracked', 0)
                    ->whereNull('fleets.closed_at');
            });
    }
}

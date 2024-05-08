<?php

namespace App\Models;

use App\Models\Scopes\SortedCategoryScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        // Register a scope that handles sorting automatically
        static::addGlobalScope(SortedCategoryScope::class);

        // Register a closure which sets the order field if it isn't already filled in
        static::saving(function (Category $category) {
            if ($category->hasAttribute('order') && $category->getAttribute('order') !== null) {
                return;
            }

            $lastOrder = max(static::query()->max('order'), 0) ?? 0;
            $category->setAttribute('order', $lastOrder + 1);
        });
    }

    /**
     * The fleets that are listed under this category.
     */
    public function fleets(): HasMany
    {
        return $this->hasMany(Fleet::class);
    }
}

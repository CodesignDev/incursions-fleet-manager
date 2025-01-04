<?php

namespace App\Models\SDE;

use App\Models\Concerns\IsSdeModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaGroup extends Model
{
    use IsSdeModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'description',
    ];

    /**
     * The types that are part of this meta group.
     */
    public function types(): HasMany
    {
        return $this->hasMany(InventoryType::class, 'meta_group_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagedGroupRole extends Model
{
    use HasUuids, SoftDeletes;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'prevent_manual_assignment' => false,
        'auto_remove_role' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prevent_manual_assignment',
        'auto_remove_role',
    ];

    /**
     * Get the attributes that should be cast.
     */
    public function casts(): array
    {
        return [
            'prevent_manual_assignment' => 'boolean',
            'auto_remove_role' => 'boolean',
        ];
    }

    /**
     * The role this is attached to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The group that is managing the role.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(GiceGroup::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

class Corporation extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'alliance_id',
        'name',
        'ticker',
        'tax_rate',
    ];

    /**
     * Get the attributes that should be cast.
     */
    public function casts(): array
    {
        return [
            'tax_rate' => 'float',
        ];
    }

    /**
     * Is the corporation an NPC corp?
     */
    public function is_npc(): Attribute
    {
        return Attribute::get(
            fn () => $this->id >= 1_000_000 && $this->id <= 2_000_000
        );
    }

    /**
     * The alliance that this corporation is a member of.
     */
    public function alliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class);
    }

    /**
     * The characters that are members of this corporation.
     */
    public function members(): HasMany
    {
        return $this->hasMany(Character::class);
    }
}

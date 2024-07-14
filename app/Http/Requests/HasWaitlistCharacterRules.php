<?php

namespace App\Http\Requests;

use App\Models\Character;
use App\Models\DoctrineShip;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait HasWaitlistCharacterRules
{
    /**
     * A flag to show whether to apply the doctrine-based validation rules
     */
    private bool $applyDoctrineRules = false;

    /**
     * Returns a validation rule which validates the character entry array
     */
    protected function characterArrayRule(): string
    {
        $keys = collect($this->characterRules())
            ->keys()
            ->reject(fn ($key) => Str::contains($key, '*'));

        return 'array:'.$keys->join(',');
    }

    /**
     * Apply the relevant validation rules for each character entry
     */
    protected function applyCharacterRules(string $keyPrefix = ''): array
    {
        return collect($this->characterRules())
            ->unless(empty($keyPrefix))
            ->mapWithKeys(fn($value, $key) => [$keyPrefix.'.'.$key => $value])
            ->toArray();
    }

    /**
     * The rules for the character data array.
     */
    protected function characterRules(): array
    {
        $applyDoctrineRules = $this->applyDoctrineRules;

        $rules = collect([
            'character' => [
                'required',
                'distinct',
                'integer',
                Rule::exists(Character::class, 'id')
                    ->where('user_id', $this->user()->id),
            ],
            'ships' => [
                'sometimes',
                'required',
                $applyDoctrineRules ? 'array' : 'string',
            ],
        ]);

        return $rules
            ->when($applyDoctrineRules)
            ->merge([
                'ships.*' => [
                    'required',
                    'uuid',
                    Rule::exists(DoctrineShip::class, 'id'),
                ],
            ])
            ->toArray();
    }

    /**
     * A wrapper which can apply the doctrine-based validation rules
     */
    protected function includeDoctrineBasedRules($addDoctrineRules, $callback)
    {
        $originalValue = $this->applyDoctrineRules;

        $this->applyDoctrineRules = $addDoctrineRules;

        return tap(value($callback), fn () => $this->applyDoctrineRules = $originalValue);
    }
}

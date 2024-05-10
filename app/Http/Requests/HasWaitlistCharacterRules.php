<?php

namespace App\Http\Requests;

use App\Models\Character;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait HasWaitlistCharacterRules
{
    protected function characterArrayRule(): string
    {
        $keys = collect($this->characterRules())
            ->keys()
            ->reject(fn ($key) => Str::contains($key, '*'));

        return 'array:'.$keys->join(',');
    }

    protected function applyCharacterRules(string $keyPrefix = ''): array
    {
        return collect($this->characterRules())
            ->unless(empty($keyPrefix))
            ->mapWithKeys(fn($value, $key) => [$keyPrefix.'.'.$key => $value])
            ->toArray();
    }

    protected function characterRules(): array
    {
        return [
            'character' => [
                'required',
                'distinct',
                'integer',
                Rule::exists(Character::class, 'id')
                    ->where('user_id', $this->user()->id),
            ],
//            'ships' => 'sometimes|required|array',
//            'ships.*' => [
//                'required',
//                'uuid',
//                Rule::exists(DoctrineShip::class, 'id'),
//            ],
            'ship' => 'sometimes|required|string',
        ];
    }
}

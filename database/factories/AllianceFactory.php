<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alliance>
 */
class AllianceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->numberBetween(99_000_000, 100_000_000),
            'name' => fake()->company(),
            'ticker' => Collection::times(5, fn() => Str::upper(fake()->randomLetter()))->join(''),
        ];
    }
}

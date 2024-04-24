<?php

namespace Database\Factories;

use App\Models\Alliance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AllianceStandingsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Alliance::factory(),
            'contact_type' => function (array $attributes) {
                return Alliance::find($attributes['contact_id'])->type;
            },
            'standing' => fake()->randomFloat(1, -10, 10),
        ];
    }
}

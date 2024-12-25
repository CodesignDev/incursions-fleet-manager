<?php

namespace Database\Factories;

use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FleetScan>
 */
class FleetScanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'character_id' => Character::factory(),
            'fleet_id' => fake()->numberBetween(1_100_000_000_000, 1_200_000_000_000),
            'fleet_boss_id' => function ($attributes) {
                return fake()
                    ->optional(0.75, $attributes['character_id'])
                    ->passthrough(fn () => Character::factory());
            }
        ];
    }
}

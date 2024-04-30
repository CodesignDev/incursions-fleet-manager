<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fleet>
 */
class FleetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fleet_id' => fake()->numberBetween(1_100_000_000_000, 1_200_000_000_000),
            'name' => fake()->words(),
            'unlisted' => false,
            'listed_at' => fake()->dateTimeBetween('-1 month')
        ];
    }

    /**
     * Indicate that the model should be flagged as unlisted.
     */
    public function unlisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'unlisted' => fake()->optional()->passthrough(true),
            'listed_at' => null,
        ]);
    }

    /**
     * Indicate that the model should be flagged as closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'closed_at' => now(),
        ]);
    }
}

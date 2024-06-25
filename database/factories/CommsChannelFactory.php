<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommsChannel>
 */
class CommsChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Fleet '.fake()->randomLetter(),
            'label' => fake()->words(2),
            'url' => 'https://gnf.lt/FZsqXJM.html', // AFK Channel
            'active' => true,
        ];
    }

    /**
     * Indicate that the model should be flagged as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}

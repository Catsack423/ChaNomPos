<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryLog>
 */
class InventoryLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ingredient_id' => \App\Models\Ingredient::factory(),
            'user_id' => \App\Models\User::factory(),
            'action' => $this->faker->randomElement(['add', 'reduce']),
            'quantity' => $this->faker->numberBetween(10, 500),
            'reason' => $this->faker->sentence(),
        ];
    }
}

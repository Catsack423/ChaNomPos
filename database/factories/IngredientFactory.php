<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Tea base', 'Fresh Milk', 'Brown Sugar', 'Pearls', 'Cocoa']),
            'unit' => $this->faker->randomElement(['ml', 'g', 'scoop']),
        ];
    }
}

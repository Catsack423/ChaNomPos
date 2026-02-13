<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true), // เช่น "Milk Tea", "Green Tea"
            'price' => $this->faker->randomFloat(2, 35, 95),
            'is_active' => true,
            'description' => $this->faker->sentence(),
            'imgurl' => 'image/CV-milk-tea.jpg', // Path สมมติ
        ];
    }
}

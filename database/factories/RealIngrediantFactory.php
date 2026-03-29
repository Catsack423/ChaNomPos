<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\Real_ingrediant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Real_ingrediant>
 */
class RealIngrediantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Real_ingrediant::class;

    public function definition(): array
    {
        return [
            'ingredient_id' => Ingredient::factory(),
            'quantity' => $this->faker->randomFloat(2, 500, 1000),
            'price' => $this->faker->randomFloat(2, 50, 200),
            'expried' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'in_use' => false,
        ];
    }
}

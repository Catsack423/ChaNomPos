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
            // สุ่มหยิบ Ingredient ID จากที่มีอยู่แล้วในตาราง
            'ingredient_id' => \App\Models\Ingredient::inRandomOrder()->first()?->id
                ?? \App\Models\Ingredient::factory(),

            // สุ่มหยิบ User ID จากที่มีอยู่แล้ว
            'user_id' => \App\Models\User::inRandomOrder()->first()?->id
                ?? \App\Models\User::factory(),

            'action' => $this->faker->randomElement(['add', 'reduce']),
            'quantity' => $this->faker->numberBetween(10, 500),
            'reason' => $this->faker->sentence(),
        ];
    }
}

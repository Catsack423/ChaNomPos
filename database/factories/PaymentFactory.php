<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => \App\Models\Sale::factory(),
            'medthod' => $this->faker->randomElement(['cash', 'promptpay', 'credit_card']),
            'amount' => $this->faker->randomFloat(2, 35, 500),
            'paid_at' => now(),
        ];
    }
}

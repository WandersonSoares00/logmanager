<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'meli_item_id' => 'MLB' . $this->faker->randomNumber(8),
            'title' => $this->faker->words(3, true),
            'quantity' => $this->faker->numberBetween(1, 3),
            'unit_price' => $this->faker->randomFloat(2, 10, 200),
        ];
    }
}

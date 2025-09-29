<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $meliAccount = \App\Models\MeliAccount::first();
        if (!$meliAccount) {
            throw new \Exception('É necessário ter pelo menos uma MeliAccount para criar pedidos de teste.');
        }

        $status = $this->faker->randomElement(['paid', 'ready_to_ship', 'shipped', 'delivered', 'cancelled']);

        $paidAt = $this->faker->dateTimeBetween('-3 months', 'now');
        $shippedAt = in_array($status, ['shipped', 'delivered']) ? $this->faker->dateTimeBetween($paidAt, 'now') : null;

        $shippingId = $this->faker->unique()->randomNumber(8);


        $labelPath = null;
        if (in_array($status, ['ready_to_ship', 'shipped', 'delivered'])) {
            $labelPath = 'labels/shipping-' . $shippingId . '.pdf';
        }

        return [
            'meli_order_id' => $this->faker->unique()->randomNumber(8),
            'meli_account_id' => $meliAccount->id,
            'status' => $status,
            'total_amount' => $this->faker->randomFloat(2, 50, 1000),
            'shipping_id' => $shippingId,
            'paid_at' => $paidAt,
            'shipped_at' => $shippedAt,
            'shipping_label_local_path' => $labelPath, // <-- CAMPO ADICIONADO
        ];
    }
}

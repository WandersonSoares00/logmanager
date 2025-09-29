<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            $this->command->error('Nenhum usuário encontrado. Por favor, se autentique pelo menos uma vez antes de executar o seeder.');
            return;
        }

        $this->command->info('A criar 25 pedidos de teste com itens...');

        // Cria 25 pedidos
        Order::factory(25)
            ->create()
            ->each(function ($order) {
                // Para cada pedido criado, cria entre 1 a 5 itens de pedido aleatoriamente
                OrderItem::factory(rand(1, 5))->create([
                    'order_id' => $order->id,
                ]);
            });

        $this->command->info('Pedidos de teste criados com sucesso!');
    }
}

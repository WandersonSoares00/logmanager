<?php

namespace App\Services;

use App\Models\MeliAccount;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderSyncService
{
    // chamada pelo worker_orders para criar ou atualizar pedidos
    public function syncOrderFromNotification(array $notificationData): void
    {
        $meliAccount = MeliAccount::where('meli_user_id', $notificationData['user_id'])->first();

        if (!$meliAccount) {
            Log::warning('Recebida notificação para uma conta MELI não sincronizada.', $notificationData);
            return;
        }

        $response = Http::withToken($meliAccount->access_token)
            ->get('https://api.mercadolibre.com' . $notificationData['resource']);

        $response->throw();
        $orderData = $response->json();

        DB::transaction(function () use ($orderData, $meliAccount) {

            // Salva ou atualiza o pedido
            $order = Order::updateOrCreate(
                ['meli_order_id' => $orderData['id']],
                [
                    'meli_account_id' => $meliAccount->id,
                    'status' => $orderData['status'],
                    'total_amount' => $orderData['total_amount'],
                    'shipping_id' => $orderData['shipping']['id'] ?? null,
                    'paid_at' => isset($orderData['date_approved']) ? now()->parse($orderData['date_approved']) : null,
                ]
            );

            if (!$order->wasRecentlyCreated() && $order->wasChanged('status')) {

                Log::info("Status do pedido {$order->meli_order_id} alterado para: {$order->status}");

                $order->statusLogs()->create([
                    'status' => $order->status // order_id preenchido automaticamente
                ]);
            }

            // Salva ou atualiza os itens do pedido
            foreach ($orderData['order_items'] as $itemData) {
                OrderItem::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'meli_item_id' => $itemData['item']['id'],
                    ],
                    [
                        'title' => $itemData['item']['title'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                    ]
                );
            }

            // Dispacha o job para baixar a etiqueta se o pedido estiver pronto para envio
            if ($orderData['status'] === 'ready_to_ship' && !$order->shipping_label_local_path) {
                Log::info("Pedido {$order->meli_order_id} está pronto para envio. A despachar job para baixar a etiqueta.");

                \App\Jobs\DownloadShippingLabel::dispatch($order)->onQueue('orders.label');
            }

            Log::info("Pedido {$order->meli_order_id} sincronizado com sucesso para a conta {$meliAccount->nickname}.");
        });
    }
}

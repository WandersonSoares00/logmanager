<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMeliNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MeliNotificationController extends Controller
{
    // Recebe e processa as notificações (webhooks) do Mercado Livre.
    public function handle(Request $request)
    {
        Log::info('Notificação recebida do MELI:', $request->all());

        $notificationData = $request->all();

        if (isset($notificationData['resource'], $notificationData['user_id'], $notificationData['topic'])) {

            // Despacha o trabalho para a fila de pedidos para ser processado em background.
            ProcessMeliNotification::dispatch($notificationData)->onQueue('orders.sync');
        }

        return response()->json(['status' => 'notification received'], 200);
    }
}

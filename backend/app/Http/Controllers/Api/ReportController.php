<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;

class ReportController extends Controller
{
    // Retorna todos os pedidos que foram enviados na data atual
    public function shippedToday()
    {
        $orders = Order::whereDate('shipped_at', today())
                       ->with('meliAccount:id,nickname')
                       ->get();

        return response()->json($orders);
    }

    // retorna o SLA médio de envio para a semana atual
    // SLA: Tempo médio (em horas) entre o pagamento e o envio
    public function weeklySla()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $averageSeconds = Order::whereNotNull('paid_at')
                               ->whereNotNull('shipped_at')
                               ->whereBetween('shipped_at', [$startOfWeek, $endOfWeek])
                               ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, paid_at, shipped_at)) as avg_sla_seconds')
                               ->value('avg_sla_seconds');

        $totalOrdersInPeriod = Order::whereNotNull('paid_at')
                                    ->whereNotNull('shipped_at')
                                    ->whereBetween('shipped_at', [$startOfWeek, $endOfWeek])
                                    ->count();

        return response()->json([
            'start_of_week' => $startOfWeek->toDateString(),
            'end_of_week' => $endOfWeek->toDateString(),
            'average_sla_in_seconds' => (float) $averageSeconds,
            'average_sla_in_hours' => $averageSeconds ? round($averageSeconds / 3600, 2) : 0,
            'total_orders_in_period' => $totalOrdersInPeriod,
        ]);
    }
}

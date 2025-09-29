<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // Exibe uma lista paginada de pedidos para o usuário autenticado com suporte para filtros
    public function index(Request $request)
    {
        $request->validate([
            'meli_account_id' => 'nullable|integer|exists:meli_accounts,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Auth::user()->orders();

        if ($request->filled('meli_account_id')) {
            $query->where('meli_account_id', $request->meli_account_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('orders.paid_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('orders.paid_at', '<=', $request->end_date);
        }

        $orders = $query->with('meliAccount:id,nickname')
                        ->latest('orders.paid_at')
                        ->paginate(15);

        $orders->getCollection()->transform(function ($order) {
            $order->makeHidden(['meli_order_id']);
            return $order;
        });

        return response()->json($orders);
    }
}

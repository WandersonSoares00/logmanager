<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
//use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShippingLabelController extends Controller
{
    public function show(Order $order): StreamedResponse|JsonResponse
    {

        // Valida a Existência da Etiqueta
        if (!$order->shipping_label_local_path || !Storage::exists($order->shipping_label_local_path)) {
            return response()->json(['message' => 'Etiqueta de envio não encontrada.'], 404);
        }

        // Devolve o Ficheiro para Download
        return Storage::download($order->shipping_label_local_path);
    }
}

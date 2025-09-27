<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadShippingLabel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        Log::info("Iniciando download da etiqueta para o pedido {$this->order->meli_order_id}.");

        $meliAccount = $this->order->meliAccount;

        $response = Http::withToken($meliAccount->access_token)
            ->withHeaders(['Accept' => 'application/json'])
            ->get('https://api.mercadolibre.com/shipment_labels', [
                'shipment_ids' => $this->order->shipping_id,
                'response_type' => 'pdf', // Petiqueta em PDF
            ]);

        $response->throw();

        $pdfContent = $response->body();

        $filePath = "labels/shipping-{$this->order->shipping_id}.pdf";
        Storage::put($filePath, $pdfContent);

        $this->order->update([
            'shipping_label_local_path' => $filePath,
        ]);

        Log::info("Etiqueta para o pedido {$this->order->meli_order_id} guardada em: {$filePath}");
    }
}

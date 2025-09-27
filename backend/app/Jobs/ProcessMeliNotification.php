<?php

namespace App\Jobs;

use App\Services\OrderSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessMeliNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $notificationData;

    public function __construct(array $notificationData)
    {
        $this->notificationData = $notificationData;
    }

    public function handle(OrderSyncService $orderSyncService): void
    {
        try {
            Log::info('Processando notificação para o recurso: ' . $this->notificationData['resource']);

            if ($this->notificationData['topic'] === 'orders_v2') {
                $orderSyncService->syncOrderFromNotification($this->notificationData);
            }

        } catch (Throwable $e) {
            Log::error('Erro ao processar notificação do MELI: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            throw $e;
        }
    }
}

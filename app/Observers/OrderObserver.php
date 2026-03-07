<?php

namespace App\Observers;

use App\Http\Services\OutgoingWebhookService;
use App\Models\Order;

class OrderObserver
{
    protected OutgoingWebhookService $webhookService;

    public function __construct(OutgoingWebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            $this->webhookService->sendOrderStatusChanged($order);
        }
    }
}

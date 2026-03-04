<?php

namespace App\Http\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutgoingWebhookService
{
    /**
     * Sipariş durumunu dış sisteme gönderir.
     * Yeni sipariş veya durum güncellemesi.
     */
    public function sendOrderCreated(Order $order): void
    {
        $restaurant = $order->restaurant;
        $urls       = $this->getWebhookUrls($restaurant);

        if (empty($urls)) {
            return;
        }

        $payload = array_merge(['event' => 'new_order'], $this->buildOrderPayload($order));

        foreach ($urls as $url) {
            $this->send($url, $restaurant->api_token, $payload);
        }
    }

    public function sendOrderStatusChanged(Order $order): void
    {
        $restaurant = $order->restaurant;
        $urls       = $this->getWebhookUrls($restaurant);

        if (empty($urls)) {
            return;
        }

        $payload = [
            'event'      => 'order_status_changed',
            'order_code' => $order->order_code,
            'status'     => $this->mapStatus($order->status),
        ];

        foreach ($urls as $url) {
            $this->send($url, $restaurant->api_token, $payload);
        }
    }

    private function getWebhookUrls($restaurant): array
    {
        if (!$restaurant || blank($restaurant->webhook_url)) {
            return [];
        }

        $decoded = json_decode($restaurant->webhook_url, true);

        // Eski format: tek URL string
        if (!is_array($decoded)) {
            return filter_var($restaurant->webhook_url, FILTER_VALIDATE_URL) ? [$restaurant->webhook_url] : [];
        }

        return array_filter($decoded);
    }

    private function buildOrderPayload(Order $order): array
    {
        $order->load(['items.menuItem', 'user']);

        $items = $order->items->map(function ($item) {
            return [
                'name'       => $item->menuItem->name ?? $item->name ?? '',
                'quantity'   => $item->quantity,
                'unit_price' => $item->price,
                'item_total' => $item->price * $item->quantity,
                'options'    => $item->options ?? [],
            ];
        });

        return [
            'order_code'     => $order->order_code,
            'status'         => $this->mapStatus($order->status),
            'total'          => $order->total,
            'delivery_charge'=> $order->delivery_charge,
            'payment_method' => $order->payment_method,
            'address'        => $order->address,
            'mobile'         => $order->mobile,
            'created_at'     => $order->created_at,
            'customer'       => [
                'first_name' => $order->user->name ?? '',
                'last_name'  => '',
                'phone'      => $order->mobile ?? ($order->user->mobile ?? ''),
            ],
            'items'          => $items,
        ];
    }

    private function mapStatus(int $status): string
    {
        return match ($status) {
            OrderStatus::PENDING    => 'PENDING',
            OrderStatus::ACCEPT     => 'CONFIRMED',
            OrderStatus::PROCESS    => 'PREPARED',
            OrderStatus::ASSIGNED   => 'ASSIGNED',
            OrderStatus::ON_THE_WAY => 'HANDOVER',
            OrderStatus::COMPLETED  => 'DELIVERED',
            OrderStatus::REJECT     => 'UNSUPPLIED',
            OrderStatus::CANCEL     => 'CANCELLED',
            default                 => 'UNKNOWN',
        };
    }

    private function send(string $url, ?string $apiToken, array $payload): void
    {
        try {
            $request = Http::timeout(5);

            if ($apiToken) {
                $request = $request->withToken($apiToken);
            }

            $request->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('Outgoing webhook failed', [
                'url'     => $url,
                'payload' => $payload,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}

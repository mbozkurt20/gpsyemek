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
        $targets    = $this->getWebhookTargets($restaurant);

        if (empty($targets)) {
            return;
        }

        $payload = array_merge(['event' => 'new_order'], $this->buildOrderPayload($order));

        foreach ($targets as $target) {
            $this->send($target['url'], $restaurant->api_token, $payload, $target['domain'] ?? null);
        }
    }

    public function sendOrderStatusChanged(Order $order): void
    {
        $restaurant = $order->restaurant;
        $targets    = $this->getWebhookTargets($restaurant);

        if (empty($targets)) {
            return;
        }

        $payload = [
            'event'      => 'order_status_changed',
            'order_code' => $order->order_code,
            'status'     => $this->mapStatus($order->status),
        ];

        foreach ($targets as $target) {
            $this->send($target['url'], $restaurant->api_token, $payload, $target['domain'] ?? null);
        }
    }

    /**
     * Webhook hedeflerini [{url, domain}] formatında döner.
     * Eski format (düz URL dizisi veya tek URL string) geriye dönük desteklenir.
     */
    private function getWebhookTargets($restaurant): array
    {
        if (!$restaurant || blank($restaurant->webhook_url)) {
            return [];
        }

        $decoded = json_decode($restaurant->webhook_url, true);

        // Eski format: tek URL string
        if (!is_array($decoded)) {
            return filter_var($restaurant->webhook_url, FILTER_VALIDATE_URL)
                ? [['url' => $restaurant->webhook_url, 'domain' => null]]
                : [];
        }

        $targets = [];
        foreach ($decoded as $item) {
            // Yeni format: {url, domain}
            if (is_array($item) && !empty($item['url'])) {
                $targets[] = ['url' => $item['url'], 'domain' => $item['domain'] ?? null];
            }
            // Eski format: düz URL string
            elseif (is_string($item) && filter_var($item, FILTER_VALIDATE_URL)) {
                $targets[] = ['url' => $item, 'domain' => null];
            }
        }

        return $targets;
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

    private function send(string $url, ?string $apiToken, array $payload, ?string $domain = null): void
    {
        try {
            // Spatie Multitenancy için domain parametresi payload'a eklenir
            if ($domain) {
                $payload['domain'] = $domain;
            }

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

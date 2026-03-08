<?php

namespace App\Http\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Resources\v1\OrderItemsResource;
use App\Http\Resources\v1\RestaurantResource;
use App\Http\Resources\v1\UserResource;
use App\Models\Order;
use App\Models\Restaurant;
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
     * Domain eksikse bmd-pos /restaurant-get-domain endpoint'inden çeker ve DB'ye kaydeder.
     */
    private function getWebhookTargets($restaurant): array
    {
        if (!$restaurant || blank($restaurant->webhook_url)) {
            return [];
        }

        $decoded = json_decode($restaurant->webhook_url, true);

        // Eski format: tek URL string
        if (!is_array($decoded)) {
            if (!filter_var($restaurant->webhook_url, FILTER_VALIDATE_URL)) {
                return [];
            }
            $decoded = [$restaurant->webhook_url];
        }

        $targets = [];
        $needsSave = false;

        foreach ($decoded as $item) {
            $url    = is_array($item) ? ($item['url'] ?? null) : $item;
            $domain = is_array($item) ? ($item['domain'] ?? null) : null;

            if (blank($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            if (blank($domain)) {
                $domain    = $this->fetchTenantDomain($url, $restaurant->api_token);
                $needsSave = true;
            }

            // Domain çözümlenemezse o hedefi atla (Spatie NoCurrentTenant hatasını önler)
            if (blank($domain)) {
                continue;
            }

            $targets[] = ['url' => $url, 'domain' => $domain];
        }

        // Yeni {url, domain} formatını DB'ye kaydet (bir sonraki seferde fetch gerekmez)
        if ($needsSave && !empty($targets)) {
            $restaurant->webhook_url = json_encode(
                array_map(fn($t) => ['url' => $t['url'], 'domain' => $t['domain']], $targets)
            );
            $restaurant->saveQuietly();
        }

        return $targets;
    }

    /**
     * bmd-pos'un /restaurant-get-domain endpoint'inden tenant domain'ini çeker.
     * Başarısız olursa URL'nin host'una fallback yapar.
     */
    private function fetchTenantDomain(string $webhookUrl, ?string $apiToken): ?string
    {
        $parsed  = parse_url($webhookUrl);
        $baseUrl = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

        if ($apiToken) {
            try {
                $response = Http::timeout(5)->get($baseUrl . '/restaurant-get-domain', [
                    'gpsyemek_api_key' => $apiToken,
                ]);

                if ($response->successful()) {
                    $domain = $response->json();
                    if (is_string($domain) && !blank($domain)) {
                        return $domain;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('GPS Yemek: tenant domain resolve failed', [
                    'url'   => $webhookUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback: URL'nin hostname'ini kullan
        return $parsed['host'] ?? null;
    }

    private function buildOrderPayload(Order $order): array
    {
        $order = Order::where('id',$order->id)->with([
            'items.menuItem', // 'menuItem' modelindeki ilişki adınız neyse o olmalı (product vb.)
            'invoice.transactions',
            'restaurant',
            'user'
        ])->first();
        $restaurant = Restaurant::find($order->restaurant_id);
        return [
            'order_code'     => $order->order_code,
            'status'         => $this->mapStatus($order->status),
            'total'          => $order->total,
            'sub_total'      => $order->sub_total,
            'delivery_charge'=> $order->delivery_charge,
            'payment_method' => $this->mapPaymentMethod($order->payment_method),
            'address'        => $order->address,
            'mobile'         => $order->mobile,
            'created_at'     => $order->created_at,
            'customer'         => new UserResource($order->user),
            'restaurant_id'    => (int)$order->restaurant_id,
            'restaurant'             =>[
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'logo' => $restaurant->logo,
            ],
            'items'           => OrderItemsResource::collection($order->items),
        ];
    }

    private function mapPaymentMethod($method): string
    {
        return match ((int)$method) {
            PaymentMethod::CASH_ON_DELIVERY        => 'Kapıda Nakit Ödeme',
            PaymentMethod::CREDIT_CARD_ON_DELIVERY => 'Kapıda Kart ile Ödeme',
            PaymentMethod::CARD                    => 'Kredi/Banka Kartı',
            PaymentMethod::PAYPAL                  => 'PayPal',
            PaymentMethod::WALLET                  => 'Cüzdan',
            PaymentMethod::CASH                    => 'Nakit',
            PaymentMethod::IYZICO                  => 'iyzico',
            PaymentMethod::TAMI                    => 'Tami',
            PaymentMethod::PAYTR                   => 'PayTR',
            default                                => 'Bilinmeyen Ödeme Yöntemi',
        };
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

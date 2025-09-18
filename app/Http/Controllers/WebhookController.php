<?php

namespace App\Http\Controllers;

use App\Http\Resources\v1\OrderResource;
use App\Http\Resources\v1\RestaurantOrderResource;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Restaurant;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->bearerToken();
        $restaurant = Restaurant::where('api_token', $token)->first();

        if (!$restaurant) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        $event = $data['event'] ?? null;

        switch ($event) {
            case 'get_orders':
                return $this->getOrders($restaurant);
            case 'order_updated':
                return $this->updateOrder($restaurant->id, $data['order']);
            default:
                return response()->json(['error' => 'Unknown event'], 400);
        }
    }

    private function getOrders($restaurant)
    {
        $orders = Order::with('items', 'user')
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('created_at', 'desc')
            ->whereDate('created_at', date('Y-m-d'))
            ->get();

        return response()->json([
            'success' => true,
            'orders' =>RestaurantOrderResource::collection($orders),
        ]);
    }

    private function updateOrder($restaurant_id, $data)
    {
        $order = Order::where('id', $data['id'])
            ->where('restaurant_id', $restaurant_id)
            ->first();

        if ($order) {
            $order->status = $data['status'];
            $order->update();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }
}

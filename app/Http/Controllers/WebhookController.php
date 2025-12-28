<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
                return $this->updateOrder($restaurant->id, $data);
            case 'restaurant_status_changed':
                return $this->restaurantOrderChange($restaurant->id);
            default:
                return response()->json(['error' => 'Unknown event'], 400);
        }
    }

    private function getOrders($restaurant)
    {
        $orders = Order::with('items', 'user')
            ->where('restaurant_id', $restaurant->id)
            ->where('status', OrderStatus::PENDING)
            ->orderBy('created_at', 'desc')
            ->whereDate('created_at', date('Y-m-d'))
            ->get();

        return response()->json([
            'success' => true,
            'orders' => RestaurantOrderResource::collection($orders),
        ]);
    }

    private function updateOrder($restaurant_id, $data)
    {
        $order = Order::where('restaurant_id', $restaurant_id)
            ->where('misc->order_code', $data['order_code'])
            ->first();


        switch ($data['status']){
            case 'PREPARED':
                $orderStatus = OrderStatus::ACCEPT;
                break;

            case 'ASSIGNED':
                $orderStatus = OrderStatus::ON_THE_WAY;
                break;

            case 'DELIVERED':
                $orderStatus = OrderStatus::COMPLETED;
                break;
        }

        if ($order) {
            $order->status = $orderStatus;
            $order->update();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'error' => 'Order not found'], 404);
        }
    }

    private function restaurantOrderChange($restaurant_id){

        $restaurant = Restaurant::where('id',$restaurant_id)->first();

        if (!$restaurant){
            return response()->json(['error' => 'Restaurant not found'], 404);
        }

        $restaurant->current_status = $restaurant->current_status == 5 ? 0 : 5;
        $restaurant->update();

        return response()->json(['success' => true,'restaurant' => $restaurant]);
    }
}

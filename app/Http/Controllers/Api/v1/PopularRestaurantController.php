<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\CurrentStatus;
use App\Enums\RestaurantStatus;
use App\Helpers\MapHelper;
use App\Helpers\OrdersHelper;
use App\Helpers\RestaurantHelper;
use App\Http\Resources\v1\PopularRestaurantResource;
use App\Models\Restaurant;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\BackendController;

class PopularRestaurantController extends BackendController
{
    use ApiResponse;
/*
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');

    }
/*
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $current_time = now()->format('H:i');
        $latitude = $request->lat;
        $longitude = $request->long;
        $radius = 20;

        $query = Restaurant::leftJoin('orders', 'restaurants.id', '=', 'orders.restaurant_id')
            ->select(
                'restaurants.id',
                'restaurants.name',
                'restaurants.slug',
                'restaurants.description',
                'restaurants.address',
                'restaurants.lat',
                'restaurants.long',
                'restaurants.permanently_closed',
                'restaurants.temporary_closed_until'
            )
            ->selectRaw('count(orders.id) as orders_count')
            ->where('restaurants.status', RestaurantStatus::ACTIVE)
            ->where('restaurants.current_status', CurrentStatus::YES)
            ->groupBy(
                'restaurants.id',
                'restaurants.name',
                'restaurants.slug',
                'restaurants.description',
                'restaurants.address',
                'restaurants.lat',
                'restaurants.long',
                'restaurants.permanently_closed',
                'restaurants.temporary_closed_until'
            )
            ->with('timeSlots');

        // Lat ve Long gelmişse mesafe hesaplamasını ve filtresini ekle
        $query->when($latitude && $longitude, function ($q) use ($latitude, $longitude, $radius) {
            $q->selectRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(restaurants.lat)) * cos(radians(restaurants.long) - radians(?)) + sin(radians(?)) * sin(radians(restaurants.lat)))) AS distance",
                [$latitude, $longitude, $latitude]
            )->having('distance', '<=', $radius);
        });

        $bestSellingRestaurants = $query->orderBy('orders_count', 'desc')->get()
            ->sortByDesc(fn($r) => RestaurantHelper::getStatus($r) === 'open' ? 1 : 0)
            ->values();

        try {
            return $this->successResponse([
                'status' => 200,
                'data' => PopularRestaurantResource::collection($bestSellingRestaurants)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

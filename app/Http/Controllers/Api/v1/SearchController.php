<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\CurrentStatus;
use App\Http\Resources\v1\PopularRestaurantResource;
use App\Models\TimeSlot;
use App\Enums\TableStatus;
use App\Models\Restaurant;
use App\Enums\PickupStatus;
use App\Enums\RatingStatus;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Enums\DeliveryStatus;
use App\Enums\RestaurantStatus;
use App\Models\RestaurantRating;
use App\Http\Services\RatingsService;
use App\Http\Services\RestaurantService;
use App\Http\Resources\v1\RatingResource;
use App\Helpers\RestaurantHelper;
use App\Http\Controllers\BackendController;
use App\Http\Resources\v1\MenuItemResource;
use App\Http\Resources\v1\RestaurantResource;


class SearchController extends BackendController
{
    use ApiResponse;
    protected  $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Restaurants';
        $this->restaurantService = $restaurantService;
    }
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $name = null;
        if (!blank($request->get('name'))) {
            $name = $request->get('name');
        }

        $expedition = null;
        if (!blank($request->get('expedition'))) {
            $expedition = $request->get('expedition');
        }

        $latitude  = $request->get('lat');
        $longitude = $request->get('long');
        $radius    = 20;

        try {
            $restaurants = $this->getallrestaurant($name, $expedition, $latitude, $longitude, $radius);
            return $this->successResponse(['status' => 200, 'data' => PopularRestaurantResource::collection($restaurants)]);
        } catch (\Exception $e) {
            return response()->json([
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }


    public function getallrestaurant($name, $expedition, $latitude = null, $longitude = null, $radius = 20)
    {
        $queryArray = [];
        $queryArray['status']         = RestaurantStatus::ACTIVE;
        $queryArray['current_status'] = CurrentStatus::YES;

        if (!blank($expedition)) {
            if ($expedition == 'delivery') {
                $queryArray['delivery_status'] = DeliveryStatus::ENABLE;
            } elseif ($expedition == 'pickup') {
                $queryArray['pickup_status'] = PickupStatus::ENABLE;
            } elseif ($expedition == 'table') {
                $queryArray['table_status'] = TableStatus::ENABLE;
            }
        }

        $today = now()->toDateString();

        $query = Restaurant::withCount('orders')->with(['timeSlots', 'coupons' => function ($q) use ($today) {
            $q->whereDate('from_date', '<=', $today)
              ->whereDate('to_date', '>=', $today)
              ->where('limit', '>', 0);
        }]);

        if (!blank($name) && !blank($expedition)) {
            $query->where($queryArray)->where('name', 'like', '%' . $name . '%');
        } elseif (!blank($expedition)) {
            $query->where($queryArray);
        } elseif (!blank($name)) {
            $query->where('name', 'like', '%' . $name . '%');
        } else {
            $query->where($queryArray);
        }

        if (is_numeric($latitude) && is_numeric($longitude)) {
            $distanceRaw = "(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(`long`) - radians(?)) + sin(radians(?)) * sin(radians(lat))))";
            $query->whereRaw("$distanceRaw <= ?", [$latitude, $longitude, $latitude, $radius])
                  ->orderByRaw("$distanceRaw ASC", [$latitude, $longitude, $latitude]);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->get()
            ->sortBy([
                fn($a, $b) => (RestaurantHelper::getStatus($b) === 'open') <=> (RestaurantHelper::getStatus($a) === 'open'),
                fn($a, $b) => $b->coupons->isNotEmpty() <=> $a->coupons->isNotEmpty(),
                fn($a, $b) => $b->orders_count <=> $a->orders_count,
            ])
            ->values();
    }
}

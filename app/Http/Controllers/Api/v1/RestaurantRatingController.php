<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\OrderStatus;
use App\Enums\RatingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\RatingResource;
use App\Http\Services\RatingsService;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantRating;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RestaurantRatingController extends Controller
{
    use ApiResponse;

    // Siparişin tamamlanmasından sonra yorum yapılabilmesi için gereken minimum süre (gün)
    const REVIEW_MIN_DAYS = 0;
    // Siparişin tamamlanmasından sonra yorum yapılabilecek maksimum süre (gün)
    const REVIEW_MAX_DAYS = 7;

    public function __construct()
    {
        $this->middleware('auth:api')->only('store');
    }

    /**
     * Restorana ait tüm aktif yorumları ve ortalama puanı döner.
     */
    public function index($restaurantId)
    {
        if (!Restaurant::find($restaurantId)) {
            return response()->json(['status' => 404, 'message' => 'Restoran bulunamadı.'], 404);
        }

        $ratings = RestaurantRating::where([
            'restaurant_id' => $restaurantId,
            'status'        => RatingStatus::ACTIVE,
        ])->with('user')->get();

        $ratingService = new RatingsService();
        $ratingArray   = $ratingService->avgRating($restaurantId);

        return $this->successResponse([
            'status'    => 200,
            'avgRating' => $ratingArray['avgRating'],
            'countUser' => $ratingArray['countUser'],
            'data'      => RatingResource::collection($ratings),
        ]);
    }

    /**
     * Kullanıcının restorana sipariş bazlı yorum ve puan eklemesini sağlar.
     *
     * Kurallar:
     * - Restoran var olmalı
     * - order_id verilmişse: sipariş kullanıcıya ve restorana ait olmalı, tamamlanmış olmalı
     * - Aynı sipariş için daha önce yorum yapılmamış olmalı
     * - Sipariş tamamlanma tarihinden itibaren REVIEW_MAX_DAYS gün içinde yorum yapılabilir
     */
    public function store(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::find($restaurantId);
        if (!$restaurant) {
            return response()->json(['status' => 404, 'message' => 'Restoran bulunamadı.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating'   => 'required|numeric|min:1|max:5',
            'review'   => 'required|string|max:500',
            'order_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()], 422);
        }

        $userId  = auth()->id();
        $orderId = $request->order_id;

        if ($orderId) {
            // Siparişi kontrol et
            $order = Order::where([
                'id'            => $orderId,
                'user_id'       => $userId,
                'restaurant_id' => $restaurantId,
                'status'        => OrderStatus::COMPLETED,
            ])->first();

            if (!$order) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'Bu sipariş bulunamadı veya tamamlanmamış.',
                ], 422);
            }

            // Sipariş tamamlanma süresi kontrolü (max REVIEW_MAX_DAYS gün)
            $completedAt = $order->updated_at;
            $daysPassed  = $completedAt->diffInDays(now());

            if ($daysPassed > self::REVIEW_MAX_DAYS) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'Bu sipariş için yorum yapma süresi dolmuş (' . self::REVIEW_MAX_DAYS . ' gün).',
                ], 422);
            }

            // Aynı sipariş için daha önce yorum yapılmış mı?
            $existingByOrder = RestaurantRating::where('order_id', $orderId)->first();
            if ($existingByOrder) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'Bu sipariş için daha önce yorum yapılmış.',
                ], 422);
            }

            // Bu sipariş için yeni yorum oluştur
            $rating = RestaurantRating::create([
                'user_id'       => $userId,
                'restaurant_id' => $restaurantId,
                'order_id'      => $orderId,
                'rating'        => $request->rating,
                'review'        => $request->review,
                'status'        => RatingStatus::ACTIVE,
            ]);
        } else {
            // order_id yoksa kullanıcının bu restorana önceki yorumu güncellenir
            $rating = RestaurantRating::updateOrCreate(
                ['user_id' => $userId, 'restaurant_id' => $restaurantId, 'order_id' => null],
                ['rating' => $request->rating, 'review' => $request->review, 'status' => RatingStatus::ACTIVE]
            );
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Değerlendirmeniz başarıyla kaydedildi.',
            'data'    => new RatingResource($rating),
        ], 200);
    }
}

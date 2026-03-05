<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\BannerStatus;
use App\Enums\CouponType;
use App\Enums\DiscountStatus;
use App\Enums\DiscountType;
use App\Http\Controllers\BackendController;
use App\Http\Requests\BannerRequest;
use App\Http\Resources\v1\BannerResource;
use App\Models\Coupon;
use App\Models\Discount;
use App\Traits\ApiResponse;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CouponController extends BackendController
{
    use ApiResponse;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply(Request $request)
    {
        $msg = '';
        $discountAmount = 0;
        $couponID = 0;

        $today = Carbon::now()->format('Y-m-d H:i:s');
        $total_amount = $request->total_amount;
        $restaurant_id = $request->restaurant_id;

        $coupon = Coupon::where('slug', $request->coupon)->first();

        if (!blank($coupon)) {
            $total_used = Discount::where('coupon_id', $coupon->id)
                ->where('status', DiscountStatus::ACTIVE)
                ->count();

            $user_limit = Discount::where([
                'coupon_id' => $coupon->id,
                'user_id' => auth()->id() ?? 0
            ])
                ->where('status', DiscountStatus::ACTIVE)
                ->count();

            if ($coupon->coupon_type == CouponType::VOUCHER && $coupon->restaurant_id != $restaurant_id) {
                $msg = 'Bu kupon bu restoran için geçerli değil';
            } elseif ($total_used >= $coupon->limit) {
                $msg = 'Tutar limitten eşit ve büyük olamaz';
            } elseif (!(($coupon->to_date >= $today) && ($coupon->from_date <= $today))) {
                $msg = 'Bu Kuponun Süresi Doldu';
            } elseif ($user_limit >= $coupon->user_limit) {
                $msg = 'Limitiniz kupon limitinden büyük olamaz';
            } elseif ($total_amount < $coupon->minimum_order_amount) {
                $msg = 'Bu Kupon için Minimum Sipariş Tutarı ' . currencyFormat($coupon->minimum_order_amount);
            } elseif ($coupon->user_id != null && $coupon->user_id != auth()->id()) {
                $msg = 'Üzgünüz, bu kupon sadece belirli müşteriler için geçerlidir.';
            }
        } else {
            $msg = 'Bu Kupon Geçersiz';
        }

        if (blank($msg) && !blank($coupon)) {
            $couponID = $coupon->id;

            if ($coupon->discount_type == DiscountType::FIXED) {
                $discountAmount = round($coupon->amount);
            } else {
                $discountAmount = round(($total_amount * $coupon->amount) / 100);
            }

            if ($coupon->user_id != null) {
                $coupon->delete();
            }

            return $this->successresponse([
                'success' => true,
                'coupon_id' => $couponID,
                'discount_amount' => $discountAmount,
                'new_total' => $total_amount - $discountAmount,
                'msg' => 'Kupon başarıyla uygulandı'
            ], 200);
        }

        return $this->successresponse([
            'success' => false,
            'msg' => $msg,
            'discount_amount' => 0
        ], 422);
    }
}

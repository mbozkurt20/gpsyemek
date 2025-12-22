<?php

namespace App\Http\Services;

use App\Enums\OrderTypeStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Restaurant;
use Opcodes\LogViewer\Logs\Log;

class PaymentService
{
    public $data = array();

    public function payment($paymentSuccess)
    {
        $restaurantId = session('session_cart_restaurant_id');
        $restaurant = Restaurant::find($restaurantId);
        $request = session()->get('checkoutRequest');

        $cart = session()->get('cart-'.$restaurantId);

        if (($cart && isset($cart['delivery_type']) && $cart['delivery_type'] == true) || ($cart && isset($cart['free_delivery']) && $cart['free_delivery'])) {
            $delivery_charge = 0;
            $order_type = OrderTypeStatus::PICKUP;
        } else {
            $delivery_charge = session()->get('delivery_charge');
            $order_type = OrderTypeStatus::DELIVERY;
        }

        $items = [];

        $cartItems = $cart['items'] ?? [];

        foreach ($cartItems as $ct) {
            $menuItemVariationId = $ct['variation']['id'] ?? null;
            $variation = $ct['variation'] ?? null;
            $options = $ct['options'] ?? null;
            $instructions = $ct['instructions'] ?? null;

            $items[] = [
                'restaurant_id' => $restaurant->id,
                'menu_item_variation_id' => $menuItemVariationId,
                'menu_item_id' => $ct['menuItem_id'],
                'unit_price' => (float) $ct['price'],
                'quantity' => (int) $ct['qty'],
                'discounted_price' => (float) $ct['discount'],
                'variation' => $variation,
                'options' => $options,
                'instructions' => $instructions,
            ];
        }

        \Illuminate\Support\Facades\Log::info('girdi');

        \Illuminate\Support\Facades\Log::info('request payment type', ['request' => $request]);
        \Illuminate\Support\Facades\Log::info('cart 1', ['cart 1' => $cart]);
        if ($request['payment_type'] == PaymentMethod::STRIPE && $paymentSuccess) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
        } elseif ($request['payment_type'] == PaymentMethod::IYZICO) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
            $this->data['payment_id'] = session()->get('paymentId');
        } elseif ($request['payment_type'] == PaymentMethod::PAYTR) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
            $this->data['payment_id'] = session()->get('paymentId');
        } elseif ($request['payment_type'] == PaymentMethod::TAMI) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
        }elseif ($request['payment_type'] == PaymentMethod::PAYTM) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
        } elseif ($request['payment_type'] == PaymentMethod::PHONEPE) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
        } elseif ($request['payment_type'] == PaymentMethod::WALLET) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
        } elseif ($request['payment_type'] == PaymentMethod::PAYSTACK && $paymentSuccess) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
        } elseif ($request['payment_type'] == PaymentMethod::PAYPAL && $paymentSuccess) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
        } elseif ($request['payment_type'] == PaymentMethod::RAZORPAY && $paymentSuccess) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
        } elseif ($request['payment_type'] == PaymentMethod::SSLCOMMERZ) {
            $this->data['paid_amount'] = $cart['totalAmount'] + $delivery_charge;
            $this->data['payment_method'] = $request['payment_type'];
            $this->data['payment_status'] = PaymentStatus::PAID;
        } elseif ($request['payment_type'] == PaymentMethod::CREDIT_CARD_ON_DELIVERY) {
            $this->data['paid_amount'] = 0;
            $this->data['payment_method'] = PaymentMethod::CREDIT_CARD_ON_DELIVERY;
            $this->data['payment_status'] = PaymentStatus::UNPAID;
        }else {
            $this->data['paid_amount'] = 0;
            $this->data['payment_method'] = PaymentMethod::CASH_ON_DELIVERY;
            $this->data['payment_status'] = PaymentStatus::UNPAID;
        }

        $this->data['coupon_id'] = isset($cart['couponID']) ? $cart['couponID'] : null;
        $this->data['coupon_amount'] = isset($cart['coupon_amount']) ? $cart['coupon_amount'] : null;

        $this->data['items'] = $items;
        $this->data['order_type'] = $order_type;
        $this->data['restaurant_id'] = session('session_cart_restaurant_id');
        $this->data['user_id'] = auth()->user()->id;
        $this->data['total'] = isset($cart['totalAmount']) ? $cart['totalAmount'] : 0;
        $this->data['delivery_charge'] = $delivery_charge;
        $this->data['address'] = isset($request['address']) ? $request['address'] : '';
        $this->data['mobile'] = $request['countrycode'] . $request['mobile'];

        $orderService = app(OrderService::class)->order($this->data);

        return $orderService;
    }
}

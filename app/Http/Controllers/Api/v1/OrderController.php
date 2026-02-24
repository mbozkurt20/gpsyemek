<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Services\PushNotificationService;
use App\Models\MenuItemOption;
use App\Models\Restaurant;
use Carbon\Carbon;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Traits\ApiResponse;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use App\Models\OrderLineItem;
use App\Enums\OrderTypeStatus;
use App\Models\MenuItemVariation;
use App\Http\Services\FileService;
use App\Http\Services\OrderService;
use App\Notifications\OrderCreated;
use App\Notifications\OrderUpdated;
use App\Helpers\RestaurantHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Http\Resources\v1\UserResource;
use App\Http\Resources\v1\OrderResource;
use App\Http\Services\TransactionService;
use Illuminate\Support\Facades\Validator;
use App\Notifications\NewShopOrderCreated;
use App\Http\Resources\v1\OrderApiResource;
use App\Http\Requests\Api\OrderStoreRequest;

class OrderController extends Controller
{
    use ApiResponse;
    public $adminBalanceId = 1;
    /**
     * OrderController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    public function index()
    {
        $response = Order::where(['user_id' => auth()->user()->id])->orderBy('id', 'desc')->with('items')->get();
        $response->map(function ($post) {
            $post['status_name']         = trans('order_status.' . $post->status);
            $post['order_code']          = $post->order_code;
            $post['address']             = orderAddress($post->address);
            $post['order_type']          =  (int)$post->order_type;
            $post['order_type_name']     =  $post->getOrderType;
            $post['payment_method_name'] = trans('payment_method.' . $post->payment_method);
            $post['created_at_convert']  = food_date_format($post->created_at);
            $post['updated_at_convert']  = food_date_format($post->updated_at);
            $post['deliveryBoy']         = $post->delivery_boy_id == null?null:new UserResource($post->delivery);

            foreach ($post['items'] as $itemKey => $item) {
                $post['items'][$itemKey]['created_at_convert'] = food_date_format($post->created_at);
                $post['items'][$itemKey]['updated_at_convert'] = food_date_format($post->updated_at);
                $post['items'][$itemKey]['menuItem']['image']  = $item['menuItem']->image;
            }
            return $post;
        });

        return new OrderResource($response);
    }

    public function show($id)
    {
        try{
            $response = Order::where(['id' => $id, 'user_id' => auth()->user()->id])->latest()->with('items', 'invoice.transactions')->first();

            if($response == null){
                return $this->successResponse(['status'=> 200, 'message' => 'No available orders']);
            }
            $order= new OrderApiResource($response);
            return $this->successResponse(['status'=> 200, 'data' => $order]);
        } catch (\Exception $e){
            return response()->json([
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->phone == null || $user->phone_verify == false) {
            return response()->json([
                'status'  => 401,
                'message' => 'Lütfen önce telefon numaranızı profil sayfasından ekleyiniz.',
            ], 401);
        }

        $validator = new OrderStoreRequest();
        $validator = Validator::make($request->all(), $validator->rules());

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()], 422);
        }

        $restaurant = Restaurant::with('timeSlots')->find($request->restaurant_id);

        if (!$restaurant) {
            return response()->json(['status' => 400, 'message' => 'Restoran Bulunamadı'], 400);
        }

        if (RestaurantHelper::getStatus($restaurant) === 'closed') {
            return response()->json([
                'status'  => 400,
                'message' => RestaurantHelper::getStatusMessage($restaurant),
            ], 400);
        }

        // JSON Decode
        $orderItems = is_string($request->items) ? json_decode($request->items) : $request->items;

        $items = [];
        if (!blank($orderItems)) {
            foreach ($orderItems as $item) {
                $selectedOptions = [];

                // Opsiyon ID'lerini alıyoruz (Service tarafında detaylı kontrol yapıldığı için burada sadece ID gönderiyoruz)
                // Eğer Service tarafında ID listesi bekliyorsan böyle kalsın:
                $selectedOptions = isset($item->options) ? (array)$item->options : [];

                $items[] = [
                    'restaurant_id'    => $request->restaurant_id,
                    'menu_item_id'     => $item->menuItem_id, // SERVICE TARAFINDAKİYLE AYNI YAPTIK
                    'unit_price'       => (float) $item->unit_price,
                    'quantity'         => (int) $item->quantity,
                    'discounted_price' => (float) $item->discounted_price,
                    'options'          => $selectedOptions,
                    'instructions'     => $item->instructions ?? '',
                ];
            }
        }

        // Ödeme Mantığı Düzenleme
        $paymentMethod = $request->payment_method ?? PaymentMethod::CASH_ON_DELIVERY;
        $paymentStatus = PaymentStatus::UNPAID;
        $paidAmount    = 0;

        if ($request->paid_amount > 0 && $paymentMethod != PaymentMethod::CASH_ON_DELIVERY) {
            $paymentMethod = $request->payment_method; // Veya $request->payment_type hangisini gönderiyorsan
            $paymentStatus = PaymentStatus::PAID;
            $paidAmount    = $request->paid_amount;
        }

        // Request verilerini merge ederek Service'e hazır hale getiriyoruz
        $request->merge([
            'items'           => $items,
            'user_id'         => $user->id,
            'payment_method'  => $paymentMethod,
            'payment_status'  => $paymentStatus,
            'paid_amount'     => $paidAmount,
        ]);

        // Service çağrısı (Service array bekliyor demiştin, o yüzden ->all() gönderiyoruz)
        $orderService = app(OrderService::class)->order($request->all());

        if ($orderService->status) {
            $order = Order::find($orderService->order_id);

            try {
                app(PushNotificationService::class)->NotificationForRestaurant($order, $order->restaurant->user, 'restaurant');
                app(PushNotificationService::class)->NotificationForCustomer($order,  $order->user, 'customer');
            } catch (\Exception $exception) {
                // Loglanabilir
            }

            return response()->json([
                'status'  => 200,
                'message' => 'Siparişiniz Başarıyla Alındı.',
                'data'    => $this->orderResponse($order),
            ], 200);
        }

        return response()->json([
            'status'  => 401,
            'message' => $orderService->message,
        ], 401);
    }

    private function orderResponse($order)
    {
        return ['order_id' => $order->id, 'total_amount' => $order->total ];
    }

    private function createShow($id)
    {
        $response = Order::where(['id' => $id, 'user_id' => auth()->user()->id])->latest()->with('items', 'invoice.transactions')->first();

        $response->setAttribute('status_name', trans('order_status.' . $response->status));
        $response->setAttribute('created_at_convert', $response->created_at->format('d-m-Y H:i:s'));
        $response->setAttribute('updated_at_convert', $response->updated_at->format('d-m-Y H:i:s'));
        $response->setAttribute('attachment', $response->image);

        if (isset($response['invoice'])) {
            $response['invoice']['created_at_convert'] = food_date_format($response['invoice']->created_at);
            $response['invoice']['updated_at_convert'] = food_date_format($response['invoice']->updated_at);
        }

        if (isset($response['invoice']) && isset($response['invoice']['transactions'])) {
            foreach ($response['invoice']['transactions'] as $transactionKey => $transaction) {
                $response['invoice']['transactions'][$transactionKey]['created_at_convert'] = food_date_format($transaction->created_at);
                $response['invoice']['transactions'][$transactionKey]['updated_at_convert'] = food_date_format($transaction->updated_at);
            }
        }

        if (isset($response['items'])) {
            foreach ($response['items'] as $itemKey => $item) {
                $response['items'][$itemKey]['created_at_convert'] = food_date_format($item->created_at);
                $response['items'][$itemKey]['updated_at_convert'] = food_date_format($item->updated_at);
                $response['items'][$itemKey]['options']            = json_decode($item->options);
                $response['items'][$itemKey]['product']['image']   = $item['product']->images ?? '';
                unset($response['items'][$itemKey]['product']['media']);
            }
        }
        return $response;
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, $id)
    {
        $status = [
            OrderStatus::CANCEL => 'Cancel'
        ];

        if((int) $id) {
            $order = Order::find($id);
            if(!blank($order)) {
                if(isset($status[$request->status])) {
                    $orderService = app(OrderService::class)->orderUpdate($id, $request->status);

                    if($orderService->status) {
                        try {
                            app(PushNotificationService::class)->sendNotificationOrderUpdate($order, $order->user,'customer');
                        } catch(\Exception $e) {

                        }
                        return response()->json([
                            'status'  => 200,
                            'message' => 'Siparişiniz başarıyla güncellendi.',
                            'data'    => $orderService
                        ], 200);
                    } else {
                        return response()->json([
                            'status'  => 422,
                            'message' => $orderService->message
                        ], 422);
                    }
                } else {
                    return response()->json([
                        'status'  => 422,
                        'message' => 'Durum bulunamadı',
                    ], 422);
                }
            } else {
                return response()->json([
                    'status'  => 422,
                    'message' => 'Sipariş bulunamadı',
                ], 422);
            }
        } else {
            return response()->json([
                'status'  => 422,
                'message' => 'Sipariş ID bulunamadı',
            ], 422);
        }
    }

    public function orderPayment(Request $request)
    {
        if ( (int)$request->order_id ) {
            $order = Order::find($request->order_id);
            if ( !blank($order) ) {
                if($request->payment_method != PaymentMethod::CASH_ON_DELIVERY && $order->payment_status != PaymentStatus::PAID) {
                    if ( $request->payment_method != PaymentMethod::WALLET) {
                        app(TransactionService::class)->addFund(0, $order->user->balance_id, $order->payment_method, $order->total, $order->id);
                    }
                    if ( $this->adminBalanceId != $order->user->balance_id ) {
                        app(TransactionService::class)->payment($order->user->balance_id, $this->adminBalanceId, $order->total, $order->id);
                    }

                    $order->paid_amount    = $order->total;
                    $order->payment_method = $request->payment_method;
                    $order->payment_status = PaymentStatus::PAID;
                    $order->save();
                    return response()->json([
                        'status'  => 200,
                        'message' => 'Ödeme başarıyla tamamlandı',
                    ], 200);
                } else {
                    return response()->json([
                        'status'  => 422,
                        'message' => 'Lütfen doğru ödeme yöntemini seçin',
                    ], 422);
                }
            } else {
                return response()->json([
                    'status'  => 422,
                    'message' => 'Sipariş bulunamadı',
                ], 422);
            }
        } else {
            return response()->json([
                'status'  => 422,
                'message' => 'Sipariş ID bulunamadı',
            ], 422);
        }
    }

    public function orderCancel( $id )
    {
        if ( $id ) {
            $order = Order::where([
                'user_id' => auth()->id(),
                'status'  => OrderStatus::PENDING
            ])->find($id);
            if ( !blank($order) ) {
                $orderService = app(OrderService::class)->cancel($id);
                if ( $orderService->status ) {
                    try {
                        app(PushNotificationService::class)->sendNotificationOrderUpdate($order, $order->user,'customer');

                    } catch(\Exception $e) {

                    }
                    return response()->json([
                        'status'  => 200,
                        'message' => 'You order cancel successfully',
                    ], 200);
                } else {
                    return response()->json([
                        'status'  => 422,
                        'message' => $orderService->message
                    ], 422);
                }
            } else {
                return response()->json([
                    'status'  => 422,
                    'message' => 'The order not found',
                ], 422);
            }
        } else {
            return response()->json([
                'status'  => 422,
                'message' => 'The order id not found',
            ], 422);
        }
    }

    public function attachment($id)
    {
        $order = Order::where(['id' => $id, 'user_id' => auth()->user()->id])->first();
        if (!blank($order)) {
            return response()->json([
                'data'    => $order->image,
                'status'  => 200,
                'message' => 'Success',
            ], 200);
        }
        return response()->json([
            'status'  => 401,
            'message' => 'Bad Request',
        ], 401);
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use Anand\LaravelPaytmWallet\Facades\PaytmWallet;
use App\Enums\PaymentMethod;
use App\Http\Controllers\FrontendController;
use App\Http\Services\PaymentService;
use App\Http\Services\PushNotificationService;
use App\Http\Services\StripeService;
use App\Models\Address;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\PayTrService;
use Dipesh79\LaravelPhonePe\LaravelPhonePe;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Iyzipay\Model\CheckoutForm;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Options;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use Paystack;
use Razorpay\Api\Api;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Iyzipay\Model\Payment;

class CheckoutController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['site_title'] = 'Checkout';
    }

    public function index()
    {
        $restaurant = Restaurant::find(session('session_cart_restaurant_id'));
        $cart = session()->get('cart-' . $restaurant->id);

        if (blank($cart)) {
            return redirect('/');
        }

        $this->data['addresses'] = Address::where('user_id', auth()->user()->id)->get();
        $this->data['lastAddress'] = '';

        $lastAddress = Order::select('address')->where('user_id', auth()->user()->id)->latest()->first();
        if (!blank($lastAddress)) {
            if (isJson($lastAddress->address)) {
                $this->data['lastAddress'] = Address::where('address', json_decode($lastAddress->address, true)['address'])->first();
            }
        }

        if (blank($this->data['lastAddress'])) {
            $this->data['lastAddress'] = Address::where('user_id', auth()->user()->id)->first();
        }


        $this->data['menuitems'] = session()->get('cart-' . $restaurant->id);
        $this->data['totalPayment'] = session()->get('cart-' . $restaurant->id)['totalPayAmount'];
        $this->data['restaurant'] = $restaurant;

        return view('frontend.restaurant.checkout', $this->data);
    }

    public function store(Request $request)
    {
        $sessionRestaurantId = session('session_cart_restaurant_id');
        if (blank($sessionRestaurantId)) {
            return redirect(route('checkout.index'))->withError('Restaurant Bulunmuyor');
        }

        $this->setDeliveryCharge($request);
        $restaurant = Restaurant::find($sessionRestaurantId);
        $validator = $this->validateCheckoutRequest($request, $restaurant);

        $cart = session()->get('cart-' . $sessionRestaurantId);
        if (!$cart || !isset($cart['delivery_type'])) {
            $validation = [
                'mobile' => 'required',
                'address' => 'required|string',
                'payment_type' => 'required|numeric',
            ];
        } else {
            $validation = [
                'mobile' => 'required',
                'payment_type' => 'required|numeric',
            ];
        }

        $messages = [
            'mobile.required' => 'Telefon numarası alanı zorunludur.',
        ];

        $validator = Validator::make($request->all(), $validation, $messages);
        $validator->after(function ($validator) use ($request, $restaurant, $cart) {
            if ($request->payment_type == PaymentMethod::WALLET) {
                if ((float)auth()->user()->balance->balance < (float)($cart['totalAmount'] + session()->get('delivery_charge'))) {
                    $validator->errors()->add('payment_type', 'The Credit balance does not enough for this payment.');
                }
            }
        })->validate();

        if ($validator->fails()) {
            return redirect(route('checkout.index'))->withError($validator);
        }

        if (auth()->check()) {
            session()->put('checkoutRequest', $request->all());
            $paymentType = $request->payment_type;
            if ($paymentType == PaymentMethod::STRIPE) {
                return $this->processStripePayment($restaurant);
            } elseif ($paymentType == PaymentMethod::PAYSTACK) {
                return $this->preparePaystackPaymentData($request);
            } elseif ($paymentType == PaymentMethod::PAYTM) {
                return $this->payWithPaytm($request);
            } elseif ($paymentType == PaymentMethod::PHONEPE) {
                return $this->phonePePayment($request);
            } elseif ($paymentType == PaymentMethod::PAYPAL) {
                return $this->initiatePaypalPayment();
            } elseif ($paymentType == PaymentMethod::SSLCOMMERZ) {
                return $this->sslcommerzPayment($request);
            } elseif ($paymentType == PaymentMethod::RAZORPAY) {
                return $this->processRazorpayPayment($request);
            } elseif ($paymentType == PaymentMethod::IYZICO) {
                return $this->iyzicoPayment($request);
            } elseif ($paymentType == PaymentMethod::PAYTR) {
                return $this->payTrPayment($request);
            } elseif ($paymentType == PaymentMethod::TAMI) {
                return $this->tamiPayment($request);
            } else {
                return $this->processDefaultPayment();
            }
        } else {
            return redirect()->route('login');
        }
    }

    public function payTrPayment(Request $r)
    {
        $restaurantId = session()->get('session_cart_restaurant_id');
        $request = session()->get('checkoutRequest');
        $cart = session()->get('cart-' . $restaurantId);

        $basket = [
            ['total', $cart['totalAmount'], 1],
            ['delivery', session()->get('delivery_charge'), 1],
        ];

        $address = Address::find($r->address);
        $phone = str_replace('-', '', $r->mobile);
        $name = auth()->user()->first_name . ' ' . auth()->user()->last_name;
        $email = auth()->user()->email;

        $amount = ($cart['totalAmount'] + session()->get('delivery_charge')) * 100;
        $merchant_oid = uniqid();

        Cache::put("paytr_payment_{$merchant_oid}", [
            'payment_type' => PaymentMethod::PAYTR,
            'user_id' => auth()->id(),
            'countrycode' => $r->countrycode,
            'mobile' => $r->phone,
            'cart' => $cart,
            'request' => $request,
        ], now()->addMinutes(10));

        $paytr = new PaytrService();
        $result = $paytr->getToken($name, $address->address, $phone, $email, $amount, $basket, $merchant_oid); // 50.00 TL

        if ($result['status'] === 'success') {
            return view('frontend.payment.paytr', ['token' => $result['token']]);
        } else {
            return back()->withErrors($result['reason']);
        }
    }

    public function paytrCallback(Request $request)
    {
        $merchantOid  = $_POST['merchant_oid'] ?? null;
        $status       = $_POST['status'] ?? null;
        $totalAmount  = $_POST['total_amount'] ?? null;
        $hashPost     = $_POST['hash'] ?? null;

        $hash = base64_encode(hash_hmac(
            'sha256',
            $merchantOid .
            $status .
            $totalAmount .
            setting('paytr_merchant_salt'),
            setting('paytr_merchant_key'),
            true
        ));


        if ($hash !== $hashPost) {
            Log::error('PayTR bad hash', $_POST);
            return response('PAYTR notification failed: bad hash', 400);
        }

        return response('OK');
    }

    public function payTrSuccess(Request $request)
    {
        if (
            !session()->has('checkoutRequest') ||
            !session()->has('session_cart_restaurant_id')
        ) {
            Log::warning('payTrSuccess called without session');
            return redirect()->route('account.order');
        }

        $orderService = app(PaymentService::class)->payment(true);
        return $this->handleOrderServiceResponse($orderService);
    }

    public function payTrFail($merchantOid)
    {
        $orderService = app(PaymentService::class)->payment(false);
        return $this->handleOrderServiceResponse($orderService);
    }

    public function iyzicoPayment(Request $r)
    {
        $options = new Options();
        $options->setApiKey(setting('iyzico_api_key'));
        $options->setSecretKey(setting('iyzico_secret_key'));
        $options->setBaseUrl(config('iyzico.base_url'));

        $amount = 1;

        $paymentRequest = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
        $paymentRequest->setLocale(\Iyzipay\Model\Locale::TR);
        $paymentRequest->setConversationId(uniqid());
        $paymentRequest->setPrice("{$amount}");
        $paymentRequest->setPaidPrice("{$amount}");
        $paymentRequest->setCurrency(\Iyzipay\Model\Currency::TL);
        $paymentRequest->setBasketId("B" . uniqid());
        $paymentRequest->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $paymentRequest->setCallbackUrl(route('iyzico.callback')); // Ödeme sonrası yönlendirilecek route

        $address = Address::find($r->address);
        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($address->label_name);
        $shippingAddress->setCity("Istanbul");
        $shippingAddress->setCountry("Turkey");
        $shippingAddress->setAddress($address->address);
        $shippingAddress->setZipCode("34732");

        $paymentRequest->setShippingAddress($shippingAddress);
        $paymentRequest->setBillingAddress($shippingAddress);

        // buyer, addresses, basket items minimal doldurun (README örneğine bakın)
        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId("BY" . uniqid());
        $buyer->setName(auth()->user()->first_name);
        $buyer->setSurname(auth()->user()->last_name);
        $buyer->setGsmNumber($r->countrycode . $r->mobile);
        $buyer->setEmail(auth()->user()->email);
        $buyer->setIdentityNumber("11111111110");
        $buyer->setLastLoginDate(date('Y-m-d H:i:s'));
        $buyer->setRegistrationDate(date('Y-m-d H:i:s'));
        $buyer->setRegistrationAddress($address->address);
        $buyer->setIp($r->ip());
        $buyer->setCity("Istanbul");
        $buyer->setCountry("Turkey");
        $buyer->setZipCode("34732");

        $paymentRequest->setBuyer($buyer);

        // basit basket item
        $item = new \Iyzipay\Model\BasketItem();
        $item->setId("BI1");
        $item->setName("Test Ürün");
        $item->setCategory1("Kategori");
        $item->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
        $item->setPrice("1");

        $paymentRequest->setBasketItems([$item]);

        $checkoutFormInitialize = CheckoutFormInitialize::create($paymentRequest, $options);

        return view('frontend.payment.iyzico', [
            'checkoutFormContent' => $checkoutFormInitialize->getCheckoutFormContent()
        ]);

    }

    public function iyzicoCallback(Request $r)
    {
        $token = $r->input('token');

        $options = new Options();
        $options->setApiKey(setting('iyzico_api_key'));
        $options->setSecretKey(setting('iyzico_secret_key'));
        $options->setBaseUrl(config('iyzico.base_url'));

        $request = new RetrieveCheckoutFormRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($r->conversationId);
        $request->setToken($token);

        $checkoutForm = CheckoutForm::retrieve($request, $options);
        $paymentId = $checkoutForm->getPaymentId();

        if ($checkoutForm->getPaymentStatus() === 'SUCCESS') {
            session()->put('paymentId', $paymentId);
            $orderService = app(PaymentService::class)->payment(true);
        } else {
            $orderService = app(PaymentService::class)->payment(false);
        }

        return $this->handleOrderServiceResponse($orderService);
    }

    public function tamiPayment(Request $request)
    {
        return false;
    }

    public function sslcommerzPayment($request)
    {
        $restaurantId = session()->get('session_cart_restaurant_id');
        $cart = session()->get('cart-' . $restaurantId);
        try {
            $array['store_id'] = env('SSLCOMMERZ_STORE_ID');
            $array['store_passwd'] = env('SSLCOMMERZ_STORE_PASSWORD');
            $array['total_amount'] = $cart['totalAmount'] + session()->get('delivery_charge');
            $array['currency'] = "USD";
            $array['tran_id'] = "SSLCZ_" . uniqid();
            $array['shipping_method'] = "NO";
            $array['cus_name'] = auth()->user()->name;
            $array['cus_email'] = auth()->user()->email;
            $array['cus_add1'] = $request->address;
            $array['cus_city'] = "";
            $array['cus_state'] = "";
            $array['cus_postcode'] = "";
            $array['cus_country'] = "";
            $array['cus_phone'] = $request->countrycode . $request->mobile;
            $array['product_name'] = env('APP_NAME');
            $array['product_category'] = "Food";
            $array['product_profile'] = "general";
            $array['product_amount'] = $cart['totalAmount'] + session()->get('delivery_charge');
            $array['discount_amount'] = "";
            $array['convenience_fee'] = session()->get('delivery_charge');
            $array['success_url'] = url('/sslcommerz/success');
            $array['fail_url'] = url('/sslcommerz/fail');
            $array['cancel_url'] = url('/sslcommerz/cancel');

            $apiUrl = 'sandbox' == env('SSLCOMMERZ_MODE') ? "https://sandbox.sslcommerz.com/gwprocess/v4/api.php" : "https://securepay.sslcommerz.com/gwprocess/v4/api.php";

            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $apiUrl);
            curl_setopt($handle, CURLOPT_TIMEOUT, 30);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $array);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, !('sandbox' == env('SSLCOMMERZ_MODE')));

            $content = curl_exec($handle);
            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($code == 200 && !(curl_errno($handle))) {
                curl_close($handle);
                $sslcommerzResponse = $content;
            } else {
                curl_close($handle);
                return redirect(route('checkout.index'))->withError('Failed to connect with SSLCOMMERZ API');
            }

            $response = json_decode($sslcommerzResponse, true);

            if (isset($response['GatewayPageURL']) && $response['GatewayPageURL'] != "") {
                return redirect($response['GatewayPageURL']);
            } else {
                return redirect(route('checkout.index'))->withError('JSON Data parsing error!');
            }
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return redirect(route('checkout.index'))->withError('Bir şeyler ters gitti!');
        }
    }

    public function sslcommerzSuccess(Request $request)
    {
        if (isset($request->bank_tran_id)) {
            $orderService = app(PaymentService::class)->payment(true);
        } else {
            $orderService = app(PaymentService::class)->payment(false);
        }
        return $this->handleOrderServiceResponse($orderService);
    }

    public function sslcommerzFail()
    {
        return redirect(route('checkout.index'))->withError('Bir şeyler ters gitti!');
    }

    public function sslcommerzCancle()
    {
        return redirect(route('checkout.index'))->withError('Bir şeyler ters gitti!');
    }

    public function phonePePayment($request)
    {
        $restaurantId = session()->get('session_cart_restaurant_id');
        $cart = session()->get('cart-' . $restaurantId);

        $phonepe = new LaravelPhonePe();
        $amount = $cart['totalAmount'] + session()->get('delivery_charge');
        $phone = $request->countrycode . $request->mobile;
        $callbak_url = url('/phonepe/status');
        $uniqueId = uniqid();
        $url = $phonepe->makePayment($amount, $phone, $callbak_url, $uniqueId);
        return redirect()->away($url);
    }

    public function phonepeCallback(Request $request)
    {
        $phonepe = new LaravelPhonePe();
        $response = $phonepe->getTransactionStatus($request->all());
        if ($response) {
            $orderService = app(PaymentService::class)->payment(true);
        } else {
            $orderService = app(PaymentService::class)->payment(false);
        }
        return $this->handleOrderServiceResponse($orderService);
    }

    protected function payWithPaytm($request)
    {
        $payment = PaytmWallet::with('receive');
        $payment->prepare([
            'order' => uniqid(),
            'user' => auth()->user()->id,
            'mobile_number' => $request->countrycode . $request->mobile,
            'email' => auth()->user()->email,
            'amount' => session()->get('cart')['totalAmount'] + session()->get('delivery_charge'),
            'callback_url' => url('/paytm/status'),
        ]);
        return $payment->receive();
    }

    protected function paytmCallback()
    {
        $transaction = PaytmWallet::with('receive');
        $response = $transaction->response();
        if ($transaction->isSuccessful()) {
            $orderService = app(PaymentService::class)->payment(true);
        } else {
            $orderService = app(PaymentService::class)->payment(false);
        }
        return $this->handleOrderServiceResponse($orderService);
    }

    protected function setDeliveryCharge($request)
    {
        $deliveryCharge = $request->total_delivery_charge;
        session()->put('delivery_charge', $deliveryCharge ?: 0);
    }

    protected function getLastAddress()
    {
        $lastAddress = Order::select('address')
            ->where('user_id', auth()->user()->id)
            ->latest()
            ->first();

        if (!blank($lastAddress) && isJson($lastAddress->address)) {
            return Address::where('address', json_decode($lastAddress->address, true)['address'])->first();
        }

        return Address::where('user_id', auth()->user()->id)->first();
    }

    protected function validateCheckoutRequest($request, $restaurant)
    {
        $validation = [
            'mobile' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'payment_type' => 'required|numeric',
        ];
        if (!$request->delivery_type) {
            $validation['address'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $validation);

        $validator->after(function ($validator) use ($request, $restaurant) {
            if (
                $request->payment_type == PaymentMethod::WALLET &&
                (float)auth()->user()->balance->balance < (float)(session()->get('cart-' . $restaurant->id)['totalAmount'] + session()->get('delivery_charge'))
            ) {
                $validator->errors()->add('payment_type', 'The Credit balance does not enough for this payment.');
            }
        });

        return $validator;
    }

    protected function processStripePayment($restaurant)
    {
        $stripeService = new StripeService();
        $stripeParameters = [
            'amount' => session()->get('cart-' . $restaurant->id)['totalAmount'] + session()->get('delivery_charge'),
            'currency' => 'USD',
            'token' => request('stripeToken'),
            'description' => 'N/A',
        ];

        $payment = $stripeService->payment($stripeParameters);
        $orderService = $this->handlePaymentResponse($payment);

        if ($orderService->status) {
            $order = Order::find($orderService->order_id);
            $this->clearSessionData();
            $this->sendOrderNotifications($order);
            return redirect(route('account.order.show', $order->id))->withSuccess('Siparişiniz Başarıyla Alındı.');
        } else {
            return redirect(route('checkout.index'))->withError($orderService->message);
        }
    }

    protected function preparePaystackPaymentData($request)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . setting('paystack_secret'),
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email' => auth()->user()->email,
            'amount' => (session()->get('cart')['totalAmount'] + session()->get('delivery_charge')) * 100, // Convert to kobo
            'callback_url' => route('paystack.callback'),
        ]);

        $responseData = $response->json();
        if (isset($responseData['data']['authorization_url'])) {
            $paymentUrl = $responseData['data']['authorization_url'];
            return redirect($paymentUrl);
        } else {
            return redirect()->route('pay')->with('error', 'Payment initialization failed. Please try again.');
        }
    }

    public function PaystackCallback()
    {
        $payment = Paystack::getPaymentData();

        if ($payment['status'] && $payment['data']['status'] === 'success') {
            $orderService = app(PaymentService::class)->payment(true);
        } else {
            $orderService = app(PaymentService::class)->payment(false);
        }

        return $this->handleOrderServiceResponse($orderService);
    }

    protected function initiatePaypalPayment()
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $response = $this->createPaypalOrder($provider);

        if (isset($response['id']) && $response['id'] != null) {
            return $this->redirectPaypalApproval($response['links']);
        } else {
            return redirect(route('checkout.index'))->withError('You have canceled the transaction.');
        }
    }

    protected function createPaypalOrder($provider)
    {
        return $provider->createOrder([
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => route('successTransaction'),
                'cancel_url' => route('cancelTransaction'),
            ],
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => setting('currency_name'),
                        'value' => session()->get('cart')['totalAmount'] + session()->get('delivery_charge'),
                    ],
                ],
            ],
        ]);
    }

    protected function redirectPaypalApproval($links)
    {
        foreach ($links as $link) {
            if ($link['rel'] == 'approve') {
                return redirect()->away($link['href']);
            }
        }

        return redirect(route('checkout.index'))->withError('You have canceled the transaction.');
    }

    protected function processRazorpayPayment($request)
    {
        $input = $request->all();
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        $payment = $this->fetchRazorpayPayment($api, $input);

        if (count($input) && !empty($input['razorpay_payment_id'])) {
            $response = $this->captureRazorpayPayment($api, $input, $payment);

            $orderService = app(PaymentService::class)->payment($response['status'] === 'captured');
            return $this->handleOrderServiceResponse($orderService);
        } else {
            return redirect(route('checkout.index'))->withError('You have canceled the transaction.');
        }
    }

    protected function fetchRazorpayPayment($api, $input)
    {
        return $api->payment->fetch($input['razorpay_payment_id']);
    }

    protected function captureRazorpayPayment($api, $input, $payment)
    {
        return $api->payment->fetch($input['razorpay_payment_id'])->capture(['amount' => $payment['amount']]);
    }

    protected function processDefaultPayment()
    {
        $orderService = app(PaymentService::class)->payment(false);
        return $this->handleOrderServiceResponse($orderService);
    }

    protected function handleOrderServiceResponse($orderService)
    {
        if ($orderService->status) {
            $order = Order::find($orderService->order_id);
            $this->clearSessionData($order->restaurant_id);
            $this->sendOrderNotifications($order);
            return redirect(route('account.order.show', $order->id))->withSuccess('Siparişiniz Başarıyla Alındı.');
        } else {
            return redirect(route('checkout.index'))->withError($orderService->message);
        }
    }

    protected function clearSessionData($restaurantId)
    {
        session()->put('cart-' . $restaurantId, null);
        session()->put('checkoutRequest', null);
        session()->put('session_cart_restaurant_id', 0);
        //session()->put('session_cart_restaurant', null);
    }

    protected function sendOrderNotifications($order)
    {
        try {
            app(PushNotificationService::class)->NotificationForRestaurant($order, $order->restaurant->user, 'restaurant');
            app(PushNotificationService::class)->NotificationForCustomer($order, auth()->user(), 'customer');
        } catch (\Exception $exception) {
            //
        }
    }

    protected function handlePaymentResponse($payment)
    {
        if (is_object($payment) && $payment->isSuccessful()) {
            $orderService = app(PaymentService::class)->payment(true);
        } else {
            $orderService = app(PaymentService::class)->payment(false);
        }
        return $orderService;
    }

    public function paypalSuccessTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            $orderService = app(PaymentService::class)->payment(true);
        } else {
            $orderService = app(PaymentService::class)->payment(false);
        }

        return $this->handleOrderServiceResponse($orderService);
    }

    public function paypalCancelTransaction(Request $request)
    {
        return redirect(route('checkout.index'))->withError('You have canceled the transaction.');
    }
}

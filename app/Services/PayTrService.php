<?php

namespace App\Services;

class PayTrService
{
    public function getToken($userName,$userAddress,$userPhone, $email, $payment_amount, $basket)
    {
        $merchantId      = setting('paytr_merchant_id');
        $merchantKey     = setting('paytr_merchant_key');
        $merchantSalt    = setting('paytr_merchant_salt');
        $merchantSandbox = setting('paytr_sandbox');

        $merchant_id      = $merchantId;
        $merchant_key     = $merchantKey;
        $merchant_salt    = $merchantSalt;
        $merchant_sandbox = $merchantSandbox;

        $user_ip = request()->ip();
        $no_installment = 0;
        $max_installment = 0;
        $currency = "TL";
        $test_mode = $merchant_sandbox; // 1=test, 0=live
        $merchant_oid = uniqid();

        $basket = base64_encode(json_encode($basket));

        $token_str   = $merchant_id.$user_ip.$merchant_oid.$email.$payment_amount.$basket.$no_installment.$max_installment.$currency.$test_mode.$merchant_salt;
        $paytr_token = base64_encode(hash_hmac('sha256', $token_str, $merchant_key, true));

        $post_vals = [
            'merchant_id'           => $merchant_id,
            'user_ip'               => $user_ip,
            'merchant_oid'          => $merchant_oid,
            'email'                 => $email,
            'paytr_token'           => $paytr_token,
            'payment_amount'        => $payment_amount,
            'user_basket'           => $basket,
            'no_installment'        => $no_installment,
            'max_installment'       => $max_installment,
            'currency'              => $currency,
            'test_mode'             => $test_mode,
            'user_name'             => $userName,
            'user_address'          => $userAddress,
            'user_phone'            => $userPhone,
            'merchant_ok_url'       => route('paytr.success'),
            'merchant_fail_url'     => route('paytr.fail'),
            'merchant_notify_url'   => route('paytr.callback'),
        ];

        session()->put('paymentId', $merchant_oid);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}

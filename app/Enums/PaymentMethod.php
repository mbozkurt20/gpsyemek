<?php
/**
 * Created by PhpStorm.
 * User: dipok
 * Date: 18/4/20
 * Time: 12:42 PM
 */

namespace App\Enums;

interface PaymentMethod
{
    const CASH_ON_DELIVERY = 5;
    const CREDIT_CARD_ON_DELIVERY = 6;
    const CARD             = 30;
    const PAYPAL           = 10;
    const STRIPE           = 15;
    const RAZORPAY         = 16;
    const PAYSTACK         = 17;
    const WALLET           = 20;
    const CASH             = 25;
    const PAYTM            = 32;
    const PHONEPE          = 33;
    const SSLCOMMERZ       = 34;
    const IYZICO       = 35;
    const TAMI       = 36;

}

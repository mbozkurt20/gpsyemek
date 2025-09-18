<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Setting as SeederSetting;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settingArray['site_name']                       = env('APP_NAME');
        $settingArray['site_email']                      = '';
        $settingArray['site_phone_number']               = '';
        $settingArray['site_logo']                       = 'seeder/settings/logo.png';
        $settingArray['fav_icon']                        = 'seeder/settings/favicon.png';
        $settingArray['site_address']                    = '';
        $settingArray['site_footer']                     = '@ Her hakkı saklıdır';
        $settingArray['site_description']                = '';
        $settingArray['currency_name']                   = 'TL';
        $settingArray['currency_code']                   = '₺';
        $settingArray['locale']                          = 'tr';
        $settingArray['geolocation_distance_radius']     = 20;
        $settingArray['order_commission_percentage']     = 5;
        $settingArray['free_delivery_radius']            = 0;
        $settingArray['charge_per_kilo']                 = 5;
        $settingArray['basic_delivery_charge']           = 3;
        $settingArray['timezone']                        = 'Europe/Istanbul';
        $settingArray['frontend_theme']                  = 'default';
        $settingArray['twilio_auth_token']               = '';
        $settingArray['twilio_account_sid']              = '';
        $settingArray['twilio_from']                     = '';
        $settingArray['twilio_disabled']                 = 1;
        $settingArray['stripe_key']                      = '';
        $settingArray['stripe_secret']                   = '';
        $settingArray['razorpay_key']                    = '';
        $settingArray['razorpay_secret']                 = '';
        $settingArray['paystack_public_key']             = '';
        $settingArray['paystack_secret_key']             = '';
        $settingArray['paypal_app_id']                   = '';
        $settingArray['paypal_client_id']                = '';
        $settingArray['paypal_client_secret']            = '';
        $settingArray['paypal_mode']                     = 'sandbox';
        $settingArray['paytm_environment']               = 'sandbox';
        $settingArray['paytm_merchant_id']               = '';
        $settingArray['paytm_merchant_key']              = '!nif6e1Kie';
        $settingArray['paytm_merchant_website']          = '';
        $settingArray['paytm_channel']                   = 'WEB';
        $settingArray['paytm_industry_type']             = 'Retail';
        $settingArray['phonepe_merchant_id']             = '';
        $settingArray['phonepe_merchant_user_id']        = '';
        $settingArray['phonepe_env']                     = 'sandbox';
        $settingArray['phonepe_salt_key']                = '';
        $settingArray['phonepe_salt_index']              = '1';
        $settingArray['sslcommerz_store_name']           = '';
        $settingArray['sslcommerz_store_id']             = '';
        $settingArray['sslcommerz_store_password']       = '@ssl';
        $settingArray['sslcommerz_mode']                 = 'sandbox';
        $settingArray['mail_host']                       = '';
        $settingArray['mail_port']                       = '';
        $settingArray['mail_username']                   = '';
        $settingArray['mail_password']                   = '';
        $settingArray['order_attachment_checking']       = '5';
        $settingArray['delivery_boy_order_amount_limit'] = 10000;
        $settingArray['mail_from_name']                  = 'inilabs';
        $settingArray['mail_from_address']               = 'demo@food-bank.xyz';
        $settingArray['mail_disabled']                   = 1;
        $settingArray['firebase_api_key']                = '';
        $settingArray['firebase_authDomain']             = '';
        $settingArray['projectId']                       = '';
        $settingArray['storageBucket']                   = '';
        $settingArray['messagingSenderId']               = '';
        $settingArray['appId']                           = '';
        $settingArray['measurementId']                   = '';
        $settingArray['notification_fcm_json_file']      = 'service-account-file.json';
        $settingArray['facebook_key']                    = '';
        $settingArray['facebook_secret']                 = '';
        $settingArray['facebook_url']                    = 'https://demo.food-bank.xyz/auth/facebook/callback';
        $settingArray['google_map_api_key']              = '';
        $settingArray['google_key']                      = '';
        $settingArray['google_secret']                   = '';
        $settingArray['google_url']                      = '';
        $settingArray['otp_type_checking']               = 'email';
        $settingArray['otp_digit_limit']                 = 6;
        $settingArray['otp_expire_time']                 = 10;
        $settingArray['license_code']                    = session()->has('license_code') ? session()->get('license_code') : "";
        $settingArray['settingtypesocial']               = 'facebook';
        $settingArray['facebook']                        = 'https://www.facebook.com/inilabs';
        $settingArray['instagram']                       = 'https://www.instagram.com/inilabs';
        $settingArray['youtube']                         = 'https://www.youtube.com/inilabs';
        $settingArray['twitter']                         = 'https://twitter.com/inilabs';
        $settingArray['billing-type']                    = 10;
        $settingArray['support_phone']                   = '+9901555555';
        $settingArray['customer_app_name']               = 'Customer';
        $settingArray['customer_app_logo']               = 'seeder/settings/logo.png';
        $settingArray['customer_splash_screen_logo']     = 'seeder/settings/logo.png';
        $settingArray['vendor_app_name']                 = 'Vendor';
        $settingArray['vendor_app_logo']                 = 'seeder/settings/logo.png';
        $settingArray['vendor_splash_screen_logo']       = 'seeder/settings/logo.png';
        $settingArray['delivery_app_name']               = 'Delivery';
        $settingArray['delivery_app_logo']               = 'seeder/settings/logo.png';
        $settingArray['delivery_splash_screen_logo']     = 'seeder/settings/logo.png';
        $settingArray['banner_title']                    = 'Sofranız için Organik ve Lezzetli Yemekler.';
        $settingArray['banner_image']                    = 'seeder/settings/hero.png';
        $settingArray['app_mockup']                      = 'seeder/settings/mockup.png';
        $settingArray['ios_app_link']                    = '';
        $settingArray['android_app_link']                = '';

        SeederSetting::set($settingArray);
        SeederSetting::save();
    }
}

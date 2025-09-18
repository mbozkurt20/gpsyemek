<?php

namespace App\Services;

class IyzicoService
{
    public static function options(): \Iyzipay\Options
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey(config('iyzico.api_key'));
        $options->setSecretKey(config('iyzico.secret_key'));
        $options->setBaseUrl(config('iyzico.base_url')); // sandbox veya production
        return $options;
    }
}

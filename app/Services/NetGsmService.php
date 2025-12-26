<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NetGsmService
{
    protected string $usercode;
    protected string $password;
    protected string $header;

    public function __construct()
    {
        $this->usercode = setting('netgsm_usercode');
        $this->password = setting('netgsm_password');
        $this->header   = setting('netgsm_header');
    }

    /**
     * SMS gönderme işlemi
     */
    public function sendSms(string $phone, string $message)
    {
        $data = [
            "msgheader" => $this->header,
            "messages" => [
                [
                    "msg" => $message,
                    "no" => $phone
                ],
            ],
            "encoding" => "TR",
            "iysfilter" => "",
            "partnercode" => ""
        ];

        $url = "https://api.netgsm.com.tr/sms/rest/v2/send"; // Buraya hedef API URL'sini yazın

        $username = $this->usercode; // Buraya kullanıcı adınızı yazın
        $password = $this->password; // Buraya şifrenizi yazın

        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($username . ':' . $password)
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        $response = json_decode($response, true);

        if ($response['code'] == 00) {
            return true;
        }else{
            return false;
        }
    }
}

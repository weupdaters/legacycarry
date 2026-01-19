<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    public function sendOtp($mobile, $otp)
    {
        $url = "https://sms.smswala.in/app/smsapi/index.php";

        $params = [
            'key'         => '569132F266169F',
            'campaign'    => '16867',
            'routeid'     => '30',
            'type'        => 'text',
            'contacts'    =>  $mobile,
            'senderid'    => 'DLPNPG',
            'msg'         => "Hello! Your OTP for verification is {$otp}. Please enter this code to proceed with your request. Thank you! {$otp}Dolphin",
            'template_id' => '1707168119844114869'
        ];
        
        return Http::get($url, $params)->body();
    }
}

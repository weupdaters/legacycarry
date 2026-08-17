<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    public function sendOtp($mobile, $otp)
    {
        $url = "https://sms.smswala.in/app/smsapi/index.php";

        // Sanitize and format phone number for domestic SMS (extract last 10 digits)
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        if (strlen($mobile) >= 10) {
            $mobile = substr($mobile, -10);
        }

        $params = [
            'key' => '569132F266169F',
            'campaign' => '16867',
            'routeid' => '30',
            'type' => 'text',
            'contacts' => $mobile,
            'senderid' => 'LCARRY',
            'msg' => "Your verification OTP is {$otp}. Enter this to verify your mobile number. Legacy Carry Private Limited",
            'template_id' => '1707177407603964179',
        ];

        $response = Http::get($url, $params)->body();

        \Illuminate\Support\Facades\Log::info("SMSWALA OTP sent to {$mobile}", [
            'contacts' => $mobile,
            'otp' => $otp,
            'response' => $response,
        ]);

        return $response;
    }
}

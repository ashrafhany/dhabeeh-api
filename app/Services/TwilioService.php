<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    public static function sendOtp($phone, $otp)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilioNumber = env('TWILIO_PHONE_NUMBER');

        if (!str_starts_with($phone, '+')) {
            $phone = '+20' . ltrim($phone, '0'); // تحويل الرقم المصري إلى +20
        }

        // تحقق من أن الرقم لا يساوي `From`
        if ($phone === $twilioNumber) {
            return response()->json(['error' => 'Invalid recipient number. Cannot send OTP to the Twilio number.'], 400);
        }

        $client = new Client($sid, $token);
        $client->messages->create($phone, [
            'from' => $twilioNumber,
            'body' => "Your OTP code is: $otp"
        ]);
    }
}

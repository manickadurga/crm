<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsService
{
    public function sendSms($to, $message)
{
    // Check if $to is an array (which shouldn't be the case)
    if (is_array($to)) {
        throw new \Exception('Phone number should be a string, array given.');
    }

    // Check if $message is an array
    if (is_array($message)) {
        throw new \Exception('Message should be a string, array given.');
    }

    $sid = env('TWILIO_SID');
    $token = env('TWILIO_AUTH_TOKEN');
    $twilioNumber = env('TWILIO_PHONE_NUMBER');
    
    $client = new Client($sid, $token);

    try {
        $client->messages->create(
            $to,
            [
                'from' => $twilioNumber,
                'body' => $message
            ]
        );
    } catch (\Exception $e) {
        throw new \Exception('Failed to send SMS: ' . $e->getMessage());
    }
}

}

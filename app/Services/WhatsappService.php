<?php

namespace App\Services;

use Twilio\Rest\Client;

class WhatsappService
{
    protected $client;
    protected $from;

    public function __construct()
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $this->from = 'whatsapp:+14155238886'; // Ensure this is your Twilio WhatsApp number
        $this->client = new Client($sid, $token);
    }

    public function sendWhatsAppMessage($to, $data)
    {
        // Ensure that $to is a valid WhatsApp number in the format 'whatsapp:+[country_code][number]'
        if (!preg_match('/^whatsapp:\+\d{1,15}$/', $to)) {
            throw new \Exception('Invalid WhatsApp number format.');
        }

        // Assuming $data has a message field
        $message = $data['message'] ?? 'Default message';

        try {
            $this->client->messages->create(
                $to,
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to send WhatsApp message: ' . $e->getMessage());
        }
    }
}


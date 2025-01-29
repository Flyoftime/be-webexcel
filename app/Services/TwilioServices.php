<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioServices
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    }

    public function sendWhatsAppMessage($phone, $message)
    {
        $twilio = new \Twilio\Rest\Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        // Send the message
        $twilio->messages->create(
            'whatsapp:' . $phone,
            [
                "from" => "whatsapp:+14155238886",
                'body' => 'Your verification code is: ' . $message
            ]
        );
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $message = "Welcome, {$this->data['name']}!\nThank you for signing up.";

        return $this->subject('Welcome to Our Service')
                    ->withSwiftMessage(function ($swiftMessage) use ($message) {
                        $swiftMessage->setBody($message, 'text/plain');
                    });
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmailAction extends Mailable
{
    use Queueable, SerializesModels;

    public $fromName;
    public $fromEmail;
    public $subject;
    public $message;

    public function __construct($fromName, $fromEmail, $subject, $message)
    {
        $this->fromName = $fromName;
        $this->fromEmail = $fromEmail;
        $this->subject = $subject;
        $this->message = $message;
    }

    public function build()
    {
        return $this->from($this->fromEmail, $this->fromName)
                    ->subject($this->subject)
                    ->html($this->message); // Use HTML directly
    }
}

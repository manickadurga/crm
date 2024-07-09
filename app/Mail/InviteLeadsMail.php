<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InviteLeadsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inviteLead;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inviteLead)
    {
        $this->inviteLead = $inviteLead;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('You have a new invite lead')
                    ->view('emails.inviteleads');
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CandidatesInvite extends Mailable
{
    use Queueable, SerializesModels;

    public $inviteCandidates;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inviteCandidates)
    {
        $this->inviteCandidates = $inviteCandidates;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.candidate_invite');
    }
}
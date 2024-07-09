<?php

namespace App\Mail;

use App\Models\JoinviteClient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientInvite extends Mailable
{
    use Queueable, SerializesModels;

    public $client;

    /**
     * Create a new message instance.
     *
     * @param JoinviteClient $client
     */
    public function __construct(JoinviteClient $client)
    {
        $this->client = $client;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.client_invite')->with(['client' => $this->client]);
    }
}

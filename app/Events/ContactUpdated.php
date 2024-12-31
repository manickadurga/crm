<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactUpdated
{
    use Dispatchable, SerializesModels;

    public $contact;

    /**
     * Create a new event instance.
     *
     * @param $contact
     * @return void
     */
    public function __construct($contact)
    {
        $this->contact = $contact;
    }
}

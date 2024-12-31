<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class ContactCreated
{
    use Dispatchable, SerializesModels;

    public $contact;

   
    public function __construct($contact)
    {
        $this->contact = $contact;
    }
}

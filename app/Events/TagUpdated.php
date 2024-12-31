<?php

namespace App\Events;

use App\Models\Customers;
use App\Models\Tags;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagUpdated
{
    use Dispatchable, SerializesModels;

    public $contact;
    public $tag_id;
    public $action; // "tag_added" or "tag_removed"

    public function __construct(Customers $contact,  Tags $tag_id, string $action)
    {
        $this->contact = $contact;
        $this->tag_id = $tag_id;
        $this->action = $action;
    }
}

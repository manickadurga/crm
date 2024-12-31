<?php

namespace App\Events;

use App\Models\Invoices;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoice;
    public $previousStatus;

    public function __construct(Invoices $invoice, $previousStatus)
    {
        $this->invoice = $invoice;
        $this->previousStatus = $previousStatus;
    }
}

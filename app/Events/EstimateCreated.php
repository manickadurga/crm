<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Estimate;

class EstimateCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $estimate;
    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate;
    }
}

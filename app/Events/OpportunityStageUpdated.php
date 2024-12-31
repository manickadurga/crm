<?php

namespace App\Events;

use App\Models\Opportunity;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OpportunityStageUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $opportunity;
    public function __construct(Opportunity $opportunity)
    {
        $this->opportunity = $opportunity;
    }
}

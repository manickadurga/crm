<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WaitForDuration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $duration;
    protected $actionData;

    public function __construct($duration, $actionData)
    {
        $this->duration = $duration;
        $this->actionData = $actionData;
    }

    public function handle()
    {
        sleep($this->duration); // Wait for the specified duration
        
        // Here you can execute the next action
        // For example, send a message or perform another action
        Log::info('Waited for ' . $this->duration . ' seconds and executing next action');
        // Implement the action that should be executed after waiting
        // You can dispatch another job for the next action here if needed
    }
}


<?php

namespace App\Jobs;

use App\Models\Workflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWorkflowAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $workflow;
    public $estimate;

    public function __construct($workflow, $estimate)
    {
        $this->workflow = $workflow;
        $this->estimate = $estimate;
    }

    public function handle()
    {
        Log::info("Resuming workflow ID: {$this->workflow->id} after wait action.");

        // Fetch the next action and process it
        app('App\Services\WorkflowActionService')->processNextAction($this->workflow, $this->estimate);
    }
}




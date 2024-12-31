<?php

namespace App\Services;

use App\Jobs\ProcessWorkflowAction;
use App\Models\Action;
use App\Models\Workflow;
use App\Models\Estimate;

class WorkflowActionService
{
    /**
     * Process the wait action for workflow.
     *
     * @param  array  $actionData
     * @param  Workflow  $workflow
     * @param  Estimate  $estimate
     * @return void
     */
    public function processWaitAction(array $actionData, Workflow $workflow, Estimate $estimate)
    {
        // Get the wait time from the action data (e.g., '5 minutes', '2 hours')
        $waitTime = $actionData['wait'];

        // Parse the wait time to get the delay in seconds
        $delayInSeconds = $this->parseWaitTime($waitTime);

        // Create a job to handle the next action after the wait time
        ProcessWorkflowAction::dispatch($workflow, $estimate)
            ->delay(now()->addSeconds($delayInSeconds));
    }

    /**
     * Parse the wait time into seconds.
     *
     * @param  string  $waitTime
     * @return int
     */
    public function parseWaitTime(string $waitTime): int
    {
        // Logic to parse time (minutes, hours, days)
        if (strpos($waitTime, 'minute') !== false) {
            $minutes = (int) filter_var($waitTime, FILTER_SANITIZE_NUMBER_INT);
            return $minutes * 60;
        }

        if (strpos($waitTime, 'hour') !== false) {
            $hours = (int) filter_var($waitTime, FILTER_SANITIZE_NUMBER_INT);
            return $hours * 3600;
        }

        if (strpos($waitTime, 'day') !== false) {
            $days = (int) filter_var($waitTime, FILTER_SANITIZE_NUMBER_INT);
            return $days * 86400;
        }

        return 0; // Default to 0 if time is not valid
    }
}




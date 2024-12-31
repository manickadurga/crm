<?php

namespace App\Jobs;

use App\Models\Workflow;
use App\Models\Estimate;
use App\Models\Customers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;

class GoalEventCheckJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public $workflowId;
    public $estimateId;
    public $actionData;

    /**
     * Create a new job instance.
     *
     * @param int $workflowId
     * @param int $estimateId
     * @param array $actionData
     */
    public function __construct($workflowId, $estimateId, $actionData)
    {
        $this->workflowId = $workflowId;
        $this->estimateId = $estimateId;
        $this->actionData = $actionData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Log job execution
        Log::info("Rechecking goal for workflow ID: {$this->workflowId}, estimate ID: {$this->estimateId}");

        // Fetch the workflow and estimate
        $workflow = Workflow::find($this->workflowId);
        $estimate = Estimate::find($this->estimateId);

        if (!$workflow || !$estimate) {
            Log::warning("Unable to find workflow ID: {$this->workflowId} or estimate ID: {$this->estimateId}");
            return;
        }

        // Process the goal event and check if it's met
        $goalMet = $this->handleGoalEvent($this->actionData, $workflow, $estimate);

        // Outcome logic
        $outcome = $this->actionData['outcome'] ?? 'continue anyway';
        Log::info("Goal event outcome: {$outcome}");

        if ($goalMet) {
            Log::info("Goal met for workflow ID: {$this->workflowId}, resuming workflow actions.");
            // Proceed with next actions if goal met
            // You can either trigger the next action or continue the workflow here
            // Example: dispatch next action
            // (Logic for continuing workflow here)

        } else {
            Log::info("Goal not met for workflow ID: {$this->workflowId}, retrying goal check in the future.");

            // Optionally, reschedule the job to check again in the future
            $delayInMinutes = $this->actionData['check_interval'] ?? 5; // Default to 5 minutes
            GoalEventCheckJob::dispatch($this->workflowId, $this->estimateId, $this->actionData)
                ->delay(now()->addMinutes($delayInMinutes)); // Retry after the specified delay
        }
    }

    /**
     * Handle the goal event checking.
     *
     * @param array $actionData
     * @param Workflow $workflow
     * @param Estimate $estimate
     * @return bool
     */
    private function handleGoalEvent(array $actionData, Workflow $workflow, Estimate $estimate)
    {
        Log::info("Checking goal event for workflow {$workflow->id}");

        // Check if the estimate has a contact (customer)
        $contact = $estimate->contact;
        if (!$contact) {
            Log::warning("No associated contact found for estimate ID: {$estimate->id}");
            return false;
        }

        Log::info("Found associated contact ID: {$contact->id} for estimate ID: {$estimate->id}");

        // Extract action type and tags
        $actionType = $actionData['type'] ?? null;
        Log::info("Action Type: {$actionType}");

        if (!in_array($actionType, ['added contact tag', 'removed contact tag'])) {
            Log::warning("Invalid goal type: {$actionType}");
            return false;
        }

        // Fetch tags from the contact and action data
        $tags = is_string($contact->tags) ? json_decode($contact->tags, true) : $contact->tags;
        Log::info("Tags on contact ID {$contact->id}: " . implode(',', $tags));

        $tagsToCheck = $actionData['select'] ?? [];
        Log::info("Tags to check for workflow: " . implode(',', $tagsToCheck));

        if (empty($tagsToCheck)) {
            Log::warning("No tags specified to check in goal event.");
            return false;
        }

        // Check if goal is met based on tag conditions
        switch ($actionType) {
            case 'added contact tag':
                // Check if any specified tag is added
                foreach ($tagsToCheck as $tagToCheck) {
                    if (in_array($tagToCheck, $tags)) {
                        Log::info("Tag '{$tagToCheck}' added to contact ID {$contact->id} for estimate ID: {$estimate->id}");
                        return true; // Goal met
                    }
                }
                break;

            case 'removed contact tag':
                // Check if any specified tag is removed
                foreach ($tagsToCheck as $tagToCheck) {
                    if (!in_array($tagToCheck, $tags)) {
                        Log::info("Tag '{$tagToCheck}' removed from contact ID {$contact->id} for estimate ID: {$estimate->id}");
                        return true; // Goal met
                    }
                }
                break;

            default:
                Log::warning("Unknown action type: {$actionType}");
                break;
        }

        // Goal not met
        Log::info("Goal not met for workflow {$workflow->id} with estimate ID: {$estimate->id}");
        return false;
    }
}

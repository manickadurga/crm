<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OpportunityStatusUpdated;
use App\Jobs\GoalEventCheckJob;
use App\Models\Action;
use App\Models\Customers;
use App\Models\Invoices;
use App\Models\Opportunity;
use App\Models\Tasks;
use App\Models\Workflow;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;

class SendNotificationsOnOpportunityStatusUpdated
{
    private $context = [];
    public function handle(OpportunityStatusUpdated $event)
{
    Log::info("OpportunityStatusUpdated event triggered.");
    $opportunity = $event->opportunity;

    // Fetch workflows associated with the 'opportunity_status_updated' trigger
    $workflows = Workflow::whereHas('trigger', function ($query) {
        $query->where('trigger_name', 'opportunity_status_updated');
    })->get();

    Log::info("Fetched workflows: " . $workflows->count());

    foreach ($workflows as $workflow) {
        Log::info("Processing workflow:", ['id' => $workflow->id, 'name' => $workflow->name]);
        $actionsIds = json_decode($workflow->actions_id, true);
        $actions = Action::whereIn('id', $actionsIds)
                ->orderByRaw("array_position(ARRAY[" . implode(',', $actionsIds) . "]::bigint[], id)") // PostgreSQL-compatible
                ->get();
        // Decode the filters for the trigger
        $filters = $this->decodeFilters($workflow->trigger->filters);

        // Check if filters should be applied or if none are provided
        if ($this->applyFilters($filters, $opportunity)) {
            // Keep track of visited actions to avoid infinite loops
            $visitedActions = [];
            Log::info("Fetched actions for workflow ID {$workflow->id}: " . $actions->count());

            foreach ($actions as $action) {
                if (in_array($action->id, $visitedActions)) {
                    Log::error("Detected infinite loop in Go To action. Workflow stopped.");
                    break;
                }
                $visitedActions[] = $action->id;
                $actionData = is_array($action->action_data)
                ? $action->action_data
                : json_decode($action->action_data, true);
                Log::info("Executing action:", ['type' => $action->type, 'action_data' => $actionData]);

                if (is_array($actionData)) {
                    switch ($action->type) {
                        case 'goal_event':
                            Log::info("Processing goal_event action for workflow ID: {$workflow->id}");
                            $goalMet = $this->handleGoalEvent($actionData, $workflow, $opportunity);
                        
                            // Get the outcome from action data (default to 'continue anyway' if not specified)
                            $outcome = $actionData['outcome'] ?? 'continue anyway';
                            Log::info("Goal outcome: {$outcome} for workflow ID: {$workflow->id}");
                        
                            if ($goalMet) { // Goal met logic
                                Log::info("Goal met for workflow ID: {$workflow->id}");
                                
                                // If the goal is met, simply continue the workflow
                                Log::info("Continuing workflow for workflow ID: {$workflow->id}");
                                continue 2; // Continue with the next action
                            }
                        
                            // Goal not met logic
                            Log::info("Goal not met for workflow ID: {$workflow->id}");
                        
                            if ($outcome === 'end this workflow') {
                                Log::info("Ending workflow as goal conditions are not met for workflow ID: {$workflow->id}");
                                break 2; // Exit the workflow loop (important to use break 2 to stop the outer loop)
                            } elseif ($outcome === 'continue anyway') {
                                Log::info("Continuing workflow despite goal conditions not being met for workflow ID: {$workflow->id}");
                                continue 2; // Proceed with the next action
                            } elseif ($outcome === 'wait until goal is met') {
                                Log::info("Waiting for goal to be met for workflow ID: {$workflow->id}");
                        
                                // Pause the workflow and recheck if the goal is met
                                $delayInMinutes = $actionData['check_interval'] ?? 5; 
                                $this->recheckGoalLater($workflow->id, $opportunity->id, $actionData, $delayInMinutes);
                                continue 2; // This will restart the loop and recheck the goal
                            }
                            break;
                        case 'set_event_start_date':
                            $context['event_start_date'] = $this->handleSetEventStartDateAction($actionData);
                            break;
                        case 'wait':
                                $this->handleWaitAction($actionData, $context['event_start_date'] ?? null);
                                break;
                        case 'find_contact':
                            Log::info("Running find_contact action.");
                            if (!$this->findContact($actionData, $opportunity)) {
                                // Stop the workflow if the contact does not match
                                Log::info("Contact does not match find_contact criteria. Stopping workflow.");
                                break 2; // Exit the workflow loop
                            }
                            break;
                        case 'split':
                                Log::info("Running split action for workflow ID: {$workflow->id}");
                                // Call the new function to handle the split logic
                                $this->handleSplitAction($actionData, $workflow->id);
                                break;
                        case 'assign_user':
                                Log::info("Running assign_user action.");
                                $this->assignUser($actionData, $opportunity);
                                break;
                        case 'remove_assigned_user':
                                Log::info("Running remove_assign_user action.");
                                $this->removeAssignedUser($actionData, $opportunity);
                                break;
                        case 'send_sms':
                            $this->sendSms($actionData, $opportunity);
                            break;
                        case 'send_email':
                            $this->sendEmail($actionData, $opportunity);
                            break;
                        case 'send_whatsapp':
                            $this->sendWhatsApp($actionData, $opportunity);
                            break;
                            case 'drip':
                                $this->handleDripAction($actionData, $opportunity, $actions->toArray());
                                break;
                        case 'remove_opportunity':
                            $this->removeOpportunity($actionData, $opportunity);
                            break;
                        case 'send_invoice':
                            $this->sendinvoice($actionData, $opportunity);
                            break;
                        case 'update_contact':
                            $this->updateContact($actionData, $opportunity);
                            break;
                        case 'delete_contact':
                            $this->deleteContact($opportunity);
                            break;
                        case 'update_custom_value':
                            $this->updateCustomValue($actionData, $opportunity);
                            break;
                        case 'text_formatter':
                                Log::info("Running text_formatter action for contact ID: {$opportunity->id}");
                                        if (isset($actionData['type']) && $this->isTextFormatterAction($actionData['type'])) {
                                            $this->handleTextFormatterAction($actionData, $opportunity);
                                        } else {
                                            Log::warning("Invalid or unsupported text formatter type in action data: " . json_encode($actionData));
                                        }
                                break;
                        case 'add_task':
                                    $this->handleAddTaskAction($actionData);
                                    break; 
                        case 'go_to':
                            Log::info("Running go_to action.");
                            $this->goToAction($actionData, $actions, $action, $visitedActions, $opportunity);
                            break;
                        default:
                            Log::warning("Unsupported action type: {$action->type}");
                    }
                } else {
                    Log::warning("Invalid action data for action type: {$action->type}");
                }
            }
        } else {
            Log::info("Filters not matched for workflow {$workflow->id}, skipping all actions.");
        }
    }
}

/**
 * Decode the filters from JSON string or array
 */
private function decodeFilters($filters)
{
    return is_string($filters) ? json_decode($filters, true) : $filters;
}

/**
 * Apply filters to the opportunity data
 *
 * @param array $filters
 * @param $opportunity
 * @return bool
 */
private function applyFilters($filters, $opportunity)
{
    // If no filters are given, return true (treat as matched)
    if (empty($filters)) {
        Log::info("No filters provided; treating as matched.");
        return true;
    }

    foreach ($filters as $filter) {
        $field = $filter['field'];
        $value = $filter['value'];
        $operator = $filter['operator'] ?? '=';

        // Directly access the attribute and check if it exists
        if (!isset($opportunity->$field)) {
            Log::warning("Field {$field} does not exist on opportunity model.");
            return false;
        }

        // Check the filter condition based on the operator
        $fieldValue = $opportunity->$field;
        Log::info("Field {$field} has value: {$fieldValue}");

        switch ($operator) {
            case 'greater_than':
                if ($fieldValue <= $value) {
                    return false;
                }
                break;

            case 'less_than':
                if ($fieldValue >= $value) {
                    return false;
                }
                break;

            case '=':
                if ($fieldValue != $value) {
                    return false;
                }
                break;

            default:
                Log::warning("Unsupported operator {$operator} for filter.");
                return false;
        }
    }

    return true;
}

private function handleGoalEvent(array $actionData, Workflow $workflow, Opportunity $opportunity)
    {
        Log::info("Checking goal event for workflow {$workflow->id}");
    
        // Check if the goal is met using the action data and the estimate
        $goalMet = $this->isGoalMet($actionData, $opportunity, $workflow);
        if ($goalMet) {
            Log::info("Goal met for workflow {$workflow->id}");
        } else {
            Log::info("Goal not met for workflow {$workflow->id}");
        }
    
        return $goalMet; // Return whether the goal is met or not
    }
    

    private function isGoalMet(array $actionData, Opportunity $opportunity, $workflow)
{
    $contact = $opportunity->contact; // This will use the 'contact' relationship defined in Estimate model

    if (!$contact) {
        Log::warning("No associated contact found for estimate ID: {$opportunity->id}");
        return false;
    }

    Log::info("Found associated contact ID: {$contact->id} for estimate ID: {$opportunity->id}");

    // Check for the action type (either 'added contact tag', 'removed contact tag', or 'email event')
    $actionType = $actionData['type'] ?? null;
    Log::info("Action Type: {$actionType}");

    if (!in_array($actionType, ['added contact tag', 'removed contact tag', 'email_event'])) {
        Log::warning("Invalid goal type: {$actionType}");
        return false;
    }

    if ($actionType === 'email_event') { 
        // Directly check email event goals without checking tags 
        if ($this->checkEmailEvent($contact, $actionData)) { 
            Log::info("Goal met for email event in workflow ID: {$workflow->id}"); 
            return true; 
    } }

    // Fetch the tags for the contact (assuming tags are stored as JSON in the 'tags' field)
    $tags = is_string($contact->tags) ? json_decode($contact->tags, true) : $contact->tags;

    // Ensure $tags is an array
    if (!is_array($tags)) {
        Log::warning("Tags on contact ID {$contact->id} is not an array: " . gettype($tags));
        return false;
    }
    Log::info("Tags on contact ID {$contact->id}: " . implode(',', $tags));

    // Fetch the tags to check from the action data (select field)
    $tagsToCheck = $actionData['select'] ?? [];
    Log::info("Tags to check for workflow: " . implode(',', $tagsToCheck));

    // Ensure $tagsToCheck is an array
    if (!is_array($tagsToCheck)) {
        Log::warning("Tags to check is not an array: " . gettype($tagsToCheck));
        return false;
    }

    Log::info("Tags to check for workflow: " . implode(',', $tagsToCheck));

    if (empty($tagsToCheck)) {
        Log::warning("No tags specified to check in goal event.");
        return false;
    }

    switch ($actionType) {
        case 'added contact tag':
            // Check if any of the specified tags have been added to the contact
            foreach ($tagsToCheck as $tagToCheck) {
                if (in_array($tagToCheck, $tags)) {
                    Log::info("Tag '{$tagToCheck}' added to contact ID {$contact->id} for ID: {$opportunity->id}");
                    return true; // Goal is met
                }
            }
            break;

        case 'removed contact tag':
            // Check if any of the specified tags have been removed from the contact
            foreach ($tagsToCheck as $tagToCheck) {
                if (!in_array($tagToCheck, $tags)) {
                    Log::info("Tag '{$tagToCheck}' removed from contact ID {$contact->id} for estimate ID: {$opportunity->id}");
                    return true; // Goal is met
                }
            }
            break;

        default:
            Log::warning("Unknown action type: {$actionType}");
            break;
    }

    Log::info("Goal not met for workflow {$workflow->id} with estimate ID: {$opportunity->id}");
    return false; // If the goal is not met
}

private function checkEmailEvent($contact, $actionData)
{
    // Fetch all email tracking records for the given crmid
    $emailTrackings = DB::table('email_trackings')->where('crmid', $contact->id)->get();

    if ($emailTrackings->isEmpty()) {
        Log::warning("No email tracking records found for crmid: {$contact->id}");
        return false;
    }

    $selectEmailEvent = $actionData['select_email_event'] ?? null;

    foreach ($emailTrackings as $emailTracking) {
        if ($selectEmailEvent === 'open' && $emailTracking->access_count > 1) {
            Log::info("Email open event goal met for crmid: {$contact->id}");
            return true;
        } elseif ($selectEmailEvent === 'clicked' && $emailTracking->click_count > 1) {
            Log::info("Email click event goal met for crmid: {$contact->id}");
            return true;
        }
    }

    Log::info("Email event goal not met for crmid: {$contact->id}");
    return false;
}

private function recheckGoalLater($workflowId, $estimateId, $actionData, $delayInMinutes)
{
    // Dispatch the listener to recheck goal after the specified delay
    GoalEventCheckJob::dispatch($workflowId, $estimateId, $actionData)
        ->delay(now()->addMinutes($delayInMinutes)); // Delay execution to recheck goal later
}

private function handleDripAction(array $actionData, $opportunity,  $workflowActions)
{
    $batchSize = $actionData['batch_size'] ?? 3; // Default to 3 if not provided
    $dripInterval = $actionData['drip_interval'] ?? '3 minutes'; // Default to 3 minutes if not provided
    
    // Convert drip interval to minutes
    $dripIntervalInMinutes = $this->convertIntervalToMinutes($dripInterval);
    
    // Fetch customers for the drip action
    $customers = Customers::all(); // Get all customers
    $customerChunks = $customers->chunk($batchSize); // Split into batches
    
    // Loop through each batch
    foreach ($customerChunks as $batch) {
        foreach ($batch as $customer) {
            // Check if primary_email exists for email or other logic
            if (!isset($customer->primary_email) || empty($customer->primary_email)) {
                Log::warning("Customer with ID {$customer->id} does not have a primary email.");
                continue;
            }

            // Process each action in workflow actions
            foreach ($workflowActions as $action) {
                switch ($action['type']) {
                    case 'send_email':
                        $this->sendEmailInBatch($action['action_data'], $customer);
                        break;

                    case 'send_sms':
                        $this->sendSmsInBatch($action['action_data'], $customer, $opportunity);
                        break;

                    case 'send_whatsapp':
                        $this->sendWhatsappInBatch($action['action_data'], $customer);
                        break;

                    default:
                        Log::warning("Unknown action type: {$action['type']}");
                        break;
                }
            }
        }

        // Wait for the specified drip interval
        $this->waitForDripInterval($dripIntervalInMinutes);
    }
}

private function sendEmailInBatch(array $actionData, $customer)
{
    $message = $actionData['message'] ?? "Hello, welcome to our service!";
    $fromEmail = $actionData['from_email'] ?? 'default@domain.com';
    $fromName = $actionData['from_name'] ?? 'Support';
    $subject = $actionData['subject'] ?? 'Welcome to our Service!';

    try {
        Mail::raw($message, function ($mail) use ($fromEmail, $fromName, $customer, $subject) {
            $mail->from($fromEmail, $fromName)
                 ->to($customer->primary_email)
                 ->subject($subject);
        });
        Log::info("Email sent to {$customer->primary_email}");
    } catch (\Exception $e) {
        Log::error("Failed to send email to {$customer->primary_email}: " . $e->getMessage());
    }
}

private function sendSmsInBatch(array $actionData, $customer)
{
    $phoneNumber = $customer->primary_phone ?? null; // Replace with the appropriate field
    $message = $actionData['message'] ?? "Hello, this is a notification message.";

    if (!$phoneNumber) {
        Log::warning("Customer with ID {$customer->id} does not have a phone number.");
        return;
    }

    // Explicitly retrieve Twilio credentials from configuration
    $sid = config('services.twilio.sid');
    $token = config('services.twilio.token');
    $fromPhoneNumber = config('services.twilio.from');

    if (empty($sid) || empty($token) || empty($fromPhoneNumber)) {
        Log::error("Twilio credentials are missing. SID: {$sid}, Token: {$token}, From: {$fromPhoneNumber}");
        return;
    }

    try {
        // Pass credentials explicitly to TwilioClient
        $twilio = new \Twilio\Rest\Client($sid, $token);
        $twilio->messages->create($phoneNumber, [
            'from' => $fromPhoneNumber,
            'body' => $message,
        ]);

        Log::info("SMS sent to {$phoneNumber}");
    } catch (\Exception $e) {
        Log::error("Failed to send SMS to {$phoneNumber}: " . $e->getMessage());
    }
}


private function sendWhatsappInBatch(array $actionData, $customer)
{
    $phoneNumber = $customer->primary_phone ?? null; // Replace with the appropriate field
    $message = $actionData['message'] ?? "Hello, this is a WhatsApp notification.";

    if (!$phoneNumber) {
        Log::warning("Customer with ID {$customer->id} does not have a phone number.");
        return;
    }

    // Format the phone number for WhatsApp (Twilio requires 'whatsapp:' prefix)
    $whatsappNumber = "whatsapp:{$phoneNumber}";

    // Explicitly retrieve Twilio credentials from configuration
    $sid = config('services.twilio.sid');
    $token = config('services.twilio.token');
    $fromWhatsappNumber = config('services.twilio.whatsapp_from'); // Ensure this is configured

    if (empty($sid) || empty($token) || empty($fromWhatsappNumber)) {
        Log::error("Twilio WhatsApp credentials are missing. SID: {$sid}, Token: {$token}, From: {$fromWhatsappNumber}");
        return;
    }

    try {
        // Pass credentials explicitly to TwilioClient
        $twilio = new \Twilio\Rest\Client($sid, $token);
        $twilio->messages->create($whatsappNumber, [
            'from' => $fromWhatsappNumber,
            'body' => $message,
        ]);

        Log::info("WhatsApp message sent to {$whatsappNumber}");
    } catch (\Exception $e) {
        Log::error("Failed to send WhatsApp message to {$whatsappNumber}: " . $e->getMessage());
    }
}


    private function convertIntervalToMinutes($dripInterval)
{
    // Convert the drip interval to minutes (e.g., "3 minutes" -> 3)
    if (preg_match('/(\d+)\s*(minute|minutes)/', $dripInterval, $matches)) {
        return (int) $matches[1];
    }

    return 3; // Default to 3 minutes if conversion fails
}

private function waitForDripInterval($intervalInMinutes)
{
    // Wait for the specified number of minutes before proceeding
    sleep($intervalInMinutes * 60); // Convert minutes to seconds and pause execution
}

private function handleSetEventStartDateAction($actionData)
{
    Log::info("Handling Event Start Date Action with data: " . json_encode($actionData)); // Log the incoming action data

    // Check if the type is 'specific_date_or_time'
    if ($actionData['type'] == 'specific_date_or_time') {
        Log::info("Processing specific date or time action."); // Log that we're processing this action type

        try {
            // Use 24-hour format for time parsing (H for 24-hour format)
            $eventStartDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $actionData['select_date']);
            Log::info("Event Start Date set to: {$eventStartDate}"); // Log the parsed event start date
            return $eventStartDate;
        } catch (\Exception $e) {
            Log::error("Failed to parse Event Start Date: " . $e->getMessage()); // Log error if parsing fails
            Log::error("Error details: " . json_encode($actionData)); // Log action data for debugging
        }
    }

    if ($actionData['type'] == 'custom_field' && isset($actionData['select_date'])) {
        Log::info("Processing custom field action with select_date: " . $actionData['select_date']); // Log select_date
    
        // Check if select_date is a valid date format or a column name
        if (strtotime($actionData['select_date'])) {
            // If it's a valid date or datetime, parse it directly
            try {
                // Parse the date and time exactly as it is without modifying it
                $eventStartDate = \Carbon\Carbon::parse($actionData['select_date']);
                Log::info("Event Start Date set to: {$eventStartDate}"); // Log the parsed event start date and time
                return $eventStartDate;
            } catch (\Exception $e) {
                Log::error("Failed to parse custom field date: " . $e->getMessage());
                Log::error("Error details: " . json_encode($actionData)); // Log action data for debugging
            }
        } else {
            // If it's a column name in the format {{payment.payment_date}}, we need to extract it
            $selectDate = $actionData['select_date'];
    
            // Check if select_date is in the format {{table.column}}
            if (preg_match('/\{\{(\w+)\.(\w+)\}\}/', $selectDate, $matches)) {
                // Extract table and column from the matched pattern
                $table = 'jo_' . $matches[1]; // Prepend 'jo_' to the table name
                $column = $matches[2]; // Column name
    
                // Dynamically fetch the value from the relevant table and column
                try {
                    // Fetch the value from the table
                    $value = DB::table($table)->value($column);
    
                    if ($value) {
                        // If value exists and is a valid date or datetime string, parse it
                        $eventStartDate = \Carbon\Carbon::parse($value);
                        Log::info("Event Start Date set to value from custom field: {$eventStartDate}");
                        return $eventStartDate;
                    } else {
                        Log::warning("No value found for custom field: {$column} in table {$table}");
                    }
                } catch (\Exception $e) {
                    // Log error if fetching or parsing fails
                    Log::error("Failed to fetch custom field value: " . $e->getMessage());
                    Log::error("Error details: " . json_encode($actionData)); // Log action data for debugging
                }
            } else {
                // If it's a direct date, treat it as the event start date (ignore time)
                try {
                    $eventStartDate = \Carbon\Carbon::parse($selectDate);
                    Log::info("Event Start Date set to direct date: {$eventStartDate}");
                    return $eventStartDate;
                } catch (\Exception $e) {
                    Log::error("Failed to parse direct date: " . $e->getMessage());
                    Log::error("Error details: " . json_encode($actionData)); // Log action data for debugging
                }
            }
        }
    }
    if ($actionData['type'] == 'specific_day') {
        Log::info("Processing specific day action."); // Log that we're processing this action type

        $dayType = $actionData['day_type'] ?? null;
        $dayValue = $actionData['day_value'] ?? null;
        $time = $actionData['time'] ?? null;

        try {
            if ($dayType === 'current_day_of_month' && is_int($dayValue) && $dayValue >= 1 && $dayValue <= 31) {
                // Get the specific day of the current month and set the time
                $eventStartDate = \Carbon\Carbon::now()
                    ->startOfMonth()
                    ->addDays($dayValue - 1)
                    ->setTimeFromTimeString($time);
                Log::info("Event Start Date set to specific day of month: {$eventStartDate}");
                return $eventStartDate;
            } elseif ($dayType === 'current_day_of_week' && in_array($dayValue, ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])) {
                // Get the next occurrence of the specified day of the week and set the time
                $eventStartDate = \Carbon\Carbon::parse('next ' . $dayValue)
                    ->setTimeFromTimeString($time);
                Log::info("Event Start Date set to specific day of week: {$eventStartDate}");
                return $eventStartDate;
            } else {
                Log::warning("Invalid specific day configuration: " . json_encode($actionData));
            }
        } catch (\Exception $e) {
            Log::error("Failed to process specific day: " . $e->getMessage());
            Log::error("Error details: " . json_encode($actionData)); // Log action data for debugging
        }
    }
    
    // Default fallback
    Log::warning("Invalid Event Start Date action data: " . json_encode($actionData)); // Log invalid data
    return null;
    
    
}


private function handleWaitAction(array $actionData, $eventStartDate = null)
{
    Log::info("Handling Wait Action with data: " . json_encode($actionData)); // Log the incoming action data

    // If event start date is provided, wait until that date
    if ($eventStartDate) {
        $currentTime = \Carbon\Carbon::now();
        $waitUntil = \Carbon\Carbon::parse($eventStartDate);

        Log::info("Current time: {$currentTime}, Waiting until: {$waitUntil}"); // Log current time and wait until time

        if ($waitUntil->isFuture()) {
            $timeDifferenceInSeconds = $currentTime->diffInSeconds($waitUntil);

            if ($timeDifferenceInSeconds > 0) {
                Log::info("Waiting for {$timeDifferenceInSeconds} seconds until: {$waitUntil}");
                sleep($timeDifferenceInSeconds); // Sleep until the event start time
            }
        }
    } else {
        Log::info("No Event Start Date provided, proceeding with wait duration only."); // Log that we're only waiting for the specified time
    }

    // Handle the wait duration (e.g., 2 minutes) once event start time has passed (or directly if no event start date)
    if (isset($actionData['wait_for']) && $actionData['wait_for'] === 'time delay' && isset($actionData['wait'])) {
        Log::info("Wait duration specified: {$actionData['wait']}"); // Log the wait duration
        $waitDuration = $this->parseWaitDuration($actionData['wait']);
        if ($waitDuration > 0) {
            Log::info("Waiting for {$actionData['wait']} ({$waitDuration} seconds) after event start date or directly.");
            sleep($waitDuration); // Pause execution for the wait duration
        } else {
            Log::warning("Invalid wait duration in action data: " . json_encode($actionData));
        }
    }
}

/**
 * Parse wait duration from string (e.g., "5 minutes", "5 hours", "5 days").
 */
private function parseWaitDuration(string $wait): int
{
    Log::info("Parsing wait duration: {$wait}"); // Log the wait string being parsed

    $parts = explode(' ', $wait);
    if (count($parts) === 2) {
        $value = (int) $parts[0];
        $unit = strtolower($parts[1]);

        Log::info("Parsed wait duration: {$value} {$unit}"); // Log parsed values

        switch ($unit) {
            case 'seconds':
                return $value;
            case 'minutes':
                return $value * 60;
            case 'hours':
                return $value * 3600;
            case 'days':
                return $value * 86400;
        }
    }

    Log::warning("Invalid wait duration format: {$wait}"); // Log invalid wait duration format
    return 0; // Invalid format
}


private function handleAddTaskAction(array $actionData)
{
    // Extract action data fields
    $title = $actionData['title'] ?? 'Untitled Task';
    $description = $actionData['description'] ?? 'No description provided.';
    $assignTo = $actionData['assign_to'] ?? [];
    $dueIn = $actionData['due_in'] ?? '1 day';

    // Convert due_in to a date
    $dueDate = $this->calculateDueDate($dueIn);

    if (!$dueDate) {
        Log::error("Invalid due_in value: {$dueIn}");
        return;
    }

    try {
        // Loop through the assign_to list and create tasks for each assignee
        foreach ($assignTo as $employeeId) {
            $task = new Tasks();
            $task->title = $title;
            $task->description = $description;
            $task->addorremoveemployee = $employeeId; // Map to addorremoveemployee field
            $task->duedate = $dueDate; // Map to duedate field
            $task->save();

            Log::info("Task '{$title}' assigned to Employee ID {$employeeId} with due date {$dueDate}.");
        }
    } catch (\Exception $e) {
        Log::error("Failed to create task: " . $e->getMessage());
    }
}

private function calculateDueDate(string $dueIn): ?string
{
    try {
        $date = new \DateTime();
        $date->modify($dueIn); // Adjust date based on the due_in string
        return $date->format('Y-m-d H:i:s');
    } catch (\Exception $e) {
        Log::error("Error parsing due_in string '{$dueIn}': " . $e->getMessage());
        return null;
    }
}


// Add this function inside your listener or handler class
private function handleSplitAction($actionData, $workflowId)
{
    // Check if paths exist and are an array
    if (isset($actionData['paths']) && is_array($actionData['paths'])) {
        // Calculate the total percentage
        $totalPercentage = 0;
        foreach ($actionData['paths'] as $pathPercentage) {
            // Extract the number part of the percentage (e.g., "50%" -> 50)
            $percentage = (int)str_replace('%', '', $pathPercentage);
            $totalPercentage += $percentage;
        }

        // Check if the total percentage exceeds 100
        if ($totalPercentage > 100) {
            Log::error("Error: Total percentage in paths exceeds 100% for workflow ID: {$workflowId}. Total: {$totalPercentage}%");
        } else {
            // Log the number of paths created
            $numberOfPaths = count($actionData['paths']);
            Log::info("Number of paths created for workflow ID: {$workflowId}: {$numberOfPaths}");
        }
    } else {
        Log::warning("Invalid or missing paths in split action data for workflow ID: {$workflowId}");
    }
}


private function isTextFormatterAction($type)
{
    $textFormatterTypes = [
        'upper_case',
        'lower_case',
        'title_case',
        'capitalize',
        'default_value',
        'trim',
        'trim_whitespace',
        'replace_text',
        'find',
        'word_count',
        'length',
        'split_text',
        'remove_html_tags',
        'extract_email',
        'extract_url',
    ];

    return in_array($type, $textFormatterTypes);
}


private function handleTextFormatterAction($actionData, &$opportunity)
{
    $field = $actionData['field'] ?? null;
    $type = $actionData['type'] ?? null;
    $value = $actionData['value'] ?? null;
    $length = $actionData['length'] ?? null;
    $delimiter = $actionData['delimiter'] ?? null;
    $replaceWith = $actionData['replace_with'] ?? null;

    if (!$field || !isset($opportunity[$field])) {
        Log::warning("Text Formatter: Field '{$field}' does not exist or is not specified.");
        return;
    }
    $originalValue = $opportunity[$field]; // Store the original value for logging
    $text = $opportunity[$field];

    switch ($type) {
        case 'upper_case':
            $opportunity[$field] = strtoupper($text);
            break;

        case 'lower_case':
            $opportunity[$field] = strtolower($text);
            break;

        case 'title_case':
            $opportunity[$field] = ucwords(strtolower($text));
            break;

        case 'capitalize':
            $opportunity[$field] = ucfirst(strtolower($text));
            break;

        case 'default_value':
            if (empty($text)) {
                $opportunity[$field] = $value; // Set the default value if the field is empty
                Log::info("Text Formatter: Set default value for '{$field}' to '{$value}' because the original value was empty.");
            }
            break;

        case 'trim':
            $opportunity[$field] = $length ? substr($text, 0, $length) : $text;
            break;

        case 'trim_whitespace':
            $opportunity[$field] = trim($text);
            break;

        case 'replace_text':
            $replaceWith = $actionData['replace_with'] ?? null;
            if ($replaceWith) {
                $opportunity[$field] = str_replace($actionData['value'], $replaceWith, $text);
                Log::info("Text Formatter: Replaced '{$actionData['value']}' with '{$replaceWith}' in field '{$field}'.");
            } else {
                Log::warning("Text Formatter: 'replace_with' value not provided for replace_text action.");
            }
            break;

        case 'find':
                $position = strpos($text, $value);
                if ($position !== false) {
                    Log::info("Find: Value '{$value}' found at position {$position} in '{$field}'.");
                } else {
                    Log::info("Find: Value '{$value}' not found in '{$field}'.");
                }
                break;
            

        case 'word_count':
            $wordCount = str_word_count($text);
            Log::info("Word Count: The field '{$field}' contains {$wordCount} words.");
            break;

        case 'length':
                $length = strlen($text);
                Log::info("Length: The field '{$field}' has a length of {$length} characters.");
                break;
    

        case 'split_text':
            if ($delimiter) {
                $splitText = explode($delimiter, $text);
                // Log the result of the split
                Log::info("Split Text: Field '{$field}' split into " . json_encode($splitText));
                
                // If you want to store the result as a JSON array in the field
                $opportunity[$field] = json_encode($splitText); // Store the result back to the field (as a JSON string)
            } else {
                Log::warning("Split Text: 'delimiter' is required for this action.");
            }
            break;

        case 'remove_html_tags':
            $opportunity[$field] = strip_tags($text);
            break;

        case 'extract_email':
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
                $opportunity[$field] = $matches[0];
            } else {
                Log::info("Extract Email: No email found in '{$field}'.");
            }
            break;

        case 'extract_url':
            if (preg_match('/https?:\/\/[^\s]+/', $text, $matches)) {
                $opportunity[$field] = $matches[0];
            } else {
                Log::info("Extract URL: No URL found in '{$field}'.");
            }
            break;

        default:
            Log::warning("Text Formatter: Unsupported action type '{$type}'.");
    }
    if ($originalValue !== $opportunity[$field]) {
        // Save changes to the database
        $opportunity->save();
        Log::info("Text Formatter: Updated '{$field}' from '{$originalValue}' to '{$opportunity[$field]}' and saved to database.");
    } else {
        Log::info("Text Formatter: No changes detected for '{$field}'.");
    }

    Log::info("Text Formatter: Processed '{$type}' on field '{$field}' with result: {$opportunity[$field]}");
}

private function findContact(array $actionData, $opportunity)
    {
        $contactId = $opportunity->contact_id;

        if (!$contactId) {
            Log::info("Find Contact: No contact ID found in estimate.");
            return false;
        }

        $contact = Customers::find($contactId);

        if (!$contact) {
            Log::info("Find Contact: Contact not found for ID {$contactId}.");
            return false;
        }

        foreach ($actionData as $key => $value) {
            if (!isset($contact->$key) || $contact->$key != $value) {
                Log::info("Find Contact: Contact does not match the criteria.", [
                    'criteria' => $actionData,
                    'contact' => $contact->toArray()
                ]);
                return false; // Contact does not match
            }
        }

        Log::info("Find Contact: Contact matches the criteria.", [
            'criteria' => $actionData,
            'contact' => $contact->toArray()
        ]);

        $this->context['contact'] = $contact; // Store matched contact for next actions
        return true; // Contact matches
}

public function assignUser($actionData, $opportunity)
    {
        Log::info("Running assign_user action for estimate ID: {$opportunity->id}.");
    
        // Fetch the contact associated with the estimate (contact_id from jo_estimates -> contacts field)
        $contactId = $opportunity->contact_id; // Assuming 'contacts' is the field containing the contact ID(s)
        $contact = Customers::find($contactId); // Fetch the contact from the jo_customers table
    
        if (!$contact) {
            Log::warning("Contact with ID {$contactId} not found. Cannot assign user.");
            return;
        }
    
        // Retrieve the user(s) from the action_data (an array of user IDs)
        $userIds = $actionData['name']; // Assuming 'name' contains an array of user IDs
    
        // Assign the users to the contact
        try {
            $contact->users()->sync($userIds); // Use 'sync' to add/remove users and ensure no duplicates
            Log::info("Assigned user(s) to contact {$contact->name}.");
        } catch (\Exception $e) {
            Log::error("Failed to assign user(s) to contact {$contact->name}: " . $e->getMessage());
        }
    }
    
    public function removeAssignedUser($actionData, $opportunity)
    {
        Log::info("Running remove_assigned_user action for estimate ID: {$opportunity->id}.");
    
        // Fetch the contact associated with the estimate (contact_id from jo_estimates -> contacts field)
        $contactId = $opportunity->contact_id; // Assuming 'contacts' is the field containing the contact ID(s)
        $contact = Customers::find($contactId); // Fetch the contact from the jo_customers table
    
        if (!$contact) {
            Log::warning("Contact with ID {$contactId} not found. No users to remove.");
            return;
        }
    
        // Detach all users from the contact
        try {
            $contact->users()->detach();  // Remove all assigned users
            Log::info("Removed all users from contact {$contact->name}.");
        } catch (\Exception $e) {
            Log::error("Failed to remove assigned users from contact {$contact->name}: " . $e->getMessage());
        }
    }

    
public function updateContact(array $actionData, $opportunity)
{
    try {
        // Fetch the contact using the contact_id from the opportunity
        $contact = \App\Models\Customers::find($opportunity->contact_id);

        if (!$contact) {
            Log::warning("Contact not found for contact ID {$opportunity->contact_id}.");
            return;
        }

        // Ensure action_data contains key-value pairs
        if (empty($actionData)) {
            Log::warning("Empty action data provided for contact ID {$contact->id}.");
            return;
        }

        // Iterate over action_data and update the contact fields dynamically
        foreach ($actionData as $field => $value) {
            // Ensure the field exists in the contact model's attributes
            if (!array_key_exists($field, $contact->getAttributes())) {
                Log::warning("Field '{$field}' does not exist in the Customers model for contact ID {$contact->id}.");
                continue;
            }

            // Update the field with the provided value
            $contact->{$field} = $value;
        }

        // Save the contact if there are any changes
        if ($contact->isDirty()) { // Only save if there are changes to the model
            $contact->save();
            Log::info("Contact ID {$contact->id} updated successfully with action data: ", $actionData);
        } else {
            Log::info("No changes detected for contact ID {$contact->id}.");
        }
    } catch (Exception $e) {
        // Log the error for debugging
        Log::error("Error updating contact for opportunity ID {$opportunity->id}: " . $e->getMessage(), [
            'action_data' => $actionData,
        ]);
    }
}

public function deleteContact($opportunity)
{
    try {
        // Fetch the contact using the contact_id from the opportunity
        $contact = \App\Models\Customers::find($opportunity->contact_id);

        if (!$contact) {
            Log::warning("Contact not found for contact ID {$opportunity->contact_id}. Deletion aborted.");
            return;
        }

        // Delete the contact
        $contact->delete();

        Log::info("Contact ID {$contact->id} deleted successfully.");
    } catch (Exception $e) {
        // Log the error for debugging
        Log::error("Error deleting contact for opportunity ID {$opportunity->id}: " . $e->getMessage());
    }
}

    private function sendEmail(array $data, $opportunity)
    {
        // Fetch the customer's email using contact_id
        $customer = Customers::find($opportunity->contact_id);
    
        if (!$customer || !$customer->primary_email) {
        Log::error("No primary email found for contact_id: {$opportunity->contact_id}");
        return;
        }
        $message = str_replace('{{opportunity.name}}', $opportunity->opportunity_name, $data['message']);

        try {
            Mail::raw($message, function ($mail) use ($data, $customer) {
                $mail->from($data['from_email'], $data['from_name'])
                    ->to($customer->primary_email)
                    ->subject($data['subject']);

                if (isset($data['attachments']) && is_array($data['attachments'])) {
                    foreach ($data['attachments'] as $attachment) {
                        if ($attachment['type'] == 'file' && isset($attachment['url'])) {
                            $mail->attach($attachment['url']);
                        }
                    }
                }
            });

            Log::info("Email sent to {$opportunity->primary_email}");
        } catch (\Exception $e) {
            Log::error("Failed to send email: " . $e->getMessage());
        }
    }

    private function sendSms(array $data, $opportunity)
    {
    // Fetch the customer's phone number using contact_id
    $customer = Customers::find($opportunity->contact_id);
    $phoneNumber = $data['test_phone_number'] ?? $customer->primary_phone;

    if (!$phoneNumber) {
        Log::error("No phone number found for contact_id: {$opportunity->contact_id}");
        return;
    }

    $message = str_replace('{{opportunity.name}}', $opportunity->opportunity_name, $data['message']);

    // Twilio configuration
    $sid = config('services.twilio.sid');
    $token = config('services.twilio.token');
    $twilioNumber = config('services.twilio.from');

    Log::info("Twilio SID: {$sid}");
    Log::info("Twilio Auth Token: {$token}");
    Log::info("Twilio Phone Number: {$twilioNumber}");

    Log::info("Sending SMS to: {$phoneNumber}");
    Log::info("Message: {$message}");

    if (empty($sid) || empty($token) || empty($twilioNumber)) {
        Log::error("Twilio credentials are missing. SID: {$sid}, Token: {$token}, Phone Number: {$twilioNumber}");
        return;
    }

    $client = new TwilioClient($sid, $token);

    try {
        $message= $client->messages->create(
            $phoneNumber, // To
            [
                'from' => $twilioNumber, // From
                'body' => $message,
                'statusCallback' => 'https://yourcallbackurl.com/status' 
            ]
        );

        Log::info("SMS sent to {$phoneNumber}: {$message}");
    } catch (\Exception $e) {
        Log::error("Failed to send SMS: " . $e->getMessage());
    }
    }

    private function sendWhatsApp(array $data, $opportunity)
    {
        // Fetch the customer's phone number using contact_id
    $customer = Customers::find($opportunity->contact_id);
    $whatsappNumber = $data['test_phone_number'] ?? $customer->primary_phone;

    if (!$whatsappNumber) {
        Log::error("No phone number found for contact_id: {$opportunity->contact_id}");
        return;
    }
        $message = str_replace('{{opportunity.name}}', $opportunity->opportunity_name, $data['message']);

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $twilioWhatsappNumber = config('services.twilio.whatsapp_from');

        $client = new TwilioClient($sid, $token);

        try {
            $client->messages->create("whatsapp:{$whatsappNumber}", ['from' => $twilioWhatsappNumber, 'body' => $message]);
            Log::info("WhatsApp message sent to {$whatsappNumber}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp message: " . $e->getMessage());
        }
    }

    private function removeOpportunity($actionData, $opportunity)
    {
        if (isset($actionData['select_pipeline']) && $opportunity->select_pipeline == $actionData['select_pipeline']) {
            Log::info("Removing opportunity from pipeline:", ['opportunity_id' => $opportunity->id, 'pipeline_id' => $actionData['select_pipeline']]);

            // Perform the actual removal (for example, you might set a 'deleted' flag or delete the record)
            $opportunity->delete(); // Or use a different method if "soft deleting" or marking as removed
            Log::info("Opportunity removed successfully.");
        } else {
            Log::warning("Pipeline ID mismatch or missing 'select_pipeline' in action data.");
        }
    }

    private function sendInvoice(array $actionData, $opportunity)
    {
    // Retrieve the customer email from the opportunity's contact_id
    $customer = Customers::find($opportunity->contact_id);

    if (!$customer || !$customer->primary_email) {
        Log::error("No primary email found for contact_id: {$opportunity->contact_id}");
        return;
    }

    // Fetch the invoice template by ID from action_data
    $invoiceTemplate = Invoices::find($actionData['invoice_template']);
    Log::info("Invoice Template Retrieved: ", ['invoiceTemplate' => $invoiceTemplate]);


    if (!$invoiceTemplate) {
        Log::error("No invoice template found for ID: {$actionData['invoice_template']}");
        return;
    }

    // Construct the email body with important fields from the template
$emailBody = "Invoice Details:\n";
$emailBody .= "Invoice Number: {$invoiceTemplate->invoicenumber}\n";
$emailBody .= "Contact ID: {$invoiceTemplate->contacts}\n";
$emailBody .= "Invoice Date: {$invoiceTemplate->invoicedate}\n";
$emailBody .= "Due Date: {$invoiceTemplate->duedate}\n";
$emailBody .= "Discount: {$invoiceTemplate->discount}{$invoiceTemplate->discount_suffix}\n";
$emailBody .= "Currency: {$invoiceTemplate->currency}\n";
$emailBody .= "Tax1: {$invoiceTemplate->tax1}{$invoiceTemplate->tax1_suffix}\n";
$emailBody .= "Tax2: {$invoiceTemplate->tax2}{$invoiceTemplate->tax2_suffix}\n";
$emailBody .= "Status: {$invoiceTemplate->invoice_status}\n";
$emailBody .= "Organization Name ID: {$invoiceTemplate->organization_name}\n";

Log::info("Constructed email body: " . $emailBody);

    try {
        Mail::html($emailBody, function ($mail) use ($actionData, $customer, $invoiceTemplate) {
            $mail->from($actionData['from_user'] . '@example.com', $actionData['from_user'])
                ->to($customer->primary_email)
                ->subject("Invoice #{$invoiceTemplate->invoicenumber}");

            // Attach the invoice file if available
            if (isset($invoiceTemplate->file_path)) {
                $mail->attach(storage_path('app/' . $invoiceTemplate->file_path));
            }
        });

        Log::info("Invoice email sent to {$customer->primary_email}");
    } catch (\Exception $e) {
        Log::error("Failed to send invoice: " . $e->getMessage());
    }
}

private function updateCustomValue($actionData, $contact)
{
    if (!isset($actionData['custom_value'], $actionData['current_value'], $actionData['new_value'])) {
        Log::warning("Missing required fields in update_custom_value action data: " . json_encode($actionData));
        return;
    }

    $customField = $actionData['custom_value'];
    $currentValue = $actionData['current_value'];
    $newValue = $actionData['new_value'];

    // Check if the custom field exists in the model's attributes
    if (!array_key_exists($customField, $contact->getAttributes())) {
        Log::warning("Custom field '{$customField}' does not exist in the Contact model attributes.");
        return;
    }

    // Get the current value of the field
    $currentFieldValue = $contact->$customField;

    // Normalize the current value into an array for consistent processing
    if (is_string($currentFieldValue)) {
        $currentFieldValue = json_decode($currentFieldValue, true) ?? [$currentFieldValue];
    } elseif (is_numeric($currentFieldValue)) {
        $currentFieldValue = [$currentFieldValue];
    } elseif (!is_array($currentFieldValue)) {
        Log::error("Unsupported data type for custom field '{$customField}': " . gettype($currentFieldValue));
        return;
    }

    // Normalize currentValue and newValue into arrays for comparison
    $currentValue = (array) $currentValue;
    $newValue = (array) $newValue;

    // Check if the current value matches the expected value
    if (!array_intersect($currentValue, $currentFieldValue)) {
        Log::info("Current value does not match the expected value for '{$customField}'. Skipping update.");
        return;
    }

    // Replace the current value with the new value
    $updatedValue = array_diff($currentFieldValue, $currentValue); // Remove matching current values
    $updatedValue = array_merge($updatedValue, $newValue); // Add new values

    // Update the field in the database
    if (is_array($contact->$customField)) {
        // If the field is cast as JSON
        $contact->$customField = $updatedValue;
    } else {
        // Convert back to a scalar value if the field is not JSON-cast
        $contact->$customField = count($updatedValue) === 1 ? reset($updatedValue) : json_encode($updatedValue);
    }

    $contact->save();

    Log::info("Updated custom field '{$customField}' for Contact ID {$contact->id}. New value: " . json_encode($updatedValue));
}



private function goToAction($actionData, $actions, &$currentAction, &$visitedActions, $opportunity)
{
    $nextActionId = $actionData['actions_id'] ?? null;

    if (!$nextActionId) {
        Log::error("Go To action missing actions_id. Skipping.");
        return;
    }

    // Find the referenced action
    $nextAction = $actions->firstWhere('id', $nextActionId);

    if (!$nextAction) {
        Log::error("Go To action references an invalid actions_id: {$nextActionId}. Skipping.");
        return;
    }

    Log::info("Go To action executed. Jumping to Action ID: {$nextActionId}");

    // Update visited actions to prevent loops
    $visitedActions[] = $nextAction->id;

    // Decode action data
    $actionData = is_array($nextAction->action_data)
        ? $nextAction->action_data
        : json_decode($nextAction->action_data, true);

    if (is_array($actionData)) {
        // Perform the action logic dynamically based on the type
        switch ($nextAction->type) {
            case 'find_contact':
                Log::info("Re-performing find_contact action from Go To.");
                $this->findContact($actionData, $opportunity);
                break;
            case 'assign_user':
                Log::info("Re-performing assign_user action from Go To.");
                $this->assignUser($actionData, $opportunity);
                break;
            case 'remove_assigned_user':
                Log::info("Re-performing remove_assigned_user action from Go To.");
                $this->removeAssignedUser($actionData, $opportunity);
                break;
            case 'send_sms':
                Log::info("Re-performing send_sms action from Go To.");
                $this->sendSms($actionData, $opportunity);
                break;
            case 'send_email':
                Log::info("Re-performing send_email action from Go To.");
                $this->sendEmail($actionData, $opportunity);
                break;
            case 'send_whatsapp':
                Log::info("Re-performing send_whatsapp action from Go To.");
                $this->sendWhatsApp($actionData, $opportunity);
                break;
            case 'remove_opportunity':
                $this->removeOpportunity($actionData, $opportunity);
                break;
            case 'send_invoice':
                    $this->sendinvoice($actionData, $opportunity);
                    break;
            case 'update_contact':
                Log::info("Re-performing update_contact action from Go To.");
                $this->updateContact($actionData, $opportunity);
                break;
            case 'delete_contact':
                Log::info("Re-performing delete_contact action from Go To.");
                $this->deleteContact($opportunity);
                break;
            case 'update_custom_value':
                $this->updateCustomValue($actionData, $opportunity);
                break;
            default:
                Log::warning("Unsupported action type: {$nextAction->type} from Go To.");
        }
    } else {
        Log::warning("Invalid action data for action type: {$nextAction->type} in Go To.");
    }
}


}



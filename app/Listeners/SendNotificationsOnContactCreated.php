<?php

namespace App\Listeners;

use App\Events\ContactCreated;
use App\Jobs\GoalEventCheckJob;
use App\Models\Action;
use App\Models\Customers;
use App\Models\Opportunity;
use App\Models\Tasks;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowContact;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Twilio\Rest\Client as TwilioClient;

class SendNotificationsOnContactCreated
{
    private $context = [];
    /**
     * Handle the event.
     *
     * @param \App\Events\ContactCreated $event
     * @return void
     */
    public function handle(ContactCreated $event)
{
    $contact = $event->contact;
    
    // Fetch workflows related to contact creation
    $workflows = Workflow::whereHas('trigger', function($query) {
        $query->where('trigger_name', 'contact_created');
    })->get();

    foreach ($workflows as $workflow) {
        // Process actions in the order specified in actions_id
        $actionsIds = json_decode($workflow->actions_id, true);
        $actions = Action::whereIn('id', $actionsIds)
            ->orderByRaw("array_position(ARRAY[" . implode(',', $actionsIds) . "]::bigint[], id)") // PostgreSQL-compatible
            ->get();

        // Keep track of visited actions to avoid infinite loops
        $visitedActions = [];
        foreach ($actions as $action) {
            if (in_array($action->id, $visitedActions)) {
                Log::error("Detected infinite loop in Go To action. Workflow stopped.");
                break;
            }
            $visitedActions[] = $action->id;
            $actionData = is_array($action->action_data)
                ? $action->action_data
                : json_decode($action->action_data, true);

            // Ensure $actionData is an array and not null
            if (is_array($actionData)) {
                switch ($action->type) {
                    case 'set_event_start_date':
                        $context['event_start_date'] = $this->handleSetEventStartDateAction($actionData);
                        break;
                        case 'goal_event':
                            Log::info("Processing goal_event action for workflow ID: {$workflow->id}");
                            $goalMet = $this->handleGoalEvent($actionData, $workflow, $contact);
                        
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
                                $this->recheckGoalLater($workflow->id, $contact->id, $actionData, $delayInMinutes);
                                continue 2; // This will restart the loop and recheck the goal
                            }
                            break;
                    case 'wait':
                            $this->handleWaitAction($actionData, $context['event_start_date'] ?? null);
                            break;
                    case 'find_contact':
                        Log::info("Running find_contact action.");
                            if (!$this->findContact($actionData, $contact)) {
                                // Stop the workflow if the contact does not match
                                Log::info("Contact does not match find_contact criteria. Stopping workflow.");
                                break 2; // Exit from the workflow loop
                            }
                            break;
                    case 'drip':
                                $this->handleDripAction($actionData, $contact, $actions->toArray());
                                break;
                    case 'split':
                                Log::info("Running split action for workflow ID: {$workflow->id}");
                                // Call the new function to handle the split logic
                                $this->handleSplitAction($actionData, $workflow->id);
                                break;
                            
                    case 'number_formatter':
                                Log::info("Running number_formatter action for contact ID: {$contact->id}");
                                
                                if (isset($actionData['type'])) {
                                    switch ($actionData['type']) {
                                        case 'format_phonenumber':
                                            $this->formatPhoneNumberAction($actionData, $contact);
                                            break;
                            
                                        case 'random_number':
                                            $this->generateRandomNumberAction($actionData, $contact);
                                            break;
                            
                                        default:
                                            Log::warning("Invalid or unsupported number formatter type in action data: " . json_encode($actionData));
                                            break;
                                    }
                                } else {
                                    Log::warning("Type not set in action data: " . json_encode($actionData));
                                }
                                break;
                    case 'add_task':
                            $this->handleAddTaskAction($actionData);
                            break;        
                            
                    case 'assign_user':
                        Log::info("Running assign_user action.");
                            $this->assignUser($actionData, $contact);
                            break;
                    case 'add_contact_tag':
                        Log::info("Running add_contact_tag action for contact ID: {$contact->id}");
                        $this->addContactTag($actionData, $contact);
                        break;
                    case 'remove_contact_tag':
                        Log::info("Running remove_contact_tag action for contact ID: {$contact->id}");
                        $this->removeContactTag($actionData, $contact);
                        break;
                    case 'add_to_workflow':
                        $this->addtoworkflow($actionData, $contact);
                        break;
                    case 'create_opportunity':
                        $this->createOpportunity($actionData, $contact);
                        break;
                    case 'send_sms':
                        $this->sendSms($actionData, $contact);
                        break;
                    case 'send_email':
                        $this->sendEmail($actionData, $contact);
                        break;
                    case 'send_whatsapp':
                        $this->sendWhatsApp($actionData, $contact);
                        break;
                    case 'find_contact':
                        Log::info("Running find_contact action.");
                            if (!$this->findContact($actionData, $contact)) {
                                // Stop the workflow if the contact does not match
                                Log::info("Contact does not match find_contact criteria. Stopping workflow.");
                                break 2; // Exit from the workflow loop
                            }
                            break;
                    case 'update_contact':
                        $this->updateContact($actionData, $contact);
                        break;
                    case 'delete_contact':
                        $this->deleteContact($contact);
                        break;
                    case 'text_formatter':
                        Log::info("Running text_formatter action for contact ID: {$contact->id}");
                            if (isset($actionData['type']) && $this->isTextFormatterAction($actionData['type'])) {
                                $this->handleTextFormatterAction($actionData, $contact);
                            } else {
                                Log::warning("Invalid or unsupported text formatter type in action data: " . json_encode($actionData));
                            }
                        break;
                        
                    case 'go_to':
                            Log::info("Running go_to action.");
                            $this->goToAction($actionData, $actions, $action, $visitedActions, $contact);
                            break;
                    default:
                        Log::warning("Unsupported action type: {$action->type}");
                }
            } else {
                Log::warning("Invalid action data for action type: {$action->type}");
            }
        }
    }
}

private function handleGoalEvent(array $actionData, Workflow $workflow, Customers $contact)
    {
        Log::info("Checking goal event for workflow {$workflow->id}");
    
        // Check if the goal is met using the action data and the estimate
        $goalMet = $this->isGoalMet($actionData, $contact, $workflow);
        if ($goalMet) {
            Log::info("Goal met for workflow {$workflow->id}");
        } else {
            Log::info("Goal not met for workflow {$workflow->id}");
        }
    
        return $goalMet; // Return whether the goal is met or not
    }
    

    private function isGoalMet(array $actionData, Customers $contact, $workflow)
{
        if (!$contact) {
        Log::warning("No associated contact found for estimate ID: {$contact->id}");
        return false;
    }

    Log::info("Found associated contact ID: {$contact->id} for estimate ID: {$contact->id}");

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
                    Log::info("Tag '{$tagToCheck}' added to contact ID {$contact->id} for ID: {$contact->id}");
                    return true; // Goal is met
                }
            }
            break;

        case 'removed contact tag':
            // Check if any of the specified tags have been removed from the contact
            foreach ($tagsToCheck as $tagToCheck) {
                if (!in_array($tagToCheck, $tags)) {
                    Log::info("Tag '{$tagToCheck}' removed from contact ID {$contact->id} for estimate ID: {$contact->id}");
                    return true; // Goal is met
                }
            }
            break;

        default:
            Log::warning("Unknown action type: {$actionType}");
            break;
    }

    Log::info("Goal not met for workflow {$workflow->id} with estimate ID: {$contact->id}");
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


private function formatPhoneNumberAction(array $actionData, $contact)
{
    if (!isset($actionData['field'], $actionData['to_format'], $actionData['country_code'])) {
        Log::warning("Invalid or incomplete action data for format_phonenumber: " . json_encode($actionData));
        return;
    }

    $phoneNumber = $contact->{$actionData['field']}; // Access primary_phone field
    if (empty($phoneNumber)) {
        Log::warning("No phone number found for contact ID: {$contact->id}");
        return;
    }

    try {
        $formattedPhone = $this->formatPhoneNumber($phoneNumber, $actionData['country_code'], $actionData['to_format']);
        Log::info("Formatted phone number for contact ID: {$contact->id} - From: $phoneNumber To: $formattedPhone");

        // Save the formatted phone number back to the database (optional)
        $contact->{$actionData['field']} = $formattedPhone;
        $contact->save();
    } catch (\Exception $e) {
        Log::error("Error formatting phone number for contact ID: {$contact->id}. Error: " . $e->getMessage());
    }
}

/**
 * Format a phone number based on the given format and country code.
 *
 * @param string $phoneNumber
 * @param string $countryCode
 * @param string $format
 * @return string
 */
private function formatPhoneNumber($phoneNumber, $countryCode, $format)
{
    // Remove all non-digit characters
    $numericPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

    // Ensure the phone number starts with the appropriate country code
    $internationalPrefix = '+91'; // Default country code for India
    if ($countryCode === 'India') {
        $internationalPrefix = '+91';
    } elseif ($countryCode === 'US') {
        $internationalPrefix = '+1';
    }
    // Add additional country codes as needed

    switch (strtolower($format)) {
        case 'e164': // E.164 format: +[CountryCode][NationalNumber]
            return $internationalPrefix . $numericPhone;

        case 'international': // International format: +[CountryCode] [AreaCode] [Number]
            // Example: +91 98765 43210
            return $internationalPrefix . ' ' . substr($numericPhone, 0, 5) . ' ' . substr($numericPhone, 5);

        case 'international_no_countrycode': // Without country code: [AreaCode] [Number]
            // Example: 98765 43210
            return substr($numericPhone, 0, 5) . ' ' . substr($numericPhone, 5);

        case 'international_no_hyphens': // International without hyphens: +[CountryCode][Number]
            // Example: +919876543210
            return $internationalPrefix . $numericPhone;

        case 'international_no_symbols': // International with no spaces or symbols: +[CountryCode][Number]
            // Example: +919876543210
            return $internationalPrefix . $numericPhone;

        case 'national': // National format with parentheses: (AreaCode) Number
            // Example: (09876) 543210
            return '(' . substr($numericPhone, 0, 5) . ') ' . substr($numericPhone, 5);

        case 'national_no_parenthesis': // National without parentheses: AreaCode Number
            // Example: 09876 543210
            return substr($numericPhone, 0, 5) . ' ' . substr($numericPhone, 5);

        case 'national_no_symbols': // National without symbols or spaces: [AreaCode][Number]
            // Example: 09876543210
            return $numericPhone;

        default:
            // Return as is if no matching format
            Log::warning("Unsupported phone number format: {$format}");
            return $phoneNumber;
    }
}

private function generateRandomNumberAction(array $actionData, $contact)
{
    // Get the lower and upper range for the random number generation
    $lowerRange = $actionData['lower_range'] ?? 0;
    $upperRange = $actionData['upper_range'] ?? 100;
    $decimalPoints = $actionData['decimal_points'] ?? 2;

    // Generate a random number within the specified range and decimal points
    $randomNumber = $this->generateRandomNumber($lowerRange, $upperRange, $decimalPoints);

    // Log the generated random number
    Log::info("Generated random number for contact ID: {$contact->id}: {$randomNumber}");

    // Optionally, store the generated random number in a field
    if (isset($actionData['field'])) {
        $contact->{$actionData['field']} = $randomNumber;  // Assuming a field exists in the contact to store this
        $contact->save();
    } else {
        Log::warning("No field specified to save the generated random number for contact ID: {$contact->id}");
    }
}

private function generateRandomNumber($lowerRange, $upperRange, $decimalPoints)
{
    // Generate a random float between lowerRange and upperRange
    $randomNumber = mt_rand($lowerRange * pow(10, $decimalPoints), $upperRange * pow(10, $decimalPoints)) / pow(10, $decimalPoints);

    return number_format($randomNumber, $decimalPoints);  // Format the number to specified decimal points
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


private function handleTextFormatterAction($actionData, &$contact)
{
    $field = $actionData['field'] ?? null;
    $type = $actionData['type'] ?? null;
    $value = $actionData['value'] ?? null;
    $length = $actionData['length'] ?? null;
    $delimiter = $actionData['delimiter'] ?? null;
    $replaceWith = $actionData['replace_with'] ?? null;

    if (!$field || !isset($contact[$field])) {
        Log::warning("Text Formatter: Field '{$field}' does not exist or is not specified.");
        return;
    }
    $originalValue = $contact[$field]; // Store the original value for logging
    $text = $contact[$field];

    switch ($type) {
        case 'upper_case':
            $contact[$field] = strtoupper($text);
            break;

        case 'lower_case':
            $contact[$field] = strtolower($text);
            break;

        case 'title_case':
            $contact[$field] = ucwords(strtolower($text));
            break;

        case 'capitalize':
            $contact[$field] = ucfirst(strtolower($text));
            break;

        case 'default_value':
            if (empty($text)) {
                $contact[$field] = $value; // Set the default value if the field is empty
                Log::info("Text Formatter: Set default value for '{$field}' to '{$value}' because the original value was empty.");
            }
            break;

        case 'trim':
            $contact[$field] = $length ? substr($text, 0, $length) : $text;
            break;

        case 'trim_whitespace':
            $contact[$field] = trim($text);
            break;

        case 'replace_text':
            $replaceWith = $actionData['replace_with'] ?? null;
            if ($replaceWith) {
                $contact[$field] = str_replace($actionData['value'], $replaceWith, $text);
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
                $contact[$field] = json_encode($splitText); // Store the result back to the field (as a JSON string)
            } else {
                Log::warning("Split Text: 'delimiter' is required for this action.");
            }
            break;

        case 'remove_html_tags':
            $contact[$field] = strip_tags($text);
            break;

        case 'extract_email':
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
                $contact[$field] = $matches[0];
            } else {
                Log::info("Extract Email: No email found in '{$field}'.");
            }
            break;

        case 'extract_url':
            if (preg_match('/https?:\/\/[^\s]+/', $text, $matches)) {
                $contact[$field] = $matches[0];
            } else {
                Log::info("Extract URL: No URL found in '{$field}'.");
            }
            break;

        default:
            Log::warning("Text Formatter: Unsupported action type '{$type}'.");
    }
    if ($originalValue !== $contact[$field]) {
        // Save changes to the database
        $contact->save();
        Log::info("Text Formatter: Updated '{$field}' from '{$originalValue}' to '{$contact[$field]}' and saved to database.");
    } else {
        Log::info("Text Formatter: No changes detected for '{$field}'.");
    }

    Log::info("Text Formatter: Processed '{$type}' on field '{$field}' with result: {$contact[$field]}");
}


public function assignUser($actionData, $contact)
{
    Log::info("Running assign_user action.");

    // Check if 'name' in action data is an array (user IDs are passed as array)
    $userIds = $actionData['name'];

    // Fetch the users based on provided user IDs
    $users = User::whereIn('id', $userIds)->get();

    // Check if users exist
    if ($users->isEmpty()) {
        Log::error("No users found for the given IDs: " . implode(', ', $userIds));
        return;
    }

    // Assign each user to the contact
    foreach ($users as $user) {
        try {
            // Assuming you have a relationship like 'users' on the 'Customers' model
            $contact->users()->attach($user->id);
            Log::info("User {$user->name} assigned to contact {$contact->name}.");
        } catch (\Exception $e) {
            Log::error("Failed to assign user {$user->name} to contact {$contact->name}: " . $e->getMessage());
        }
    }
}


private function findContact(array $actionData, $contact)
{
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
    return true; // Contact matches
}


/**
 * Update contact fields dynamically based on action data.
 *
 * @param array $actionData Action data containing field-value pairs for updating.
 * @param \App\Models\Customers $contact The contact model instance to be updated.
 * @return void
 */
public function updateContact(array $actionData, $contact)
{
    try {
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
        Log::error("Error updating contact ID {$contact->id}: " . $e->getMessage(), [
            'action_data' => $actionData,
        ]);
    }
}

public function deleteContact($contact)
{
    try {
        // Ensure the contact exists before attempting to delete
        if (!$contact) {
            Log::warning("Contact does not exist for deletion.");
            return;
        }

        $contactId = $contact->id;

        // Delete the contact from the jo_customers table
        $contact->delete();

        Log::info("Contact ID {$contactId} deleted successfully.");
    } catch (Exception $e) {
        // Log the error for debugging
        Log::error("Error deleting contact ID {$contact->id}: " . $e->getMessage());
    }
}


private function addtoworkflow(array $actionData, $contact)
{
    // Ensure the actionData has a 'workflow_id'
    $workflowId = $actionData['workflow_id'];

    // Find the workflow
    $workflow = Workflow::find($workflowId);

    if ($workflow) {
        // Check if the contact is already linked to the workflow
        $existingRelation = WorkflowContact::where('workflow_id', $workflowId)
                                            ->where('contact_id', $contact->id)
                                            ->first();

        if (!$existingRelation) {
            // Add the contact to the workflow
            WorkflowContact::create([
                'workflow_id' => $workflowId,
                'contact_id' => $contact->id,
            ]);

            Log::info("Added contact ID {$contact->id} to workflow ID {$workflow->id}");
        } else {
            Log::info("Contact ID {$contact->id} is already in workflow ID {$workflow->id}");
        }
    } else {
        Log::error("Workflow with ID {$workflowId} not found.");
    }
}

private function addContactTag(array $actionData, $contact)
{
    // Ensure `tags` data is present in actionData
    $tagsToAdd = $actionData['tags'] ?? [];

    if (empty($tagsToAdd)) {
        Log::warning("No tags provided in action_data to add.");
        return;
    }

    // Retrieve existing tags on the contact (assuming tags are stored as a JSON array)
    $existingTags = $contact->tags ?? []; // Default to an empty array if tags are null

    // Ensure tags are arrays and find the tags that are not yet added
    if (is_array($existingTags)) {
        $newTags = array_diff($tagsToAdd, $existingTags);

        if (empty($newTags)) {
            Log::info("All tags from action_data are already present for contact ID: {$contact->id}");
        } else {
            // Merge new tags into existing tags and save to contact
            $updatedTags = array_merge($existingTags, $newTags);
            $contact->tags = $updatedTags;
            $contact->save();

            Log::info("Added tags to contact ID {$contact->id}: " . json_encode($newTags));
        }
    } else {
        Log::error("Contact tags attribute is not an array for contact ID: {$contact->id}");
    }
}

private function removeContactTag(array $actionData, $contact)
{
    // Retrieve tags to remove from actionData, if specified
    $tagsToRemove = $actionData['tags'] ?? [];

    // Retrieve existing tags on the contact (assuming tags are stored as a JSON array)
    $existingTags = $contact->tags ?? []; // Default to an empty array if tags are null

    // If specific tags are provided in actionData, remove those tags
    if (!empty($tagsToRemove)) {
        if (is_array($existingTags)) {
            // Remove specified tags from the existing tags
            $updatedTags = array_diff($existingTags, $tagsToRemove);

            // Save the updated tags back to the contact
            $contact->tags = array_values($updatedTags); // Reset keys
            $contact->save();

            Log::info("Removed tags from contact ID {$contact->id}: " . json_encode($tagsToRemove));
        } else {
            Log::error("Contact tags attribute is not an array for contact ID: {$contact->id}");
        }
    } else {
        // If no specific tags are provided, clear all tags
        $contact->tags = [];
        $contact->save();

        Log::info("All tags removed from contact ID {$contact->id}");
    }
}

private function createOpportunity(array $actionData, $contact)
{
    try {
        // Create a new opportunity using action_data and estimate contact ID
        Opportunity::create([
            'select_pipeline' => $actionData['select_pipeline'],
            'select_stage' => $actionData['select_stage'],
            'opportunity_name' => $actionData['opportunity_name'],
            'opportunity_source' => $actionData['opportunity_source'],
            'lead_value' => $actionData['lead_value'],
            'opportunity_status' => $actionData['opportunity_status'],
            'contact_id' => $contact->id,
        ]);

        Log::info("Opportunity created successfully for contact ID: {$contact->id}");
    } catch (\Exception $e) {
        Log::error("Failed to create opportunity: " . $e->getMessage());
    }
}

    private function sendSms(array $data, $contact)
    {
        $phoneNumber = $data['test_phone_number'] ?? $contact->primary_phone;
        $message = str_replace('{{contact.name}}', $contact->name, $data['message']);
        Log::info("Preparing to send SMS to {$phoneNumber} with message: {$message}");
        // Validate Twilio configuration
        try {
            $this->validateTwilioConfig();
            Log::info("Twilio configuration validated successfully.");
        } catch (\Exception $e) {
            Log::error("Twilio configuration validation failed: " . $e->getMessage());
            return; // Exit if validation fails
        }

        $client = new TwilioClient(config('services.twilio.sid'), config('services.twilio.token'));
        
        try {
            Log::info("Attempting to send SMS via Twilio.");
            $client->messages->create(
                $phoneNumber,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $message
                ]
            );

            Log::info("SMS sent to {$phoneNumber}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send SMS: " . $e->getMessage());
        }
    }

    private function sendEmail(array $data, $contact)
    {
        // Ensure necessary fields are present in the action data
        $message = str_replace('{{contact.name}}', $contact->name, $data['message']);

        try {
            Mail::raw($message, function ($mail) use ($data, $contact) {
                $mail->from($data['from_email'], $data['from_name'])
                    ->to($contact->primary_email)
                    ->subject($data['subject']);

                // Add attachments
                if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if ($attachment['type'] == 'file' && isset($attachment['url'])) {
                        $mail->attach($attachment['url']);
                    }
                }
                }
            });

            Log::info("Email sent to {$contact->primary_email}");
        } catch (\Exception $e) {
            Log::error("Failed to send email: " . $e->getMessage());
        }
    }

    // private function sendEmail(array $data, $contact)
    // {
    //     // Generate a unique mail ID
    // $mailId = uniqid();

    // // Insert the tracking record
    // DB::table('email_trackings')->insert([
    //     'crmid' => $contact->id,
    //     'mailid' => $mailId,
    //     'access_count' => 0,
    //     'click_count' => 0,
    //     'created_at' => now(),
    //     'updated_at' => now()
    // ]);

    // // Use your Ngrok URL for tracking
    // $ngrokUrl = "https://47fe-49-37-201-165.ngrok-free.app";
    // $trackingPixelUrl = "$ngrokUrl/track_open?mailid=$mailId";
    // $messageContent = str_replace('{{contact.name}}', $contact->name, $data['message']);

    //     try {
    //         Mail::send('emails.opportunity', compact('messageContent', 'trackingPixelUrl', 'trackableLinkUrl'), function ($mail) use ($data, $contact) {
    //             $mail->from($data['from_email'], $data['from_name'])
    //                 ->to($contact->primary_email)
    //                 ->subject($data['subject']);

    //             // Attach files if present
    //             if (!empty($data['attachments'])) {
    //                 foreach ($data['attachments'] as $attachment) {
    //                     if (isset($attachment['url'])) {
    //                         $mail->attach($attachment['url']);
    //                     }
    //                 }
    //             }
    //         });

    //         Log::info("Email sent to {$contact->primary_email}");
    //     } catch (\Exception $e) {
    //         Log::error("Failed to send email: " . $e->getMessage());
    //     }
    // }


    private function sendWhatsApp(array $data, $contact)
    {
        $whatsappNumber = $data['test_phone_number'] ?? $contact->primary_phone;
        $message = str_replace('{{contact.name}}', $contact->name, $data['message']);
        
        // Log the WhatsApp number and message content
        Log::info("Preparing to send WhatsApp message.");
        Log::info("WhatsApp Number: {$whatsappNumber}");
        Log::info("Message: {$message}");
        // Validate Twilio WhatsApp configuration
        $this->validateTwilioConfig('whatsapp');

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $whatsappFrom = config('services.twilio.whatsapp_from');

        Log::info("Twilio SID: {$sid}");
        Log::info("Twilio Token: {$token}");
        Log::info("Twilio WhatsApp 'From' Number: {$whatsappFrom}");
        Log::info("TWILIO_WHATSAPP_NUMBER: " . env('TWILIO_WHATSAPP_NUMBER'));

        $client = new TwilioClient(config('services.twilio.sid'), config('services.twilio.token'));

        try {
            $client->messages->create(
                "whatsapp:{$whatsappNumber}",
                [
                    'from' => $whatsappFrom,
                    'body' => $message
                ]
            );

            Log::info("WhatsApp message sent to {$whatsappNumber}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp message: " . $e->getMessage());
        }
    }

    private function validateTwilioConfig($type = 'sms')
    {
        // $sid = env('TWILIO_SID');
        // $token = env('TWILIO_AUTH_TOKEN');
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $number= config('services.twilio.from');
        // $number = $type === 'whatsapp' ? env('TWILIO_WHATSAPP_NUMBER') : env('TWILIO_PHONE_NUMBER');

        if (empty($sid) || empty($token) || empty($number)) {
            Log::error("Twilio credentials are missing. SID: {$sid}, Token: {$token}, Phone Number: {$number}");
            throw new \Exception("Twilio credentials are missing for {$type}.");
        }
    }

    private function goToAction($actionData, $actions, &$currentAction, &$visitedActions, $contact)
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
                $this->findContact($actionData, $contact);
                break;
            case 'assign_user':
                Log::info("Running assign_user action.");
                    $this->assignUser($actionData, $contact);
                    break;
            case 'add_contact_tag':
                Log::info("Running add_contact_tag action for contact ID: {$contact->id}");
                $this->addContactTag($actionData, $contact);
                break;
            case 'remove_contact_tag':
                Log::info("Running remove_contact_tag action for contact ID: {$contact->id}");
                $this->removeContactTag($actionData, $contact);
                break;
            case 'add_to_workflow':
                $this->addtoworkflow($actionData, $contact);
                break;
            case 'create_opportunity':
                $this->createOpportunity($actionData, $contact);
                break;
            case 'send_sms':
                $this->sendSms($actionData, $contact);
                break;
            case 'send_email':
                $this->sendEmail($actionData, $contact);
                break;
            case 'send_whatsapp':
                $this->sendWhatsApp($actionData, $contact);
                break;
            case 'update_contact':
                $this->updateContact($actionData, $contact);
                break;
            case 'delete_contact':
                $this->deleteContact($contact);
                break;
            default:
                Log::warning("Unsupported action type: {$nextAction->type} from Go To.");
        }
    } else {
        Log::warning("Invalid action data for action type: {$nextAction->type} in Go To.");
    }
}
}

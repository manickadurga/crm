<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\PaymentsReceived;
use App\Jobs\GoalEventCheckJob;
use App\Models\Action;
use App\Models\Customers;
use App\Models\Payments;
use App\Models\Tasks;
use App\Models\Workflow;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Twilio\Rest\Client as TwilioClient;

class SendNotificationsOnPaymentReceived
{
    private $context = [];

    public function handle(PaymentsReceived $event)
    {
        $payment = $event->payment;

    // Fetch workflows related to opportunity creation
    $workflows = Workflow::whereHas('trigger', function($query) {
        $query->where('trigger_name', 'payment_received');
    })->get();

    foreach ($workflows as $workflow) {
        $actionsIds = json_decode($workflow->actions_id, true);
        $actions = Action::whereIn('id', $actionsIds)
                ->orderByRaw("array_position(ARRAY[" . implode(',', $actionsIds) . "]::bigint[], id)") // PostgreSQL-compatible
                ->get();
        // Decode the filters for the trigger
        $filters = $this->decodeFilters($workflow->trigger->filters);

        // Check if filters should be applied or if none are provided
        if ($this->applyFilters($filters, $payment)) {
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

                if (is_array($actionData)) {
                    switch ($action->type) {
                        case 'goal_event':
                            Log::info("Processing goal_event action for workflow ID: {$workflow->id}");
                            $goalMet = $this->handleGoalEvent($actionData, $workflow, $payment);
                        
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
                                $this->recheckGoalLater($workflow->id, $payment->id, $actionData, $delayInMinutes);
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
                            if (!$this->findContact($actionData, $payment)) {
                                // Stop the workflow if the contact does not match
                                Log::info("Contact does not match find_contact criteria. Stopping workflow.");
                                break 2; // Exit the workflow loop
                            }
                            break;
                        case 'number_formatter':
                                Log::info("Running number_formatter action for payment ID: {$payment->id}");
                                $this->handleNumberFormatterAction($actionData, $payment);
                                break;
                        case 'math_operation':
                                    Log::info("Running math_operation action for estimate ID: {$payment->id}");
                                
                                    if (isset($actionData['field'], $actionData['operator'], $actionData['update_field'])) {
                                        // Call the performMathOperation function to handle the logic
                                        $this->performMathOperation($actionData, $payment);
                                    } else {
                                        Log::warning("Invalid or incomplete action data for math_operation on payment ID: {$payment->id}");
                                    }
                                    break;
                        case 'split':
                                        Log::info("Running split action for workflow ID: {$workflow->id}");
                                        // Call the new function to handle the split logic
                                        $this->handleSplitAction($actionData, $workflow->id);
                                        break;
                        case 'send_sms':
                            $this->sendSms($actionData, $payment);
                            break;
                        case 'send_email':
                            $this->sendEmail($actionData, $payment);
                            break;
                        case 'send_whatsapp':
                            $this->sendWhatsApp($actionData, $payment);
                            break;
                        case 'update_contact':
                            $this->updateContact($actionData, $payment);
                            break;
                        case 'delete_contact':
                            $this->deleteContact($payment);
                            break;
                        case 'update_custom_value':
                            $this->updateCustomValue($actionData, $payment);
                            break;
                        case 'text_formatter':
                                Log::info("Running text_formatter action for contact ID: {$payment->id}");
                                    if (isset($actionData['type']) && $this->isTextFormatterAction($actionData['type'])) {
                                        $this->handleTextFormatterAction($actionData, $payment);
                                    } else {
                                        Log::warning("Invalid or unsupported text formatter type in action data: " . json_encode($actionData));
                                    }
                                break;
                        case 'date_formatter':
                                    $this->handleDateFormatterAction($actionData, $payment);
                                    break;
                        case 'add_task':
                                        $this->handleAddTaskAction($actionData);
                                        break;
                        case 'go_to':
                            Log::info("Running go_to action.");
                            $this->goToAction($actionData, $actions, $action, $visitedActions, $payment);
                            break;
                        case 'drip':
                                $this->handleDripAction($actionData, $payment, $actions->toArray());
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

private function handleGoalEvent(array $actionData, Workflow $workflow, Payments $payment)
    {
        Log::info("Checking goal event for workflow {$workflow->id}");
    
        // Check if the goal is met using the action data and the estimate
        $goalMet = $this->isGoalMet($actionData, $payment, $workflow);
        if ($goalMet) {
            Log::info("Goal met for workflow {$workflow->id}");
        } else {
            Log::info("Goal not met for workflow {$workflow->id}");
        }
    
        return $goalMet; // Return whether the goal is met or not
    }
    

    private function isGoalMet(array $actionData, Payments $payment, $workflow)
{
    $contact = $payment->contact; // This will use the 'contact' relationship defined in Estimate model

    if (!$contact) {
        Log::warning("No associated contact found for payment ID: {$payment->id}");
        return false;
    }

    Log::info("Found associated contact ID: {$contact->id} for payment ID: {$payment->id}");

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
                    Log::info("Tag '{$tagToCheck}' added to contact ID {$contact->id} for ID: {$payment->id}");
                    return true; // Goal is met
                }
            }
            break;

        case 'removed contact tag':
            // Check if any of the specified tags have been removed from the contact
            foreach ($tagsToCheck as $tagToCheck) {
                if (!in_array($tagToCheck, $tags)) {
                    Log::info("Tag '{$tagToCheck}' removed from contact ID {$contact->id} for estimate ID: {$payment->id}");
                    return true; // Goal is met
                }
            }
            break;

        default:
            Log::warning("Unknown action type: {$actionType}");
            break;
    }

    Log::info("Goal not met for workflow {$workflow->id} with payment ID: {$payment->id}");
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
private function applyFilters($filters, $payment)
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

        if ($field === 'assigned_to') {
            // Fetch the customer's name based on contact_id in jo_customers table
            $customer = Customers::find($payment->contacts);
            if (!$customer) {
                Log::warning("Customer not found for contact_id {$payment->contacts}");
                return false;
            }

            // Check if the customer name matches the filter value
            if ($customer->name != $value) {
                Log::info("Filter mismatch for assigned_to: expected '{$value}', found '{$customer->name}'");
                return false;
            }
        } else {
            // Directly access the attribute and check if it exists
            if (!isset($payment->$field)) {
                Log::warning("Field {$field} does not exist on opportunity model.");
                return false;
            }

            // Check the filter condition based on the operator
            $fieldValue = $payment->$field;
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
    }

    return true;
}

protected function addPaymentToBatch($workflowId, $payment)
    {
        DB::table('payment_batches')->insert([
            'workflow_id' => $workflowId,
            'payment_id' => $payment->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Added payment ID {$payment->id} to batch for workflow ID {$workflowId}.");
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


private function performMathOperation(array $actionData, $payment)
{
    // Ensure necessary fields are present in action data
    if (isset($actionData['field'], $actionData['operator'], $actionData['update_field'])) {
        $field = $actionData['field'];  // Field to perform operation on (e.g., paymentdate)
        $operator = $actionData['operator'];  // Add or Subtract
        $days = $actionData['Days'] ?? 0;  // Days to add/subtract
        $months = $actionData['months'] ?? 0;  // Months to add/subtract
        $years = $actionData['years'] ?? 0;  // Years to add/subtract
        $updateField = $actionData['update_field'];  // Field to store result (e.g., duedate)

        // Ensure the field exists and is a valid date
        if (isset($payment->{$field}) && $this->isValidDate($payment->{$field})) {
            $date = Carbon::parse($payment->{$field});  // Parse the date from the field

            // Perform the math operation
            switch ($operator) {
                case 'add':
                    $date = $date->addDays($days)->addMonths($months)->addYears($years);
                    break;

                case 'sub':
                    $date = $date->subDays($days)->subMonths($months)->subYears($years);
                    break;

                default:
                    Log::warning("Invalid operator in math operation for payment ID: {$payment->id}");
                    return;
            }

            // Log the result
            Log::info("Performed math operation for payment ID: {$payment->id}. " .
                "Field: {$field}, Operator: {$operator}, Days: {$days}, Months: {$months}, Years: {$years}, " .
                "Updated Value: {$date->toDateString()}");

            // Save the result in the specified update field
            $payment->{$updateField} = $date->toDateString();  // Save only the date (no time)
            $payment->save();
        } else {
            Log::warning("Invalid or missing date field for math operation on payment ID: {$payment->id}");
        }
    } else {
        Log::warning("Missing necessary action data for math_operation on payment ID: {$payment->id}");
    }
}

private function isValidDate($date)
{
    try {
        Carbon::parse($date); // Try parsing the date with Carbon
        return true;
    } catch (\Exception $e) {
        return false;  // Return false if parsing fails
    }
}

private function handleNumberFormatterAction(array $actionData, $payment)
{
    $field = $actionData['field'] ?? null;
    $type = $actionData['type'] ?? null;

    // Skip the field check for random number type, since it doesn't require a field
    if ($type !== 'random_number') {
        $field = $actionData['field'] ?? null;

    if (!$field || !isset($payment->$field)) {
        Log::warning("Field {$field} not found in payment data.");
        return;
    }

    $value = $payment->$field;
}

    switch ($type) {
        case 'text_to_number':
            $decimalMark = $actionData['decimal_mark'] ?? 'period(123.45)';
            $plainNumericValue = $this->convertTextToNumber($value, $decimalMark);
            break;

        case 'format_number':
            $decimalMark = $actionData['decimal_mark'] ?? 'period(123.45)';
            $toFormat = $actionData['to_format'] ?? 'Comma for grouping and period for decimal';
            
            // Log the initial value
            Log::info("Initial value for {$field}: {$value}");

            // Convert to plain numeric value for database
            $plainNumericValue = $this->convertToDatabaseNumber($value, $decimalMark);
            Log::info("Converted value for database: {$plainNumericValue}");

            if (!is_numeric($plainNumericValue)) {
                Log::error("Failed to convert {$field} to numeric: {$value}");
                return;
            }

            // Update the database with the plain numeric value
            $payment->$field = $plainNumericValue;
            $payment->save();

            // Format the value for display/logging
            $formattedValue = $this->formatNumber($plainNumericValue, $decimalMark, $toFormat);
            Log::info("Formatted value for {$field}: {$formattedValue}");
            return; // End the case after updating the value and logging

        case 'format_currency':
                $currency = $actionData['currency'] ?? 'USD';
                $currencyLocale = $actionData['currency_locale'] ?? 'en_US';
            
                // Format the currency
                $formattedResult = $this->formatCurrency($value, $currency, $currencyLocale);
            
                if (is_array($formattedResult)) {
                    $payment->$field = $formattedResult['numeric']; // Store plain numeric value in DB
                    $payment->save();
            
                    Log::info("Formatted currency for payment ID: {$payment->id}. Display value: {$formattedResult['formatted']}");
                } else {
                    Log::error("Failed to format currency for field {$field}.");
                }
                break;
            
            case 'random_number':
                    $lowerRange = $actionData['lower_range'] ?? 0;
                    $upperRange = $actionData['upper_range'] ?? 100;
                    $decimalPoints = $actionData['decimal_points'] ?? 2;
        
                    // Generate random number within the given range and decimal points
                    $randomNumber = $this->generateRandomNumber($lowerRange, $upperRange, $decimalPoints);
        
                    // Log the generated random number
                    Log::info("Generated random number: {$randomNumber}");
        
                    break;   

        default:
            Log::warning("Unsupported number formatter type: {$type}");
            return;
    }
}


private function convertTextToNumber($value, $decimalMark)
{
    // Replace decimal marks based on the specified type
    if ($decimalMark === 'comma(123,45)') {
        $value = str_replace('.', '', $value); // Remove grouping dots
        $value = str_replace(',', '.', $value); // Convert comma to decimal point
    } elseif ($decimalMark === 'period(123.45)') {
        $value = str_replace(',', '', $value); // Remove grouping commas
    }

    return floatval($value);
}

private function formatNumber($value, $decimalMark, $toFormat)
{
    $decimalSeparator = '.';
    $thousandsSeparator = ',';

    // Define separators based on the desired format
    if ($toFormat === 'Comma for grouping and period for decimal') {
        $thousandsSeparator = ',';
        $decimalSeparator = '.';
    } elseif ($toFormat === 'Period for grouping and comma for decimal') {
        $thousandsSeparator = '.';
        $decimalSeparator = ',';
    } elseif ($toFormat === 'Space for grouping and comma for decimal') {
        $thousandsSeparator = ' ';
        $decimalSeparator = ',';
    } elseif ($toFormat === 'Space for grouping and period for decimal') {
        $thousandsSeparator = ' ';
        $decimalSeparator = '.';
    }

    // Format the number using the specified separators
    return number_format($value, 2, $decimalSeparator, $thousandsSeparator);
}

private function convertToDatabaseNumber($value, $decimalMark)
{
    // Remove any non-numeric characters (including grouping separators like commas and periods) 
    // except for the decimal point
    $value = preg_replace('/[^\d.-]/', '', $value);
    
    // Normalize the decimal mark (depending on the format passed)
    if ($decimalMark === 'comma(123,45)') {
        // Convert commas to periods
        $value = str_replace(',', '.', $value);
    } elseif ($decimalMark === 'period(123.45)') {
        // No further action needed since period is the decimal separator
    } elseif ($decimalMark === 'space(123 45)') {
        // Remove spaces
        $value = str_replace(' ', '', $value);
    }

    // Return a float value
    return floatval($value); // This ensures that the value is numeric and compatible with the DB
}


private function formatCurrency($value, $currency, $currencyLocale)
{
    // Check and normalize the value to be numeric
    if (!is_numeric($value)) {
        Log::error("Invalid value for formatting currency: {$value}");
        return $value;
    }

    // Set locale and currency format based on provided data
    try {
        // Normalize the locale format (e.g., en-IN)
        $locale = str_replace(' ', '_', strtolower($currencyLocale));
        $fmt = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        // Format the currency
        $formattedValue = $fmt->formatCurrency($value, $currency);

        // Ensure no invalid characters for database storage
        $numericValue = preg_replace('/[^\d.-]/', '', $value);

        Log::info("Formatted currency for {$currency} in locale {$currencyLocale}: {$formattedValue}");
        return [
            'formatted' => $formattedValue, // For display purposes
            'numeric' => floatval($numericValue), // For database storage
        ];
    } catch (\Exception $e) {
        Log::error("Failed to format currency: " . $e->getMessage());
        return $value;
    }
}

private function generateRandomNumber($lowerRange, $upperRange, $decimalPoints)
{
    // Generate a random number within the specified range
    $randomNumber = mt_rand($lowerRange * 100, $upperRange * 100) / 100;

    // Round the number to the specified decimal points
    return number_format($randomNumber, $decimalPoints, '.', '');
}

private function handleDateFormatterAction($actionData, &$estimate)
{
    $type = $actionData['type'] ?? null;
    $field = $actionData['field'] ?? null;
    $startDateType = $actionData['start_date'] ?? null;
    $endDateType = $actionData['end_date'] ?? null;
    $specificStartDate = $actionData['specific_start_date'] ?? null;
    $specificEndDate = $actionData['specific_end_date'] ?? null;
    $toFormat = $actionData['to_format'] ?? 'Y-m-d'; // Default to ISO format

    // Convert "DD-MM-YYYY" to Carbon-compatible format
    $toFormat = str_replace(['YYYY', 'DD', 'MM'], ['Y', 'd', 'm'], $toFormat);

    // Date Handling Helper
    $getDateValue = function ($dateType, $specificDate = null) use ($estimate) {
        switch ($dateType) {
            case 'specific_date':
                return $specificDate ? Carbon::createFromFormat('Y-m-d', $specificDate) : null;
            case 'current_date':
                return Carbon::now();
            default:
                return isset($estimate[$dateType]) ? Carbon::parse($estimate[$dateType]) : null;
        }
    };

    try {
        switch ($type) {
            case 'format_dates':
                // Determine the date source: field or explicitly specified date
                if (!empty($startDateType)) {
                    $date = $getDateValue($startDateType, $specificStartDate);
                } elseif (!empty($field) && isset($estimate[$field])) {
                    $date = Carbon::parse($estimate[$field]); // Use the field's value
                } else {
                    Log::warning("Date Formatter: Unable to determine the date for formatting.");
                    break;
                }
            
                if ($date) {
                    // Format for display or processing
                    $formattedDateForDisplay = $date->format($toFormat);
            
                    // Keep the original format for database storage
                    $dateForDatabase = $date->format('Y-m-d');
            
                    if ($field) {
                        $originalValue = $estimate[$field] ?? null;
                        $estimate[$field] = $dateForDatabase; // Save in database format
                        $estimate->save();
                        Log::info("Date Formatter: Updated '{$field}' from '{$originalValue}' to '{$formattedDateForDisplay}' (stored as '{$dateForDatabase}').");
                    } else {
                        Log::info("Date Formatter: Formatted date to '{$formattedDateForDisplay}' without updating any field.");
                    }
                } else {
                    Log::warning("Date Formatter: Unable to determine the date for formatting.");
                }
                break;
            

                case 'compare_dates':
                    $startDate = $getDateValue($startDateType, $specificStartDate);
                    $endDate = $getDateValue($endDateType, $specificEndDate);
                
                    if ($startDate && $endDate) {
                        // Normalize both dates to midnight
                        $startDateNormalized = $startDate->startOfDay();
                        $endDateNormalized = $endDate->startOfDay();
                
                        // Ensure Start Date is always less than or equal to End Date for absolute difference
                        if ($startDateNormalized->greaterThan($endDateNormalized)) {
                            Log::warning("Date Comparison: Start date '{$startDateNormalized->toDateString()}' is greater than end date '{$endDateNormalized->toDateString()}'. Adjusting order for absolute difference.");
                            [$startDateNormalized, $endDateNormalized] = [$endDateNormalized, $startDateNormalized];
                        }
                
                        // Calculate the absolute difference in days
                        $differenceInDays = $startDateNormalized->diffInDays($endDateNormalized);
                        Log::info("Date Comparison: Difference between dates is {$differenceInDays} days. (End: {$endDateNormalized->toDateString()}, Start: {$startDateNormalized->toDateString()})");
                    } else {
                        Log::warning("Date Comparison: One or both dates could not be determined. Start: {$startDate}, End: {$endDate}");
                    }
                    break;
                
                
            default:
                Log::warning("Date Formatter: Unsupported action type '{$type}'.");
        }
    } catch (\Exception $e) {
        Log::error("Date Formatter: Error processing action - {$e->getMessage()}");
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


private function handleTextFormatterAction($actionData, &$payment)
{
    $field = $actionData['field'] ?? null;
    $type = $actionData['type'] ?? null;
    $value = $actionData['value'] ?? null;
    $length = $actionData['length'] ?? null;
    $delimiter = $actionData['delimiter'] ?? null;
    $replaceWith = $actionData['replace_with'] ?? null;

    if (!$field || !isset($payment[$field])) {
        Log::warning("Text Formatter: Field '{$field}' does not exist or is not specified.");
        return;
    }
    $originalValue = $payment[$field]; // Store the original value for logging
    $text = $payment[$field];

    switch ($type) {
        case 'upper_case':
            $payment[$field] = strtoupper($text);
            break;

        case 'lower_case':
            $payment[$field] = strtolower($text);
            break;

        case 'title_case':
            $payment[$field] = ucwords(strtolower($text));
            break;

        case 'capitalize':
            $payment[$field] = ucfirst(strtolower($text));
            break;

        case 'default_value':
            if (empty($text)) {
                $payment[$field] = $value; // Set the default value if the field is empty
                Log::info("Text Formatter: Set default value for '{$field}' to '{$value}' because the original value was empty.");
            }
            break;

        case 'trim':
            $payment[$field] = $length ? substr($text, 0, $length) : $text;
            break;

        case 'trim_whitespace':
            $payment[$field] = trim($text);
            break;

        case 'replace_text':
            $replaceWith = $actionData['replace_with'] ?? null;
            if ($replaceWith) {
                $payment[$field] = str_replace($actionData['value'], $replaceWith, $text);
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
                $payment[$field] = json_encode($splitText); // Store the result back to the field (as a JSON string)
            } else {
                Log::warning("Split Text: 'delimiter' is required for this action.");
            }
            break;

        case 'remove_html_tags':
            $payment[$field] = strip_tags($text);
            break;

        case 'extract_email':
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
                $payment[$field] = $matches[0];
            } else {
                Log::info("Extract Email: No email found in '{$field}'.");
            }
            break;

        case 'extract_url':
            if (preg_match('/https?:\/\/[^\s]+/', $text, $matches)) {
                $payment[$field] = $matches[0];
            } else {
                Log::info("Extract URL: No URL found in '{$field}'.");
            }
            break;

        default:
            Log::warning("Text Formatter: Unsupported action type '{$type}'.");
    }
    if ($originalValue !== $payment[$field]) {
        // Save changes to the database
        $payment->save();
        Log::info("Text Formatter: Updated '{$field}' from '{$originalValue}' to '{$payment[$field]}' and saved to database.");
    } else {
        Log::info("Text Formatter: No changes detected for '{$field}'.");
    }

    Log::info("Text Formatter: Processed '{$type}' on field '{$field}' with result: {$payment[$field]}");
}

private function findContact(array $actionData, $payment)
    {
        $contactId = $payment->contacts;

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


public function updateContact(array $actionData, $payment)
{
    try {
        // Fetch the contact using the contact_id from the opportunity
        $contact = \App\Models\Customers::find($payment->contacts);

        if (!$contact) {
            Log::warning("Contact not found for contact ID {$payment->contacts}.");
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
        Log::error("Error updating contact for payment ID {$payment->id}: " . $e->getMessage(), [
            'action_data' => $actionData,
        ]);
    }
}

public function deleteContact($payment)
{
    try {
        // Fetch the contact using the contact_id from the opportunity
        $contact = \App\Models\Customers::find($payment->contacts);

        if (!$contact) {
            Log::warning("Contact not found for contact ID {$payment->contacts}. Deletion aborted.");
            return;
        }

        // Delete the contact
        $contact->delete();

        Log::info("Contact ID {$contact->id} deleted successfully.");
    } catch (Exception $e) {
        // Log the error for debugging
        Log::error("Error deleting contact for opportunity ID {$payment->id}: " . $e->getMessage());
    }
}

private function sendSms(array $data, $payment)
    {
        // Fetch the customer's phone number using contact_id
    $customer = Customers::find($payment->contacts);
    $phoneNumber = $data['test_phone_number'] ?? $customer->primary_phone;

    if (!$phoneNumber) {
        Log::error("No phone number found for contact_id: {$payment->contacts}");
        return;
    }

    $message = str_replace('{{payment.number}}', $payment->invoice_number, $data['message']);

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
                'body' => $message
            ]
        );

        Log::info("SMS sent to {$phoneNumber}: {$message}");
    } catch (\Exception $e) {
        Log::error("Failed to send SMS: " . $e->getMessage());
    }
}

private function sendEmail(array $data, $payment)
{
    $customer = Customers::find($payment->contacts);

    if (!$customer || !$customer->primary_email) {
        Log::error("No primary email found for contact_id: {$payment->contacts}");
        return;
        }

    // Generate a unique mail ID
    $mailId = uniqid();

    // Insert the tracking record
    DB::table('email_trackings')->insert([
        'crmid' => $payment->contacts,
        'mailid' => $mailId,
        'access_count' => 0,
        'click_count' => 0,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // Use your Ngrok URL for tracking
    $ngrokUrl = "https://e039-49-37-201-165.ngrok-free.app";
    $trackingPixelUrl = "$ngrokUrl/track_open?mailid=$mailId";
    $trackableLinkUrl = "$ngrokUrl/track_click?mailid=$mailId&redirect=" . urlencode("https://dummyurl.com/test");

    // Prepare the message content
    $messageContent = str_replace('{{payment.number}}', $payment->invoice_number, $data['message']);

    try {
        Mail::send('emails.opportunity', compact('messageContent', 'trackingPixelUrl', 'trackableLinkUrl'), function ($mail) use ($data, $customer) {
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

        Log::info("Email sent to {$customer->primary_email}");
    } catch (\Exception $e) {
        Log::error("Failed to send email: " . $e->getMessage());
    }
}

    private function sendWhatsApp(array $data, $payment)
    {
        // Fetch the customer's phone number using contact_id
    $customer = Customers::find($payment->contacts);
    $whatsappNumber = $data['test_phone_number'] ?? $customer->primary_phone;

    // Check if customer is found
    if (!$customer) {
        Log::error("No customer found for contact_id: {$payment->contacts}");
        return; // Exit if customer is not found
    }
    $whatsappNumber = $data['test_phone_number'] ?? $customer->primary_phone;

    if (!$whatsappNumber) {
        Log::error("No phone number found for contact_id: {$payment->contacts}");
        return;
    }
        $message = str_replace('{{payment.number}}', $payment->invoice_number, $data['message']);

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $twilioWhatsappNumber = config('services.twilio.whatsapp_from');

        $client = new TwilioClient($sid, $token);

        try {
            $client->messages->create("whatsapp:{$whatsappNumber}", [
                'from' => $twilioWhatsappNumber, 
                'body' => $message
            ]);
    
            Log::info("WhatsApp message sent to {$whatsappNumber}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp message: " . $e->getMessage());
        }
    }

    private function updateCustomValue($actionData, $invoice)
{
    if (!isset($actionData['custom_value'], $actionData['current_value'], $actionData['new_value'])) {
        Log::warning("Missing required fields in update_custom_value action data: " . json_encode($actionData));
        return;
    }

    $customField = $actionData['custom_value'];
    $currentValue = $actionData['current_value'];
    $newValue = $actionData['new_value'];

    // Log the values for debugging
    Log::info("Processing update for custom field '{$customField}' with current value '{$currentValue}' and new value '{$newValue}'.");

    // Check if the custom field exists in the model's attributes
    if (!array_key_exists($customField, $invoice->getAttributes())) {
        Log::warning("Custom field '{$customField}' does not exist in the Invoice model attributes.");
        return;
    }

    // Get the current value of the field
    $currentFieldValue = $invoice->$customField;

    // Log the current field value from the invoice
    Log::info("Current value of '{$customField}' in the invoice: " . json_encode($currentFieldValue));

    // Normalize currentFieldValue to an array (handles strings, numbers, arrays, and JSON)
    if (is_string($currentFieldValue)) {
        // Handle simple string values (e.g., 'USD') without decoding
        $currentFieldValue = [$currentFieldValue]; // Wrap string in an array
    } elseif (is_numeric($currentFieldValue)) {
        // Wrap numeric values in an array
        $currentFieldValue = [$currentFieldValue];
    } elseif (is_array($currentFieldValue)) {
        // If it's already an array, leave it as is
        $currentFieldValue = $currentFieldValue;
    } else {
        // Handle unsupported types (e.g., JSON objects or others)
        Log::error("Unsupported data type for custom field '{$customField}': " . gettype($currentFieldValue));
        return;
    }

    // Log the normalized current value of the field
    Log::info("Normalized current value for '{$customField}': " . json_encode($currentFieldValue));

    // Normalize `currentValue` and `newValue` into arrays (handles any non-array input)
    $currentValue = is_array($currentValue) ? $currentValue : [$currentValue];
    $newValue = is_array($newValue) ? $newValue : [$newValue];

    // Log the normalized current and new values for comparison
    Log::info("Normalized comparison values. Current value(s): " . json_encode($currentValue) . ", New value(s): " . json_encode($newValue));

    // Check if the current value matches the expected value
    if (!array_intersect($currentValue, $currentFieldValue)) {
        Log::info("Current value does not match the expected value for '{$customField}'. Skipping update.");
        return;
    }

    // Ensure the new value is a scalar number (not an array) before assigning it
    $newValue = $newValue[0]; // Get the first element if it's an array

    // Log the updated value before saving
    Log::info("Updated {$customField} with new value: " . json_encode($newValue));

    // Update the field with the new value
    $invoice->$customField = $newValue;

    // Save the updated invoice
    $invoice->save();
    Log::info("Updated custom field '{$customField}' for invoice ID {$invoice->id}.");
}


    private function goToAction($actionData, $actions, &$currentAction, &$visitedActions, $payment)
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
                $this->findContact($actionData, $payment);
                break;
            case 'send_sms':
                Log::info("Re-performing send_sms action from Go To.");
                $this->sendSms($actionData, $payment);
                break;
            case 'send_email':
                Log::info("Re-performing send_email action from Go To.");
                $this->sendEmail($actionData, $payment);
                break;
            case 'send_whatsapp':
                Log::info("Re-performing send_whatsapp action from Go To.");
                $this->sendWhatsApp($actionData, $payment);
                break;
            case 'update_contact':
                Log::info("Re-performing update_contact action from Go To.");
                $this->updateContact($actionData, $payment);
                break;
            case 'delete_contact':
                Log::info("Re-performing delete_contact action from Go To.");
                $this->deleteContact($payment);
                break;
            case 'update_custom_value':
                    $this->updateCustomValue($actionData, $payment);
                    break;
            default:
                Log::warning("Unsupported action type: {$nextAction->type} from Go To.");
        }
    } else {
        Log::warning("Invalid action data for action type: {$nextAction->type} in Go To.");
    }
}
}

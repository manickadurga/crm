<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ContactTag;
use App\Models\Workflow;
use App\Models\Action;
use App\Models\Customers;
use App\Models\Tasks;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;

class ContactTagUpdate
{
private $context = [];
public function handle(ContactTag $event)
{
    Log::info("ContactTagUpdated event triggered.");
    $customer = $event->customer;
    $tagsBeforeUpdate = $event->tagsBeforeUpdate ?? [];
    $tagsAfterUpdate = $customer->tags ?? [];
    $tagsBeforeUpdate = is_array($tagsBeforeUpdate) ? $tagsBeforeUpdate : json_decode($tagsBeforeUpdate, true);
    $tagsAfterUpdate = is_array($tagsAfterUpdate) ? $tagsAfterUpdate : json_decode($tagsAfterUpdate, true);
    Log::info("Tags before update: " . json_encode($tagsBeforeUpdate));
    Log::info("Tags after update: " . json_encode($tagsAfterUpdate));

    // Determine if tag was added or removed
    $tagsAdded = array_diff($tagsAfterUpdate, $tagsBeforeUpdate);
    $tagsRemoved = array_diff($tagsBeforeUpdate, $tagsAfterUpdate);
    Log::info("Tags added: " . json_encode($tagsAdded));
    Log::info("Tags removed: " . json_encode($tagsRemoved));

    // Fetch workflows associated with the 'contact_tag_updated' trigger
    $workflows = Workflow::whereHas('trigger', function ($query) {
        $query->where('trigger_name', 'contact_tag_updated');
    })->get();

    Log::info("Fetched workflows: " . $workflows->count());

    foreach ($workflows as $workflow) {
        Log::info("Processing workflow:", ['id' => $workflow->id, 'name' => $workflow->name]);
        $actionsIds = json_decode($workflow->actions_id, true);
        $actions = Action::whereIn('id', $actionsIds)
                ->orderByRaw("array_position(ARRAY[" . implode(',', $actionsIds) . "]::bigint[], id)") // PostgreSQL-compatible
                ->get();

        $filters = $this->decodeFilters($workflow->trigger->filters);

        // Apply filters based on the event type (tag added/removed)
        $tagMatched = false;

        foreach ($filters as $filter) {
            $field = $filter['field'];
            $value = $filter['value'];

            Log::info("Checking filter: field={$field}, value=" . json_encode($value));

            if ($field == 'tag_added' && is_array($value) && !empty($value)) {
                // Check if the added tag matches any of the values in the filter array
                if (array_intersect($value, $tagsAdded)) {
                    Log::info("Tag added matches: " . json_encode($value));
                    $tagMatched = true;
                }
            }

            if ($field == 'tag_removed' && is_array($value) && !empty($value)) {
                // Check if the removed tag matches any of the values in the filter array
                if (array_intersect($value, $tagsRemoved)) {
                    Log::info("Tag removed matches: " . json_encode($value));
                    $tagMatched = true;
                }
            }
        }

        if ($tagMatched) {

            Log::info("Fetched actions for workflow ID {$workflow->id}: " . $actions->count());
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
                Log::info("Executing action:", ['type' => $action->type, 'action_data' => $actionData]);

                if (is_array($actionData)) {
                    switch ($action->type) {
                        case 'set_event_start_date':
                            $context['event_start_date'] = $this->handleSetEventStartDateAction($actionData);
                            break;
                        case 'drip':
                                $this->handleDripAction($actionData, $customer, $actions->toArray());
                                break;
                        case 'wait':
                                $this->handleWaitAction($actionData, $context['event_start_date'] ?? null);
                                break;
                        case 'add_task':
                                $this->handleAddTaskAction($actionData);
                                break;
                        case 'send_sms':
                            $this->sendSms($actionData, $customer);
                            break;
                        case 'send_email':
                            $this->sendEmail($actionData, $customer);
                            break;
                        case 'send_whatsapp':
                            $this->sendWhatsApp($actionData, $customer);
                            break;
                        case 'update_contact':
                            $this->updatecontact($actionData, $customer);
                            break;
                        case 'update_custom_value':
                            $this->updateCustomValue($actionData, $customer);
                            break;
                        case 'split':
                                Log::info("Running split action for workflow ID: {$workflow->id}");
                                $this->handleSplitAction($actionData, $workflow->id);
                                break;
                        case 'go_to':
                                Log::info("Running go_to action.");
                                $this->goToAction($actionData, $actions, $action, $visitedActions, $customer);
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

private function handleDripAction(array $actionData, $opportunity,  $workflowActions)
{
    $batchSize = $actionData['batch_size'] ?? 3; 
    $dripInterval = $actionData['drip_interval'] ?? '3 minutes'; 
    $dripIntervalInMinutes = $this->convertIntervalToMinutes($dripInterval);
    
    // Fetch customers for the drip action
    $customers = Customers::all(); 
    $customerChunks = $customers->chunk($batchSize); // Split into batches
    
    // Loop through each batch
    foreach ($customerChunks as $batch) {
        foreach ($batch as $customer) {
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
    $phoneNumber = $customer->primary_phone ?? null; 
    $message = $actionData['message'] ?? "Hello, this is a notification message.";

    if (!$phoneNumber) {
        Log::warning("Customer with ID {$customer->id} does not have a phone number.");
        return;
    }
    $sid = config('services.twilio.sid');
    $token = config('services.twilio.token');
    $fromPhoneNumber = config('services.twilio.from');

    if (empty($sid) || empty($token) || empty($fromPhoneNumber)) {
        Log::error("Twilio credentials are missing. SID: {$sid}, Token: {$token}, From: {$fromPhoneNumber}");
        return;
    }

    try {
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
    $phoneNumber = $customer->primary_phone ?? null; 
    $message = $actionData['message'] ?? "Hello, this is a WhatsApp notification.";

    if (!$phoneNumber) {
        Log::warning("Customer with ID {$customer->id} does not have a phone number.");
        return;
    }
    $whatsappNumber = "whatsapp:{$phoneNumber}";
    $sid = config('services.twilio.sid');
    $token = config('services.twilio.token');
    $fromWhatsappNumber = config('services.twilio.whatsapp_from'); 

    if (empty($sid) || empty($token) || empty($fromWhatsappNumber)) {
        Log::error("Twilio WhatsApp credentials are missing. SID: {$sid}, Token: {$token}, From: {$fromWhatsappNumber}");
        return;
    }

    try {
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
    if (preg_match('/(\d+)\s*(minute|minutes)/', $dripInterval, $matches)) {
        return (int) $matches[1];
    }
    return 3; 
}

private function waitForDripInterval($intervalInMinutes)
{
    sleep($intervalInMinutes * 60); 
}


private function handleSetEventStartDateAction($actionData)
{
    Log::info("Handling Event Start Date Action with data: " . json_encode($actionData)); 
    if ($actionData['type'] == 'specific_date_or_time') {
        Log::info("Processing specific date or time action."); 
        try {
            // Use 24-hour format for time parsing (H for 24-hour format)
            $eventStartDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $actionData['select_date']);
            Log::info("Event Start Date set to: {$eventStartDate}"); 
            return $eventStartDate;
        } catch (\Exception $e) {
            Log::error("Failed to parse Event Start Date: " . $e->getMessage()); 
            Log::error("Error details: " . json_encode($actionData)); 
        }
    }

    if ($actionData['type'] == 'custom_field' && isset($actionData['select_date'])) {
        Log::info("Processing custom field action with select_date: " . $actionData['select_date']); 
    
        // Check if select_date is a valid date format or a column name
        if (strtotime($actionData['select_date'])) {
            try {
                // Parse the date and time exactly as it is without modifying it
                $eventStartDate = \Carbon\Carbon::parse($actionData['select_date']);
                Log::info("Event Start Date set to: {$eventStartDate}"); 
                return $eventStartDate;
            } catch (\Exception $e) {
                Log::error("Failed to parse custom field date: " . $e->getMessage());
                Log::error("Error details: " . json_encode($actionData)); 
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

private function decodeFilters($filters)
{
        return is_string($filters) ? json_decode($filters, true) : $filters;
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

private function sendSms(array $data, Customers $customer)
{
        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            $message = $data['message']; 
            $twilio->messages->create($customer->primary_phone, [
                'from' => env('TWILIO_PHONE_NUMBER'),
                'body' => $message
            ]);
            Log::info("SMS sent to {$customer->primary_phone}");
        } catch (\Exception $e) {
            Log::error("Failed to send SMS: " . $e->getMessage());
        }
    }

    
private function sendEmail(array $data, Customers $customer)
{
        $message = str_replace('{{customer.name}}', $customer->name, $data['message']);
        
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

            Log::info("Email sent to {$customer->primary_email}");
        } catch (\Exception $e) {
            Log::error("Failed to send email: " . $e->getMessage());
        }
}

private function sendWhatsApp(array $data, Customers $customer)
{
        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            $message = $data['message']; 
            $twilio->messages->create("whatsapp:{$customer->primary_phone}", [
                'from' => env('TWILIO_WHATSAPP_NUMBER'),
                'body' => $message
            ]);
            Log::info("WhatsApp sent to {$customer->primary_phone}");
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp: " . $e->getMessage());
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


private function goToAction($actionData, $actions, &$currentAction, &$visitedActions, $customer)
{
    $nextActionId = $actionData['actions_id'] ?? null;

    if (!$nextActionId) {
        Log::error("Go To action missing actions_id. Skipping.");
        return;
    }

   
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
        switch ($nextAction->type) {
            case 'send_sms':
                Log::info("Re-performing send_sms action from Go To.");
                $this->sendSms($actionData, $customer);
                break;
            case 'send_email':
                Log::info("Re-performing send_email action from Go To.");
                $this->sendEmail($actionData, $customer);
                break;
            case 'send_whatsapp':
                Log::info("Re-performing send_whatsapp action from Go To.");
                $this->sendWhatsApp($actionData, $customer);
                break;
            case 'update_contact':
                Log::info("Re-performing update_contact action from Go To.");
                $this->updateContact($actionData, $customer);
                break;
            case 'update_custom_value':
                $this->updateCustomValue($actionData, $customer);
                break;
            default:
                Log::warning("Unsupported action type: {$nextAction->type} from Go To.");
        }
    } else {
        Log::warning("Invalid action data for action type: {$nextAction->type} in Go To.");
    }
}
}

<?php

namespace App\Listeners;

use App\Events\ContactUpdated; // Ensure you have an event for contact updates
use App\Mail\WorkflowMail;
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
use Twilio\Rest\Client as TwilioClient;

class SendNotificationsOnContactUpdated
{
    private $context = [];
    /**
     * Handle the event.
     *
     * @param \App\Events\ContactUpdated $event
     * @return void
     */
    public function handle(ContactUpdated $event)
{
    Log::info("ContactUpdated event triggered.");
    
    $contact = $event->contact;
    
    // Fetch workflows with the 'contact_updated' trigger
    $workflows = Workflow::whereHas('trigger', function ($query) {
        $query->where('trigger_name', 'contact_updated');
    })->get();

    Log::info("Fetched workflows: " . $workflows->count());
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


            // Ensure actionData is an array
            if (is_array($actionData)) {
                // Process based on action type
                if ($action->type == 'find_contact') {
                    $this->findContact($actionData, $contact);
                }
                elseif ($action->type == 'number_formatter') {
                    if (isset($actionData['type']) && $actionData['type'] === 'format_phonenumber') {
                        $this->formatPhoneNumberAction($actionData, $contact);
                } else {
                        Log::warning("Invalid or unsupported number formatter type in action data: " . json_encode($actionData));
                    }
                }
                elseif ($action->type == 'add_task') {
                    $this->handleAddTaskAction($action->action_data);
                }
                elseif ($action->type == 'assign_user') {
                    $this->assignUser($actionData, $contact);
                }
                elseif ($action->type == 'remove_assigned_user') {
                    $this->removeAssignedUser($actionData, $contact);
                }
                elseif ($action->type == 'create_opportunity') {
                    $this->createOpportunity($actionData, $contact);
                }
                elseif ($action->type == 'send_email') {
                    $this->sendEmail($actionData, $contact);
                } elseif ($action->type == 'send_sms') {
                    $this->sendSms($actionData, $contact);
                } elseif ($action->type == 'send_whatsapp') {
                    $this->sendWhatsApp($actionData, $contact);
                }
                elseif ($action->type == 'add_contact_tag') {
                    $this->addContactTag($actionData, $contact);
                }
                elseif ($action->type == 'remove_contact_tag') {
                    $this->removeContactTag($actionData, $contact);
                }
                elseif ($action->type == 'remove_from_workflow') {
                    $this->removefromworkflow($actionData, $contact);
                }
                elseif ($action->type == 'update_contact') {
                    $this->updatecontact($actionData, $contact);
                }
                elseif ($action->type == 'delete_contact') {
                    $this->deleteContact($contact);
                }
                elseif ($action->type == 'update_custom_value') {
                    $this->updateCustomValue($actionData, $contact);
                }
                elseif ($action->type == 'split') {
                    $this->handleSplitAction($actionData, $workflow->id);
                }
                elseif ($action->type == 'go_to') {
                    $this->goToAction($actionData, $actions, $action, $visitedActions, $contact);
                }
                elseif ($action->type == 'drip') {
                    $this->handleDripAction($action->action_data, $contact, $actions->toArray());
                }
                elseif ($action->type == 'wait') {
                    $this->handleWaitAction($actionData);
                }
            } else {
                Log::warning("Invalid action data for action ID {$action->id}. Data: {$action->action_data}");
            }
        }
    }
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

public function removeAssignedUser($actionData, $contact)
{
    Log::info("Running remove_assigned_user action.");

    // Check if the contact has any assigned users
    $assignedUsers = $contact->users()->get();

    if ($assignedUsers->isEmpty()) {
        Log::info("No users assigned to contact {$contact->name}. No action needed.");
        return;
    }

    // Detach all users from the contact
    try {
        $contact->users()->detach();  // Detach all assigned users from the contact
        Log::info("All users removed from contact {$contact->name}.");
    } catch (\Exception $e) {
        Log::error("Failed to remove assigned users from contact {$contact->name}: " . $e->getMessage());
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

private function removeFromWorkflow(array $actionData, $contact)
    {
        $workflowId = $actionData['workflow_id'] ?? null; // Ensure the workflow_id is passed in actionData
        if (!$workflowId) {
            Log::warning("No workflow ID provided in action data.");
            return;
        }

        // Find the workflow-contact relationship entry to remove
        $workflowContact = WorkflowContact::where('workflow_id', $workflowId)
            ->where('contact_id', $contact->id)
            ->first();

        if ($workflowContact) {
            // Delete the relationship
            $workflowContact->delete();
            Log::info("Removed contact ID {$contact->id} from workflow ID {$workflowId}");
        } else {
            Log::warning("No relationship found between contact ID {$contact->id} and workflow ID {$workflowId}");
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
        // Create a new opportunity using action_data and contact ID
        Opportunity::create([
            'select_pipeline' => $actionData['select_pipeline'],
            'select_stage' => $actionData['select_stage'],
            'opportunity_name' => $actionData['opportunity_name'],
            'opportunity_source' => $actionData['opportunity_source'],
            'lead_value' => $actionData['lead_value'],
            'opportunity_status' => $actionData['opportunity_status'],
            'contact_id' => $contact->contacts,
        ]);

        Log::info("Opportunity created successfully for contact ID: {$contact->contacts}");
    } catch (\Exception $e) {
        Log::error("Failed to create opportunity: " . $e->getMessage());
    }
}

    

    private function sendSms(array $data, $contact)
    {
        $phoneNumber = $data['test_phone_number'] ?? $contact->primary_phone;
        $message = str_replace('{{contact.name}}', $contact->name, $data['message']);

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
            $client->messages->create(
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

    private function sendWhatsApp(array $data, $contact)
    {
        $whatsappNumber = $data['test_phone_number'] ?? $contact->primary_phone;
        $message = str_replace('{{contact.name}}', $contact->name, $data['message']);

        // Twilio configuration
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $twilioWhatsappNumber = "whatsapp:".config('services.twilio.whatsapp_from');

        Log::info("Twilio SID: {$sid}");
        Log::info("Twilio Auth Token: {$token}");
        Log::info("Twilio WhatsApp Number: {$twilioWhatsappNumber}");

        Log::info("Sending WhatsApp message to: {$whatsappNumber}");
        Log::info("Message: {$message}");

        if (empty($sid) || empty($token) || empty($twilioWhatsappNumber)) {
            Log::error("Twilio WhatsApp credentials are missing. SID: {$sid}, Token: {$token}, WhatsApp Number: {$twilioWhatsappNumber}");
            return;
        }

        $client = new TwilioClient($sid, $token);

        try {
            $client->messages->create(
                "whatsapp:{$whatsappNumber}", // WhatsApp To
                [
                    'from' => $twilioWhatsappNumber, // WhatsApp From
                    'body' => $message
                ]
            );

            Log::info("WhatsApp message sent to {$whatsappNumber}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp message: " . $e->getMessage());
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

    // Check if the field exists in the model's attributes
    if (!array_key_exists($customField, $contact->getAttributes())) {
        Log::warning("Custom field '{$customField}' does not exist in the Contact model attributes.");
        return;
    }

    // Get the current value of the field, assuming it’s cast as JSON
    $currentFieldValue = $contact->$customField;

    // Ensure the current value is an array
    if (!is_array($currentFieldValue)) {
        $currentFieldValue = json_decode($currentFieldValue, true) ?? [];
    }

    // Check if the current value matches
    if (!array_intersect($currentValue, $currentFieldValue)) {
        Log::info("Current value does not match the expected value for '{$customField}'. Skipping update.");
        return;
    }

    // Replace the current value with the new value
    $updatedValue = array_diff($currentFieldValue, $currentValue); // Remove current values
    $updatedValue = array_merge($updatedValue, $newValue); // Add new values

    // Update the field in the database
    $contact->$customField = $updatedValue;
    $contact->save();

    Log::info("Updated custom field '{$customField}' for Contact ID {$contact->id}. New value: " . json_encode($updatedValue));
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
            case 'update_custom_value':
                $this->updateCustomValue($actionData, $contact);
                break;
            default:
                Log::warning("Unsupported action type: {$nextAction->type} from Go To.");
        }
    } else {
        Log::warning("Invalid action data for action type: {$nextAction->type} in Go To.");
    }
}
}

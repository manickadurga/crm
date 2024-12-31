<?php

namespace App\Listeners;

use App\Events\DocumentCreated;
use App\Models\Action;
use App\Models\Customers;
use App\Models\Tasks;
use App\Models\Workflow;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Twilio\Rest\Client as TwilioClient;

class SendNotificationsOnDocumentCreated
{
    private $context = [];
   
    public function handle(DocumentCreated $event)
{
    Log::info("Running");
    $document = $event->document;

    // Fetch workflows related to document creation
    $workflows = Workflow::whereHas('trigger', function($query) {
        $query->where('trigger_name', 'document_created');
    })->get();

    foreach ($workflows as $workflow) {
        $actionsIds = json_decode($workflow->actions_id, true);
        $actions = Action::whereIn('id', $actionsIds)
                ->orderByRaw("array_position(ARRAY[" . implode(',', $actionsIds) . "]::bigint[], id)") // PostgreSQL-compatible
                ->get();
        // Decode the filters for the trigger
        $filters = $this->decodeFilters($workflow->trigger->filters);

        // Check if filters should be applied or if none are provided
        if ($this->applyFilters($filters, $document)) {
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
                        case 'set_event_start_date':
                                $context['event_start_date'] = $this->handleSetEventStartDateAction($actionData);
                                break;
                        case 'wait':
                                    $this->handleWaitAction($actionData, $context['event_start_date'] ?? null);
                                    break;
                        case 'find_contact':
                            Log::info("Running find_contact action.");
                            if (!$this->findContact($actionData, $document)) {
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
                        case 'create_contact':
                            $this->createOrUpdateContact($action->action_data);
                            break;
                        case 'update_contact':
                            $this->updateContact($actionData, $document);
                            break;
                        case 'delete_contact':
                            $this->deleteContact($document);
                            break;
                        case 'text_formatter':
                            Log::info("Running text_formatter action for contact ID: {$document->id}");
                                    if (isset($actionData['type']) && $this->isTextFormatterAction($actionData['type'])) {
                                        $this->handleTextFormatterAction($actionData, $document);
                                    } else {
                                        Log::warning("Invalid or unsupported text formatter type in action data: " . json_encode($actionData));
                                    }
                            break;    
                        case 'go_to':
                            Log::info("Running go_to action.");
                            $this->goToAction($actionData, $actions, $action, $visitedActions, $document);
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

public function updateContact(array $actionData, $document)
{
    try {
        // Fetch the contact using the contact_id from the document
        $contact = \App\Models\Customers::find($document->contact_id);

        if (!$contact) {
            Log::warning("Contact not found for contact ID {$document->contact_id}.");
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
        Log::error("Error updating contact for document ID {$document->id}: " . $e->getMessage(), [
            'action_data' => $actionData,
        ]);
    }
}

public function deleteContact($document)
{
    try {
        // Fetch the contact using the contact_id from the document
        $contact = \App\Models\Customers::find($document->contact_id);

        if (!$contact) {
            Log::warning("Contact not found for contact ID {$document->contact_id}. Deletion aborted.");
            return;
        }

        // Delete the contact
        $contact->delete();

        Log::info("Contact ID {$contact->id} deleted successfully.");
    } catch (Exception $e) {
        // Log the error for debugging
        Log::error("Error deleting contact for document ID {$document->id}: " . $e->getMessage());
    }
}

private function createOrUpdateContact(array $actionData)
{
    try {
        // Check if there are key identifiers (primary_email, primary_phone) to find an existing contact
        $query = Customers::query();

        if (!empty($actionData['primary_email'])) {
            $query->orWhere('primary_email', $actionData['primary_email']);
        }

        if (!empty($actionData['primary_phone'])) {
            $query->orWhere('primary_phone', $actionData['primary_phone']);
        }

        // Check if contact exists
        $contact = $query->first();

        // Validate and filter action_data to only include fields present in `jo_customers`
        $validFields = Schema::getColumnListing('jo_customers'); // Get table columns dynamically
        $filteredData = array_intersect_key($actionData, array_flip($validFields)); // Filter fields

        if ($contact) {
            // Update existing contact with provided fields
            $contact->update($filteredData);
            Log::info("Updated contact ID {$contact->id} with data: " . json_encode($filteredData));
        } else {
            // Create a new contact with provided fields
            $contact = Customers::create($filteredData);
            Log::info("Created new contact with ID {$contact->id} and data: " . json_encode($filteredData));
        }

        // Handle tags (if present in action_data)
        if (isset($filteredData['tags']) && is_array($filteredData['tags'])) {
            // Retrieve existing tags on the contact (ensure it's an array)
            $existingTags = $contact->tags ?? [];
            if (is_string($existingTags)) {
                $existingTags = json_decode($existingTags, true) ?? [];
            }

            // Merge and remove duplicates
            $updatedTags = array_unique(array_merge($existingTags, $filteredData['tags']));
            $contact->tags = $updatedTags; // Assuming `tags` is a JSON column
            $contact->save();

            Log::info("Updated tags for contact ID {$contact->id}: " . json_encode($updatedTags));
        }
    } catch (\Exception $e) {
        Log::error("Failed to create or update contact. Error: " . $e->getMessage());
    }
}


private function goToAction($actionData, $actions, &$currentAction, &$visitedActions, $estimate)
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
                $this->findContact($actionData, $estimate);
                break;
            case 'assign_user':
                Log::info("Re-performing assign_user action from Go To.");
                $this->assignUser($actionData, $estimate);
                break;
            case 'create_contact':
                Log::info("Re-performing create_contact action from Go To.");
                $this->createOrUpdateContact($nextAction->action_data);
                break;
            case 'update_contact':
                Log::info("Re-performing update_contact action from Go To.");
                $this->updateContact($actionData, $estimate);
                break;
            case 'delete_contact':
                Log::info("Re-performing delete_contact action from Go To.");
                $this->deleteContact($estimate);
                break;
            default:
                Log::warning("Unsupported action type: {$nextAction->type} from Go To.");
        }
    } else {
        Log::warning("Invalid action data for action type: {$nextAction->type} in Go To.");
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


private function handleTextFormatterAction($actionData, &$document)
{
    $field = $actionData['field'] ?? null;
    $type = $actionData['type'] ?? null;
    $value = $actionData['value'] ?? null;
    $length = $actionData['length'] ?? null;
    $delimiter = $actionData['delimiter'] ?? null;
    $replaceWith = $actionData['replace_with'] ?? null;

    if (!$field || !isset($document[$field])) {
        Log::warning("Text Formatter: Field '{$field}' does not exist or is not specified.");
        return;
    }
    $originalValue = $document[$field]; // Store the original value for logging
    $text = $document[$field];

    switch ($type) {
        case 'upper_case':
            $document[$field] = strtoupper($text);
            break;

        case 'lower_case':
            $document[$field] = strtolower($text);
            break;

        case 'title_case':
            $document[$field] = ucwords(strtolower($text));
            break;

        case 'capitalize':
            $document[$field] = ucfirst(strtolower($text));
            break;

        case 'default_value':
            if (empty($text)) {
                $document[$field] = $value; // Set the default value if the field is empty
                Log::info("Text Formatter: Set default value for '{$field}' to '{$value}' because the original value was empty.");
            }
            break;

        case 'trim':
            $document[$field] = $length ? substr($text, 0, $length) : $text;
            break;

        case 'trim_whitespace':
            $document[$field] = trim($text);
            break;

        case 'replace_text':
            $replaceWith = $actionData['replace_with'] ?? null;
            if ($replaceWith) {
                $document[$field] = str_replace($actionData['value'], $replaceWith, $text);
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
                $document[$field] = json_encode($splitText); // Store the result back to the field (as a JSON string)
            } else {
                Log::warning("Split Text: 'delimiter' is required for this action.");
            }
            break;

        case 'remove_html_tags':
            $document[$field] = strip_tags($text);
            break;

        case 'extract_email':
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
                $document[$field] = $matches[0];
            } else {
                Log::info("Extract Email: No email found in '{$field}'.");
            }
            break;

        case 'extract_url':
            if (preg_match('/https?:\/\/[^\s]+/', $text, $matches)) {
                $document[$field] = $matches[0];
            } else {
                Log::info("Extract URL: No URL found in '{$field}'.");
            }
            break;

        default:
            Log::warning("Text Formatter: Unsupported action type '{$type}'.");
    }
    if ($originalValue !== $document[$field]) {
        // Save changes to the database
        $document->save();
        Log::info("Text Formatter: Updated '{$field}' from '{$originalValue}' to '{$document[$field]}' and saved to database.");
    } else {
        Log::info("Text Formatter: No changes detected for '{$field}'.");
    }

    Log::info("Text Formatter: Processed '{$type}' on field '{$field}' with result: {$document[$field]}");
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

private function findContact(array $actionData, $document)
    {
        $contactId = $document->contact_id;

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

    public function assignUser($actionData, $document)
    {
        Log::info("Running assign_user action for estimate ID: {$document->id}.");
    
        // Fetch the contact associated with the estimate (contact_id from jo_estimates -> contacts field)
        $contactId = $document->contact_id; // Assuming 'contacts' is the field containing the contact ID(s)
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


private function decodeFilters($filters)
{
    return is_string($filters) ? json_decode($filters, true) : $filters;
}

/**
 * Apply filters to the document data
 *
 * @param array $filters
 * @param $document
 * @return bool
 */
private function applyFilters($filters, $document)
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
            $customer = Customers::find($document->contact_id);
            if (!$customer) {
                Log::warning("Customer not found for contact_id {$document->contact_id}");
                return false;
            }

            // Check if the customer name matches the filter value
            if ($customer->name != $value) {
                Log::info("Filter mismatch for assigned_to: expected '{$value}', found '{$customer->name}'");
                return false;
            }
        } else {
            // Directly access the attribute and check if it exists
            if (!isset($document->$field)) {
                Log::warning("Field {$field} does not exist on document model.");
                return false;
            }

            // Check the filter condition based on the operator
            $fieldValue = $document->$field;
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
}

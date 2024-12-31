<?php
namespace App\Listeners;

use App\Events\InvoiceDueSoon;
use App\Models\Action;
use App\Models\Customers;
use App\Models\Opportunity;
use App\Models\Tasks;
use App\Models\Workflow;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class SendInvoiceDueNotification implements ShouldQueue
{
    public function handle(InvoiceDueSoon $event)
    {
        $contact = $event->contact;
        $invoice = $event->invoice;
        Log::info("Handling InvoiceDueSoon event for Invoice ID {$invoice->id} and Contact ID {$contact->id}");

        $workflows = Workflow::whereHas('trigger', function ($query) {
            $query->where('trigger_name', 'invoice_due_date');
        })->get();

        foreach ($workflows as $workflow) {
            // Fetch actions related to the workflow from the Action model
            $actions = Action::whereIn('id', json_decode($workflow->actions_id, true))->get();

            foreach ($actions as $action) {
                if ($action->type == 'create_opportunity') {
                    $this->createOpportunity($action->action_data, $invoice);
                }
                elseif ($action->type == 'add_task') {
                    $this->handleAddTaskAction($action->action_data);
                }
                elseif ($action->type == 'number_formatter') {
                    Log::info("Running number_formatter action for invoice ID: {$invoice->id}");
                    $this->handleNumberFormatterAction($action->action_data, $invoice);
                }
                elseif ($action->type == 'drip') {
                    $this->handleDripAction($action->action_data, $invoice, $actions->toArray());
                }
                elseif ($action->type == 'split') {
                    $this->handleSplitAction($action->action_data, $workflow->id);
                }
                elseif ($action->type == 'send_sms') {
                    Log::info("Sending SMS for Invoice ID {$invoice->id} to Contact ID {$contact->id}");
                    $this->sendSms($action->action_data, $contact, $invoice);
                } elseif ($action->type == 'send_email') {
                    Log::info("Sending Email for Invoice ID {$invoice->id} to Contact ID {$contact->id}");
                    $this->sendEmail($action->action_data, $contact, $invoice);
                }elseif ($action->type == 'send_whatsapp') {
                    $this->sendWhatsApp($action->action_data, $contact, $invoice);
                }
                elseif ($action->type == 'update_contact') {
                    $this->updateContact($action->action_data, $invoice);
                }
                elseif ($action->type == 'delete_contact') {
                    $this->deleteContact($action->action_data, $invoice);
                }
                elseif ($action->type == 'date_formatter') {
                    $this->handleDateFormatterAction($action->action_data, $invoice);
                }
                elseif ($action->type == 'math_operation') {
                    Log::info("Running math_operation action for invoice ID: {$invoice->id}");
                            
                                if (isset($actionData['field'], $actionData['operator'], $actionData['update_field'])) {
                                    $this->performMathOperation($actionData, $invoice);
                                } else {
                                    Log::warning("Invalid or incomplete action data for math_operation on invoice ID: {$invoice->id}");
                                }
                }
                elseif ($action->type == 'wait') {
                    $this->handleWaitAction($action->action_data);
                }
                else {
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


    private function performMathOperation(array $actionData, $invoice)
{
    // Ensure necessary fields are present in action data
    if (isset($actionData['field'], $actionData['operator'], $actionData['update_field'])) {
        $field = $actionData['field'];  // Field to perform operation on (e.g., invoicedate)
        $operator = $actionData['operator'];  // Add or Subtract
        $days = $actionData['Days'] ?? 0;  // Days to add/subtract
        $months = $actionData['months'] ?? 0;  // Months to add/subtract
        $years = $actionData['years'] ?? 0;  // Years to add/subtract
        $updateField = $actionData['update_field'];  // Field to store result (e.g., duedate)

        // Ensure the field exists and is a valid date
        if (isset($invoice->{$field}) && $this->isValidDate($invoice->{$field})) {
            $date = Carbon::parse($invoice->{$field});  // Parse the date from the field

            // Perform the math operation
            switch ($operator) {
                case 'add':
                    $date = $date->addDays($days)->addMonths($months)->addYears($years);
                    break;

                case 'sub':
                    $date = $date->subDays($days)->subMonths($months)->subYears($years);
                    break;

                default:
                    Log::warning("Invalid operator in math operation for invoice ID: {$invoice->id}");
                    return;
            }

            // Log the result
            Log::info("Performed math operation for invoice ID: {$invoice->id}. " .
                "Field: {$field}, Operator: {$operator}, Days: {$days}, Months: {$months}, Years: {$years}, " .
                "Updated Value: {$date->toDateString()}");

            // Save the result in the specified update field
            $invoice->{$updateField} = $date->toDateString();  // Save only the date (no time)
            $invoice->save();
        } else {
            Log::warning("Invalid or missing date field for math operation on invoice ID: {$invoice->id}");
        }
    } else {
        Log::warning("Missing necessary action data for math_operation on invoice ID: {$invoice->id}");
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

private function handleNumberFormatterAction(array $actionData, $invoice)
{
    $field = $actionData['field'] ?? null;
    $type = $actionData['type'] ?? null;

    // Skip the field check for random number type, since it doesn't require a field
    if ($type !== 'random_number') {
        $field = $actionData['field'] ?? null;

    if (!$field || !isset($invoice->$field)) {
        Log::warning("Field {$field} not found in invoice data.");
        return;
    }

    $value = $invoice->$field;
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
            $invoice->$field = $plainNumericValue;
            $invoice->save();

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
                    $invoice->$field = $formattedResult['numeric']; // Store plain numeric value in DB
                    $invoice->save();
            
                    Log::info("Formatted currency for invoice ID: {$invoice->id}. Display value: {$formattedResult['formatted']}");
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


    private function handleDateFormatterAction($actionData, &$invoice)
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
    $getDateValue = function ($dateType, $specificDate = null) use ($invoice) {
        switch ($dateType) {
            case 'specific_date':
                return $specificDate ? Carbon::createFromFormat('Y-m-d', $specificDate) : null;
            case 'current_date':
                return Carbon::now();
            default:
                return isset($invoice[$dateType]) ? Carbon::parse($invoice[$dateType]) : null;
        }
    };

    try {
        switch ($type) {
            case 'format_dates':
                // Determine the date source: field or explicitly specified date
                if (!empty($startDateType)) {
                    $date = $getDateValue($startDateType, $specificStartDate);
                } elseif (!empty($field) && isset($invoice[$field])) {
                    $date = Carbon::parse($invoice[$field]); // Use the field's value
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
                        $originalValue = $invoice[$field] ?? null;
                        $invoice[$field] = $dateForDatabase; // Save in database format
                        $invoice->save();
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


    public function updateContact(array $actionData, $invoice)
    {
    try {
        // Fetch the contact using the contact_id from the opportunity
        $contact = \App\Models\Customers::find($invoice->contacts);

        if (!$contact) {
            Log::warning("Contact not found for contact ID {$invoice->contacts}.");
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
        Log::error("Error updating contact for payment ID {$invoice->id}: " . $e->getMessage(), [
            'action_data' => $actionData,
        ]);
    }
}

public function deleteContact($invoice)
{
    try {
        // Fetch the contact using the contact_id from the opportunity
        $contact = \App\Models\Customers::find($invoice->contacts);

        if (!$contact) {
            Log::warning("Contact not found for contact ID {$invoice->contacts}. Deletion aborted.");
            return;
        }
        $contact->delete();
        Log::info("Contact ID {$contact->id} deleted successfully.");
    } catch (Exception $e) {
        Log::error("Error deleting contact for opportunity ID {$invoice->id}: " . $e->getMessage());
    }
}

    private function createOpportunity(array $actionData, $invoice)
    {
        try {
            Opportunity::create([
                'select_pipeline' => $actionData['select_pipeline'],
                'select_stage' => $actionData['select_stage'],
                'opportunity_name' => $actionData['opportunity_name'],
                'opportunity_source' => $actionData['opportunity_source'],
                'lead_value' => $actionData['lead_value'],
                'opportunity_status' => $actionData['opportunity_status'],
                'contact_id' => $invoice->contacts,
            ]);

            Log::info("Opportunity created successfully for contact ID: {$invoice->contacts}");
        } catch (\Exception $e) {
            Log::error("Failed to create opportunity: " . $e->getMessage());
        }
    }

    private function sendSms(array $data, $contact)
    {
        $phoneNumber = $contact->primary_phone;
        $message = str_replace('{{contact.name}}', $contact->name, $data['message']);
        Log::info("Preparing to send SMS to {$phoneNumber} with message: {$message}");
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

protected function sendEmail(array $data, $contact, $invoice)
{
    $mailId = uniqid();
    DB::table('email_trackings')->insert([
        'crmid' => $invoice->contacts,
        'mailid' => $mailId,
        'access_count' => 0,
        'click_count' => 0,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    $ngrokUrl = "https://47fe-49-37-201-165.ngrok-free.app";
    $trackingPixelUrl = "$ngrokUrl/track_open?mailid=$mailId";
    $trackableLinkUrl = "$ngrokUrl/track_click?mailid=$mailId&redirect=" . urlencode("https://dummyurl.com/test");
    $messageContent = str_replace(
            ['{{contact.name}}', '{{invoice.invoicenumber}}', '{{invoice.duedate}}'],
            [$contact->name, $invoice->invoicenumber, $invoice->duedate],
            $data['message']
        );

    try {
            Mail::send('emails.opportunity', compact('messageContent', 'trackingPixelUrl', 'trackableLinkUrl'), function ($mail) use ($data, $contact) {
                $mail->to($contact->primary_email)
                     ->from($data['from_email'], $data['from_name'])
                     ->subject($data['subject']);
                foreach ($data['attachments'] as $attachment) {
                    if ($attachment['type'] == 'file' && isset($attachment['url'])) {
                        $mail->attach($attachment['url']);
                    }
                }
            });

            Log::info("Email sent successfully to {$contact->primary_email}");
    } catch (\Exception $e) {
            Log::error("Failed to send email to {$contact->primary_email}: " . $e->getMessage());
        }
    }

//     private function sendEmail(array $data, $opportunity)
// {
//     $customer = Customers::find($opportunity->contact_id);

//     if (!$customer || !$customer->primary_email) {
//         Log::error("No primary email found for contact_id: {$opportunity->contact_id}");
//         return;
//     }

//     // Generate a unique mail ID
//     $mailId = uniqid();

//     // Insert the tracking record
//     DB::table('email_trackings')->insert([
//         'crmid' => $opportunity->contact_id,
//         'mailid' => $mailId,
//         'access_count' => 0,
//         'click_count' => 0,
//         'created_at' => now(),
//         'updated_at' => now()
//     ]);

//     // Use your Ngrok URL for tracking
//     $ngrokUrl = "https://47fe-49-37-201-165.ngrok-free.app";
//     $trackingPixelUrl = "$ngrokUrl/track_open?mailid=$mailId";
//     $trackableLinkUrl = "$ngrokUrl/track_click?mailid=$mailId&redirect=" . urlencode("https://dummyurl.com/test");

//     // Prepare the message content
//     $messageContent = str_replace('{{opportunity.name}}', $opportunity->opportunity_name, $data['message']);

//     try {
//         Mail::send('emails.opportunity', compact('messageContent', 'trackingPixelUrl', 'trackableLinkUrl'), function ($mail) use ($data, $customer) {
//             $mail->from($data['from_email'], $data['from_name'])
//                 ->to($customer->primary_email)
//                 ->subject($data['subject']);

//             if (isset($data['attachments']) && is_array($data['attachments'])) {
//                 foreach ($data['attachments'] as $attachment) {
//                     if ($attachment['type'] == 'file' && isset($attachment['url'])) {
//                         $mail->attach($attachment['url']);
//                     }
//                 }
//             }
//         });

//         Log::info("Email sent to {$customer->primary_email}");
//     } catch (\Exception $e) {
//         Log::error("Failed to send email: " . $e->getMessage());
//     }
// }

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
}

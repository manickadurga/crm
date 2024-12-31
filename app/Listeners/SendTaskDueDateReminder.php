<?php
namespace App\Listeners;

use App\Events\TaskCreated;
use App\Models\Manage_Employees;
use App\Models\Trigger;
use App\Models\Action; 
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendTaskDueDateReminder implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TaskCreated $event)
    {
        Log::info("Triggered SendTaskDueDateReminder event.");
        $task = $event->task;
        Log::info("Task ID: {$task->id}, Task Title: {$task->title}");

        // Retrieve the filter settings from the triggers table
        $trigger = Trigger::where('trigger_name', 'task_due_date')->first();
        
        if (!$trigger) {
            Log::warning("No trigger found for 'task_due_date'.");
            return;
        }

        Log::info("Trigger found: {$trigger->trigger_name}");

        if (isset($trigger->filters[0])) {
            $filter = $trigger->filters[0];

            // Get the filter value (number of days before/after)
            $value = (int)$filter['value'];
            $field = $filter['field'];  // "before_no_of_days" or "after_no_of_days"
            
            // Calculate the notification date based on the filter
            $dueDate = Carbon::parse($task->duedate);
            Log::info("Due Date: {$dueDate}");

            if ($field === 'before_no_of_days') {
                $notificationDate = $dueDate->subDays($value);
                Log::info("Notification Date (Before): {$notificationDate}");
            } elseif ($field === 'after_no_of_days') {
                $notificationDate = $dueDate->addDays($value);
                Log::info("Notification Date (After): {$notificationDate}");
            }

            $today = Carbon::now()->startOfDay();
            Log::info("Today's Date: {$today}, Notification Date: {$notificationDate}");

            // Send the email if the notification date is today or in the past
            if ($notificationDate->lte($today)) {
                Log::info("Notification date is today or has already passed. Sending email immediately.");
                $this->sendEmail($task);
            } else {
                // Otherwise, schedule the email for the notification date
                Log::info("Notification date is in the future. Scheduling email.");
                $delay = $today->diffInSeconds($notificationDate);
                $this->release($delay);
                Log::info("Listener released for {$delay} seconds until the notification date.");
            }
        } else {
            Log::warning("No filters found for the trigger.");
        }    
        // Perform Math Operation
        $this->performMathOperation($task);
    }

    protected function performMathOperation($task)
    {
        $action = Action::where('action_name', 'Math Operation')->first();

        if (!$action || !isset($action->action_data)) {
            Log::warning("No action data found for 'Math Operation'.");
            return;
        }

        $actionData = $action->action_data;

        // Ensure the necessary fields are present
        if (isset($actionData['field'], $actionData['operator'], $actionData['update_field'])) {
            $field = $actionData['field'];
            $operator = $actionData['operator'];
            $updateField = $actionData['update_field'];
            $days = $actionData['Days'] ?? 0;
            $months = $actionData['months'] ?? 0;
            $years = $actionData['years'] ?? 0;

            // Check if the field exists and is a valid date
            if (isset($task->{$field}) && $this->isValidDate($task->{$field})) {
                $date = Carbon::parse($task->{$field});

                // Perform the math operation
                switch ($operator) {
                    case 'add':
                        $date = $date->addDays($days)->addMonths($months)->addYears($years);
                        break;

                    case 'sub':
                        $date = $date->subDays($days)->subMonths($months)->subYears($years);
                        break;

                    default:
                        Log::warning("Invalid operator for Math Operation in Task ID: {$task->id}");
                        return;
                }

                // Save the result in the specified update field
                $task->{$updateField} = $date->toDateString();
                $task->save();
                Log::info("Math Operation performed on Task ID: {$task->id}. Field: {$field}, Operator: {$operator}, Updated Field: {$updateField}, New Value: {$date->toDateString()}");
            } else {
                Log::warning("Invalid or missing date field for Math Operation on Task ID: {$task->id}");
            }
        } else {
            Log::warning("Incomplete action data for Math Operation on Task ID: {$task->id}");
        }
    }

    /**
     * Check if a value is a valid date.
     *
     * @param string $date
     * @return bool
     */
    private function isValidDate($date)
    {
        try {
            Carbon::parse($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Sends an email to the employees or teams associated with the task.
     * @param $task
     */
    protected function sendEmail($task)
    {
        $employeeEmails = Manage_Employees::whereIn('id', $task->addorremoveemployee)->pluck('email')->toArray();

        if (empty($employeeEmails)) {
            Log::warning("No employees found to send email for Task ID: {$task->id}");
            return;
        }

        Log::info("Sending email to employees: " . implode(', ', $employeeEmails));

        $action = Action::where('action_name', 'Task Due Notification email')->first();
        $messageBody = "Default reminder message";
        
        if ($action && isset($action->action_data['message'])) {
            $messageBody = $action->action_data['message'];
            Log::info("Message body fetched from action data: {$messageBody}");
        } else {
            Log::warning("No action data found for 'Task Due Notification email'. Using default message.");
        }
        
        foreach ($employeeEmails as $email) {
            Mail::raw($messageBody, function ($message) use ($email) {
                $message->to($email)->subject("Task Due Reminder");
            });
            Log::info("Email sent to: {$email}");
        }
        // After sending the email, update the task status to "Reminder Sent"
    try {
        $task->update([
            'status' => 'Reminder Sent',
        ]);
        Log::info("Task ID {$task->id} status updated to 'Reminder Sent'.");
    } catch (\Exception $e) {
        Log::error("Failed to update status for Task ID {$task->id}: " . $e->getMessage());
    }
    }
}

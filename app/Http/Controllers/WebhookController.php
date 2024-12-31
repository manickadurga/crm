<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleBrevoWebhook(Request $request)
    {
        $event = $request->input('event');
        $email = $request->input('email');

        // Log the event
        Log::info("Brevo event: {$event} for email: {$email}");

        // Additional handling based on event type
        switch ($event) {
            case 'open':
                $this->handleOpenEvent($email);
                break;
            case 'click':
                $this->handleClickEvent($email);
                break;
            case 'marked_as_spam':
                $this->handleSpamEvent($email);
                break;
            case 'unsubscribe':
                $this->handleUnsubscribeEvent($email);
                break;
            // Add more cases as needed
        }

        return response()->json(['status' => 'success']);
    }

    private function handleOpenEvent($email)
    {
        // Handle open event
        Log::info("Email opened: {$email}");
    }

    private function handleClickEvent($email)
    {
        // Handle click event
        Log::info("Email clicked: {$email}");
    }

    private function handleSpamEvent($email)
    {
        // Handle spam event
        Log::info("Email marked as spam: {$email}");
    }

    private function handleUnsubscribeEvent($email)
    {
        // Handle unsubscribe event
        Log::info("Email unsubscribed: {$email}");
    }
}

// namespace App\Http\Controllers;

// use App\Models\Workflow;
// use App\Models\Estimate;
// use App\Models\Action;
// use App\Models\Goal;
// use App\Models\Customers;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;

// class WebhookController extends Controller
// {
//     // Handle Mailgun's webhook events
//     public function handleEmailEvent(Request $request)
//     {
//         // Log the raw webhook data for debugging
//         Log::info('Received email event:', $request->all());

//         // Extract event and email information from Mailgun's payload
//         $event = $request->input('event');
//         $recipientEmail = $request->input('recipient');
//         $timestamp = $request->input('timestamp');
//         $message = $request->input('message');

//         // Log the event details
//         Log::info("Mailgun event: {$event} for email: {$recipientEmail}");

//         // Find the customer associated with the email address
//         $customer = Customers::where('email', $recipientEmail)->first();
//         if (!$customer) {
//             Log::warning("No customer found for email: {$recipientEmail}");
//             return response()->json(['status' => 'failure', 'message' => 'Customer not found'], 400);
//         }

//         // Find the workflow associated with this customer (assuming you have a relationship or logic to find workflow)
//         $workflow = Workflow::where('customer_id', $customer->id)->first();
//         if (!$workflow) {
//             Log::warning("No workflow found for customer ID: {$customer->id}");
//             return response()->json(['status' => 'failure', 'message' => 'Workflow not found'], 400);
//         }

//         // Process the event based on type (opened, clicked, unsubscribed, etc.)
//         switch ($event) {
//             case 'opened':
//                 $this->handleEmailOpened($customer, $workflow, $timestamp);
//                 break;
//             case 'clicked':
//                 $this->handleEmailClicked($customer, $workflow, $timestamp);
//                 break;
//             case 'unsubscribed':
//                 $this->handleEmailUnsubscribed($customer, $workflow, $timestamp);
//                 break;
//             case 'complained':
//                 $this->handleEmailComplaint($customer, $workflow, $timestamp);
//                 break;
//             default:
//                 Log::warning("Unknown email event: {$event}");
//                 break;
//         }

//         // Respond with a 200 OK status to acknowledge receipt
//         return response()->json(['status' => 'success']);
//     }

//     // Handle email opened event
//     private function handleEmailOpened($customer, $workflow, $timestamp)
//     {
//         Log::info("Email opened by: {$customer->email} at {$timestamp}");

//         // Example: Check if the goal is met for this event
//         $goal = Goal::where('workflow_id', $workflow->id)
//                     ->where('type', 'email_opened')
//                     ->first();

//         if ($goal) {
//             // Update the workflow status to reflect the goal is met
//             $this->updateWorkflowStatus($workflow, 'Goal Met - Email Opened');
//         }
//     }

//     // Handle email clicked event
//     private function handleEmailClicked($customer, $workflow, $timestamp)
//     {
//         Log::info("Email clicked by: {$customer->email} at {$timestamp}");

//         // Example: Check if the goal is met for this event
//         $goal = Goal::where('workflow_id', $workflow->id)
//                     ->where('type', 'email_clicked')
//                     ->first();

//         if ($goal) {
//             // Update the workflow status to reflect the goal is met
//             $this->updateWorkflowStatus($workflow, 'Goal Met - Email Clicked');
//         }
//     }

//     // Handle email unsubscribed event
//     private function handleEmailUnsubscribed($customer, $workflow, $timestamp)
//     {
//         Log::info("Email unsubscribed by: {$customer->email} at {$timestamp}");

//         // Example: Check if the goal is met for this event
//         $goal = Goal::where('workflow_id', $workflow->id)
//                     ->where('type', 'email_unsubscribed')
//                     ->first();

//         if ($goal) {
//             // Example: Mark the workflow as ended if unsubscribed
//             $this->updateWorkflowStatus($workflow, 'Workflow Ended - Unsubscribed');
//         }
//     }

//     // Handle email complaint event
//     private function handleEmailComplaint($customer, $workflow, $timestamp)
//     {
//         Log::info("Email complaint received from: {$customer->email} at {$timestamp}");

//         // Example: Check if the goal is met for this event
//         $goal = Goal::where('workflow_id', $workflow->id)
//                     ->where('type', 'email_complained')
//                     ->first();

//         if ($goal) {
//             // Example: Mark the workflow as ended if complaint received
//             $this->updateWorkflowStatus($workflow, 'Workflow Ended - Complaint');
//         }
//     }

//     // Update workflow status after goal is met
//     private function updateWorkflowStatus($workflow, $status)
//     {
//         // Update the workflow status (this is just an example, modify it according to your needs)
//         $workflow->status = $status;
//         $workflow->save();

//         Log::info("Workflow ID: {$workflow->id} updated to status: {$status}");

//         // Optionally, you could trigger other actions here depending on your workflow logic
//         $this->triggerActionsForWorkflow($workflow);
//     }

//     // Trigger subsequent actions based on workflow status
//     private function triggerActionsForWorkflow($workflow)
//     {
//         // Example: Get actions that are linked to this workflow
//         $actions = Action::where('workflow_id', $workflow->id)->get();

//         foreach ($actions as $action) {
//             // Perform action (e.g., send an email, create a task, etc.)
//             Log::info("Triggering action: {$action->action_name}");

//             // For email actions, you can add logic to send an email or other actions
//             if ($action->type === 'send_email') {
//                 // Here, you can call your email sending function
//                 $this->sendEmailAction($action);
//             }
//         }
//     }

//     // Example function to send an email action (you can customize it)
//     private function sendEmailAction($action)
//     {
//         // Prepare email details
//         $emailData = $action->action_data;
        
//         // Send the email using your email sending logic (e.g., Mail::send())
//         // You can access $emailData['from_email'], $emailData['subject'], etc.
//         Log::info("Sending email from: {$emailData['from_email']} with subject: {$emailData['subject']}");
//         // Mail::to($emailData['to'])->send(new SomeEmail($emailData));
//     }
// }


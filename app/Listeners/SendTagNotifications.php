<?php

namespace App\Listeners;

use App\Events\TagUpdated;
use App\Models\Action;
use App\Models\Workflow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;

class SendTagNotifications
{
    public function handle(TagUpdated $event)
    {
        $contact = $event->contact;
        $tagId = $event->tag_id;
        $actionType = $event->action; // "tag_added" or "tag_removed"

        // Log the event details
        Log::info("SendTagNotifications triggered for contact ID: {$contact->id}, Tag ID: {$tagId}, Action: {$actionType}");

        // Retrieve workflows that match the "Contact Tag" trigger
        $workflows = Workflow::with('trigger')
            ->whereHas('trigger', function($query) {
                $query->where('trigger_name', 'Contact Tag');
            })
            ->get();

        foreach ($workflows as $workflow) {
            // Loop through the actions associated with this workflow
            foreach ($workflow->actions as $action) {
                $actionData = json_decode($action->action_data, true);

                // Determine action based on filter type and execute the corresponding function
                if ($actionType === 'tag_added' && $action->type === 'send_email') {
                    $this->sendEmail($actionData, $contact);
                } elseif ($actionType === 'tag_removed' && $action->type === 'send_email') {
                    $this->sendEmail($actionData, $contact);
                }

                if ($actionType === 'tag_added' && $action->type === 'send_sms') {
                    $this->sendSms($actionData['message'], $contact);
                } elseif ($actionType === 'tag_removed' && $action->type === 'send_sms') {
                    $this->sendSms($actionData['message'], $contact);
                }

                if ($actionType === 'tag_added' && $action->type === 'send_whatsapp') {
                    $this->sendWhatsApp($actionData['message'], $contact);
                } elseif ($actionType === 'tag_removed' && $action->type === 'send_whatsapp') {
                    $this->sendWhatsApp($actionData['message'], $contact);
                }
            }
        }
    }

    private function sendEmail($actionData, $contact)
    {
        $subject = $actionData['subject'];
        $message = $actionData['message'];

        try {
            Mail::raw($message, function ($mail) use ($subject, $contact) {
                $mail->from('manickadurga@gmail.com', 'Support')
                    ->to($contact->primary_email)
                    ->subject($subject);
            });
            Log::info("Email sent to {$contact->primary_email} with subject: {$subject}");
        } catch (\Exception $e) {
            Log::error("Failed to send email: " . $e->getMessage());
        }
    }

    private function sendSms($message, $contact)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $twilioNumber = config('services.twilio.from');

        $client = new TwilioClient($sid, $token);

        try {
            $client->messages->create(
                $contact->primary_phone,
                [
                    'from' => $twilioNumber,
                    'body' => $message
                ]
            );
            Log::info("SMS sent to {$contact->primary_phone} with message: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send SMS: " . $e->getMessage());
        }
    }

    private function sendWhatsApp($message, $contact)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $whatsappNumber = "whatsapp:" . config('services.twilio.whatsapp_from');

        $client = new TwilioClient($sid, $token);

        try {
            $client->messages->create(
                "whatsapp:" . $contact->primary_phone,
                [
                    'from' => $whatsappNumber,
                    'body' => $message
                ]
            );
            Log::info("WhatsApp message sent to {$contact->primary_phone} with message: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp message: " . $e->getMessage());
        }
    }
}


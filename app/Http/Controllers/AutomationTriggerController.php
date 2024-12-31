<?php

namespace App\Http\Controllers;

use App\Models\AutomationTrigger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class AutomationTriggerController extends Controller
{
    // Create a new automation trigger
    public function createTrigger(Request $request)
    {
        $validated = $request->validate([
            'event' => 'required|string',
            'action' => 'required|string|in:send_email,send_sms',
            'message_details' => 'required|array',
            'recipient_field' => 'required|string',
        ]);

        // Store the trigger in the database
        $trigger = AutomationTrigger::create($validated);

        return response()->json(['message' => 'Trigger created successfully', 'trigger' => $trigger]);
    }

    // Handle the event
    public function handleEvent($event, $contact, $changedFields = [])
    {
        // Fetch all triggers for the given event
        $triggers = AutomationTrigger::where('event', $event)->get();

        foreach ($triggers as $trigger) {
            $messageDetails = $trigger->message_details;
            
            // Replace placeholders in the message body with actual data
            $messageBody = $this->replacePlaceholders($messageDetails['body'], $contact);

            if ($trigger->action === 'send_email') {
                $this->sendEmail($contact, $messageDetails['subject'], $messageBody, $trigger->recipient_field);
            } elseif ($trigger->action === 'send_sms') {
                $this->sendSms($contact, $messageBody, $trigger->recipient_field);
            }
        }
    }

    // Replace placeholders with actual data
    private function replacePlaceholders($messageBody, $contact)
    {
        // Define placeholder replacements
        $placeholders = [
            '{name}' => $contact->name,
            '{primary_phone}' => $contact->primary_phone,
            '{primary_email}' => $contact->primary_email,
            '{address}' => $contact->address,
        ];

        // Replace placeholders in the message body
        return str_replace(array_keys($placeholders), array_values($placeholders), $messageBody);
    }

    // Send email
    private function sendEmail($contact, $subject, $body, $recipientField)
    {
        $recipient = $contact->{$recipientField};

        if ($recipient) {
            // Send an HTML email using the 'html' method
            Mail::html($body, function ($message) use ($recipient, $subject) {
                $message->to($recipient)
                    ->subject($subject);
            });
        }
    }

    // Send SMS
    private function sendSms($contact, $messageBody, $recipientField)
    {
        $recipientPhone = $contact->{$recipientField}; // e.g., primary_phone
        
        if ($recipientPhone) {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $twilioPhoneNumber = env('TWILIO_PHONE_NUMBER');

            if (!$sid || !$token || !$twilioPhoneNumber) {
                Log::error('Twilio credentials are missing or empty.');
                return;
            }

            // Use "\n" for line breaks for SMS readability
            $formattedMessage = str_replace('<br>', "\n", $messageBody);

            Log::info("Preparing to send SMS to {$recipientPhone}");

            try {
                $twilio = new TwilioClient($sid, $token);

                $twilio->messages->create($recipientPhone, [
                    'from' => $twilioPhoneNumber,
                    'body' => $formattedMessage // SMS message content
                ]);

                Log::info("SMS sent to {$recipientPhone} successfully.");
            } catch (\Twilio\Exceptions\RestException $e) {
                Log::error('Twilio REST Exception: ' . $e->getMessage());
            } catch (\Exception $e) {
                Log::error('General Exception: ' . $e->getMessage());
            }
        } else {
            Log::warning('Recipient phone number is missing for contact ID: ' . $contact->id);
        }
    }
}


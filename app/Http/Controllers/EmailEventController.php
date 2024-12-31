<?php

namespace App\Http\Controllers;

use App\Models\EmailEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailEventController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info("Webhook received:", $request->all());

        $messageId = $request->input('event-data.message.headers.message-id');
        if (!$messageId) {
            Log::error("Missing message-id in webhook payload");
            return response('Missing message-id', 400);
        }

        $event = $request->input('event-data.event');
        $details = json_encode($request->all());

        try {
            EmailEvent::create([
                'message_id' => $messageId,
                'event' => $event,
                'details' => $details,
                'timestamp' => now(),
            ]);

            Log::info("Email event stored successfully for message-id: {$messageId}");
            return response('Webhook received', 200);
        } catch (\Exception $e) {
            Log::error("Failed to store email event: " . $e->getMessage());
            return response('Error storing email event', 500);
        }
    }
}


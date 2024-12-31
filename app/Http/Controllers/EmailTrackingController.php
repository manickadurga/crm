<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\EmailEvent; // You might want to create an EmailEvent model

class EmailTrackingController extends Controller
{
    public function trackOpen($email_id)
    {
        // Log the open event (you can store it in the database if needed)
        EmailEvent::create([
            'email_id' => $email_id,
            'event_type' => 'open',
            'event_data' => 'Email opened by recipient',
            'timestamp' => now(),
        ]);

        // Serve the 1x1 transparent image
        return response()->make('', 200, [
            'Content-Type' => 'image/png',
            'Content-Length' => '43',
        ]);
    }
}


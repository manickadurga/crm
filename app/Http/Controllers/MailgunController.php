<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailgunController extends Controller
{
    public function webhook(Request $request)
    {
        Log::info('Mailgun Event Received: ', $request->all());

        // Check if request has valid JSON
        if (!$request->isJson()) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        // Log the parsed JSON
        Log::info('Mailgun Event Data: ', $request->json()->all());

        return response()->json(['message' => 'Received'], 200);
    }
}

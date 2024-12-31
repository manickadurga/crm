<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailgunWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log the raw incoming data
        Log::info('Mailgun Webhook Received', $request->all());

        // Handle different event types
        $event = $request->input('event');

        switch ($event) {
            case 'complained':
                Log::info('Complaint received for email: ', $request->input('data'));
                break;
            case 'opened':
                Log::info('Mail has been opened: ', $request->input('data'));
                break;
            case 'clicked':
                Log::info('A link in the email was clicked: ', $request->input('data'));
                break;
            default:
                Log::info('Unhandled event type: ' . $event);
        }

        return response()->json(['status' => 'success'], 200);
    }
}




// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;

// class MailgunWebhookController extends Controller
// {
//     public function handle(Request $request)
//     {
//          // Log the raw incoming data
//          Log::info('Mailgun Webhook Received', $request->all());

//          // Handle different event types, for example:
//          $event = $request->input('event');
 
//          if ($event == 'complained') {
//              Log::info('Complaint received for email: ', $request->input('data'));
//          } elseif ($event == 'opened') {
//              Log::info('Mail has been opened: ', $request->input('data'));
//          } elseif ($event == 'clicked') {
//              Log::info('A link in the email was clicked: ', $request->input('data'));
//          }
 
//          return response()->json(['status' => 'success']);
//      }
//     }

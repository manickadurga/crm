<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function trackOpen(Request $request)
    {
        $mailId = $request->query('mailid');
        //Log the request for debugging purposes 
        Log::info("Tracking open for mail ID: {$mailId}");

        // Increment access_count in your database for this mail ID
        DB::table('email_trackings')->where('mailid', $mailId)->increment('access_count');

        // Return a 1x1 transparent pixel
        return response(base64_decode("R0lGODlhAQABAAAAACw="))->header('Content-Type', 'image/gif');
    }

    public function trackClick(Request $request)
    {
        $mailId = $request->query('mailid');
        $redirectUrl = $request->query('redirect');

        // Increment click_count in your database for this mail ID
        DB::table('email_trackings')->where('mailid', $mailId)->increment('click_count');

        // Redirect to the original URL
        return redirect($redirectUrl);
    }
}



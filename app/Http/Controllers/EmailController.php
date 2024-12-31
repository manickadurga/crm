<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTracking;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    /**
     * Track email open event.
     *
     * @param string $mailId
     * @return \Illuminate\Http\Response
     */
    public function trackOpen($mailId)
{
    // Log the incoming request to ensure the route is being hit
    Log::info("Tracking open event for mailId: {$mailId}");

    // Find the email tracking record based on the mailId
    $emailTracking = EmailTracking::where('mailid', $mailId)->first();

    // if ($emailTracking) {
    //     // Log the current access_count before incrementing
    //     Log::info("Current access_count for mailId {$mailId}: {$emailTracking->access_count}");

    //     // Increment the access_count for the given mail ID
    //     $emailTracking->increment('access_count'); // Increment the open count

    //     // Log the updated access_count after increment
    //     Log::info("Updated access_count for mailId {$mailId}: {$emailTracking->access_count}");
    // } else {
    //     // Log if no record is found for the mailId
    //     Log::error("No email tracking record found for mailId: {$mailId}");
    // }

    if ($emailTracking) {
        // Increment the open count
        $emailTracking->increment('access_count');
    }

    // Return a 1x1 transparent PNG pixel
    $imagePath = public_path('images/1719394113.jpg');  // Ensure you have a pixel.png image in public/images
    return response()->file($imagePath);
}
    


// Handle link click tracking
public function trackClick($mailId)
{
    // Log tracking for debugging
    Log::info("Tracking click event for mailId: {$mailId}");

    // Find the email tracking record
    $emailTracking = EmailTracking::where('mailid', $mailId)->first();

    if ($emailTracking) {
        $emailTracking->increment('click_count'); // Increment click count
        Log::info("Click count incremented for mailId: {$mailId}");
    } else {
        Log::error("No email tracking record found for mailId: {$mailId}");
    }

    // Redirect to the original destination URL
    $redirectUrl = request()->query('redirect', url('/')); // Default to home if no redirect parameter
    return redirect($redirectUrl);
}

}


<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Customers;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendImmediateMailJob;
use App\Jobs\SendScheduledMailJob;
use App\Jobs\SendBatchMailJob;
use App\Jobs\BatchScheduleEmailJob;
use App\Mail\CampaignMailable;
use App\Models\CampaignDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CampaignController extends Controller
{
    /**
     * Store a new campaign.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

public function store(Request $request)
{
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'template_id' => 'required|exists:jo_templates,id', // Adjust to your actual table name
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create a new campaign
        $campaign = Campaign::create([
            'name' => $request->name,
            'template_id' => $request->template_id,
            // Add other fields as needed
        ]);

        // Retrieve the selected template
        $template = Template::find($request->template_id);

        return response()->json([
            'message' => 'Campaign created successfully.',
            'campaign_id' => $campaign->id,
            'template' => $template, // Return the template details
        ], 201);
}

public function send(Request $request)
{
        // Validate the incoming request
        $validatedData = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'send_method' => 'required|in:send_now,schedule,batch_schedule',
            'sender_email' => 'required|email',
            'sender_name' => 'required|string|max:255',
            'subject_line' => 'required|string|max:255',
            'preview_text' => 'required|string',
            'recipient_to' => 'nullable|array',
            'recipient_to.*.type' => 'nullable|string|in:choose_contact,send_to_smartlist,choose_contacts_from_tags',
            'recipient_to.*.ids' => 'nullable|array',
            'recipient_to.*.condition' => 'nullable|array',
            'recipient_to.*.tags' => 'nullable|array',
            'schedule_at' => 'sometimes|required|date',
            'start_on' => 'sometimes|required|date',
            'no_of_recipients' => 'sometimes|required|integer',
            'batch_quantity' => 'required_if:send_method,batch_schedule|integer|min:1',
            'repeat_after' => 'required_if:send_method,batch_schedule|string',
            'send_on' => 'required_if:send_method,batch_schedule|string|in:sun,mon,tue,wed,thu,fri,sat',
            'start_time' => 'required_if:send_method,batch_schedule|string',
            'end_time' => 'required_if:send_method,batch_schedule|string',
        ]);

        // Retrieve the campaign and associated template_id
        $campaign = Campaign::findOrFail($validatedData['campaign_id']);
        $template_id = $campaign->template_id;

        // Add specific validation based on campaign_type
        switch ($validatedData['send_method']) {
            case 'send_now':
                // No additional fields to validate for send_now
                break;

            case 'schedule':
                $request->validate([
                    'schedule_at' => 'required|date', // Schedule date and time
                ]);
                break;

            case 'batch_schedule':
                $request->validate([
                    'start_on' => 'required|date',
                    'no_of_recipients' => 'required|integer',
                    'batch_quantity' => 'required|integer',
                    'repeat_after' => 'required|string', // Define format if necessary
                    'send_on' => 'required|string|in:sun,mon,tue,wed,thu,fri,sat',
                    'start_time' => 'required|string',
                    'end_time' => 'required|string',
                ]);
                break;

        }

        $campaignDetail = new CampaignDetail();
        $campaignDetail->campaign_id = $campaign->id;
        $campaignDetail->send_method = $validatedData['send_method'];
        $campaignDetail->sender_email = $validatedData['sender_email'];
        $campaignDetail->sender_name = $validatedData['sender_name'];
        $campaignDetail->subject_line = $validatedData['subject_line'];
        $campaignDetail->preview_text = $validatedData['preview_text'];
        $campaignDetail->recipient_to = json_encode($validatedData['recipient_to']); // Save the recipient details as JSON

        switch ($validatedData['send_method']) {
            case 'send_now':
                // No additional fields for send_now
                break;
    
            case 'schedule':
                $campaignDetail->schedule_at = $validatedData['schedule_at'];
                break;
    
            case 'batch_schedule':
                $campaignDetail->start_on = $validatedData['start_on'];
                $campaignDetail->no_of_recipients = $validatedData['no_of_recipients'];
                $campaignDetail->batch_quantity = $validatedData['batch_quantity'];
                $campaignDetail->repeat_after = $validatedData['repeat_after'];
                $campaignDetail->send_on = $validatedData['send_on'];
                $campaignDetail->start_time = $validatedData['start_time'];
                $campaignDetail->end_time = $validatedData['end_time'];
                break;
        }
        $campaignDetail->save();

        // Create a new campaign
        // $campaign = Campaign::create([
        //     'template_id' => $validatedData['template_id'],
        //     'name' => $validatedData['campaign_name'],
        // ]);

        // Get recipient emails based on the provided recipient_to structure
        $recipientEmails = $this->getRecipients($validatedData['recipient_to']);
        Log::info('Recipient Emails:', $recipientEmails);
        $template = Template::find($template_id);
        $templateContent = $template->body; // Fetch the body content


        switch ($validatedData['send_method']) {
            case 'send_now':
                foreach ($recipientEmails as $email) {
                    $body = is_array($template->body) ? $template->body : json_decode($template->body, true);
                    Mail::send('emails.template', [
                        'templateName' => $template->template_name,
                        'body' => $body,
                    ], function ($message) use ($email, $campaignDetail) {
                        $message->to($email)
                            ->subject($campaignDetail->subject_line)
                            ->from($campaignDetail->sender_email, $campaignDetail->sender_name);
                    });
                }
            break;
            
    
            case 'schedule':
                    $scheduleAt = Carbon::parse($validatedData['schedule_at']);
                    foreach ($recipientEmails as $email) {
                        $body = is_array($template->body) ? $template->body : json_decode($template->body, true);
                        SendScheduledMailJob::dispatch(
                            $template->template_name,
                            $body,
                            $campaignDetail->sender_name,
                            $campaignDetail->subject_line,
                            $campaignDetail->preview_text,
                            $email
                        )->delay($scheduleAt);
                    }
                    break;  
                           
            case 'batch_schedule':
                $startOn = Carbon::parse($validatedData['start_on']);
                $batchQuantity = $validatedData['batch_quantity'];
                $repeatAfter = $validatedData['repeat_after']; // Assuming this is in a format like '5 minutes', '10 seconds', etc.
                $recipientEmails = array_unique($recipientEmails);
                $totalRecipients = count($recipientEmails);
            
                // Parse repeat_after to get the interval in seconds
                $repeatAfterInSeconds = $this->parseRepeatAfter($repeatAfter);
            
                // Validate that we have recipients and batch quantity
                if ($totalRecipients > 0 && $batchQuantity > 0) {
                    for ($i = 0; $i < $totalRecipients; $i += $batchQuantity) {
                        // Get the batch of recipients
                        $batchRecipients = array_slice($recipientEmails, $i, $batchQuantity);
            
                        // Retrieve the template
                        // $template = Template::find($validatedData['template_id']);
                        $body = is_array($template->body) ? $template->body : json_decode($template->body, true);
            
                        // Dispatch a job for the batch
                        BatchScheduleEmailJob::dispatch(
                            $template->template_name,
                            $body,
                            $campaignDetail->sender_name,
                            $campaignDetail->subject_line,
                            $campaignDetail->preview_text,
                            $batchRecipients // Pass the batch of recipients
                        )->delay($startOn->copy()->addSeconds($i / $batchQuantity * $repeatAfterInSeconds)); // Adjust delay based on batch
                    }
                } else {
                    Log::error("No recipients to process for batch scheduling or invalid batch quantity.");
                }
                break;
            
            
        }
    

        return response()->json(['message' => 'Campaign created successfully!', 'recipients' => $recipientEmails], 201);
}

private function parseRepeatAfter($repeatAfter)
{
    preg_match('/(\d+)\s*(seconds?|minutes?|hours?)/i', $repeatAfter, $matches);

    if (count($matches) === 3) {
        $value = (int)$matches[1];
        $unit = strtolower($matches[2]);

        switch ($unit) {
            case 'second':
            case 'seconds':
                return $value; // Return seconds
            case 'minute':
            case 'minutes':
                return $value * 60; // Convert minutes to seconds
            case 'hour':
            case 'hours':
                return $value * 3600; // Convert hours to seconds
            default:
                throw new \InvalidArgumentException('Invalid time unit for repeat_after');
        }
    }

    throw new \InvalidArgumentException('Invalid format for repeat_after');
}



    /**
     * Retrieve recipients based on the recipient_to structure.
     *
     * @param  array  $recipients
     * @return array
     */
protected function getRecipients(array $recipients)
{
        $recipientEmails = [];

        foreach ($recipients as $recipient) {
            switch ($recipient['type']) {
                case 'choose_contact':
                    $ids = $recipient['ids'] ?? [];
                    // Fetch emails for the specified IDs from the jo_customers table
                    $emails = Customers::whereIn('id', $ids)->pluck('primary_email')->toArray();
                    $recipientEmails = array_merge($recipientEmails, $emails);
                    break;

                case 'send_to_smartlist':
                        $conditions = $recipient['condition'] ?? []; // Change to handle array of conditions
                    
                        // Loop through each condition in the array
                        foreach ($conditions as $condition) {
                            // Check if 'field' and 'operator' keys exist in the condition
                            if (isset($condition['field'], $condition['operator'])) {
                                $field = $condition['field'];
                                $operator = strtolower($condition['operator']);
                                $value = $condition['value'] ?? '';
                    
                                // Log the condition being checked
                                Log::info('Checking condition:', [
                                    'field' => $field,
                                    'operator' => $operator,
                                    'value' => $value,
                                ]);
                    
                                // Initialize an empty array for emails
                                $emails = [];
                    
                                // Handle different operators with special cases for "images" and "tags" fields
                                if ($operator === 'is empty') {
                                    if ($field === 'tags') {
                                        // Special handling for JSON field "tags"
                                        $emails = Customers::whereJsonLength('tags', 0)
                                                           ->whereNotNull('primary_email')
                                                           ->pluck('primary_email')
                                                           ->toArray();
                                    } else {
                                        $emails = Customers::where(function($query) use ($field) {
                                            $query->whereNull($field)
                                                  ->orWhere($field, '');
                                        })->whereNotNull('primary_email')
                                          ->pluck('primary_email')
                                          ->toArray();
                                    }
                                } elseif ($operator === 'is not empty') {
                                    if ($field === 'tags') {
                                        // Special handling for JSON field "tags"
                                        $emails = Customers::whereJsonLength('tags', '>', 0)
                                                           ->whereNotNull('primary_email')
                                                           ->pluck('primary_email')
                                                           ->toArray();
                                    } else {
                                        $emails = Customers::whereNotNull($field)
                                                           ->where($field, '!=', '')
                                                           ->whereNotNull('primary_email')
                                                           ->pluck('primary_email')
                                                           ->toArray();
                                    }
                                } else {
                                    // Handle other operators (e.g., =, !=)
                                    $emails = Customers::where($field, $operator, $value)
                                                       ->whereNotNull('primary_email')
                                                       ->pluck('primary_email')
                                                       ->toArray();
                                }
                    
                                // Log the emails found
                                Log::info('Emails found:', [
                                    'emails' => $emails,
                                ]);
                    
                                // Merge the retrieved emails with the main recipientEmails array
                                $recipientEmails = array_merge($recipientEmails, $emails);
                            } else {
                                // Handle missing field or operator error
                                Log::error('Invalid condition: "field" or "operator" is missing.', [
                                    'condition' => $condition,
                                ]);
                            }
                        }
                        break;
                    
                    
                        case 'choose_contacts_from_tags':
                        $tags = $recipient['tags'] ?? [];
                        if (!empty($tags)) {
                            // Use whereJsonContains for JSON column
                            $emails = Customers::whereJsonContains('tags', $tags)->pluck('primary_email')->toArray();
                            $recipientEmails = array_merge($recipientEmails, $emails);
                        }
                        break;
            }
        }

        // Remove duplicates from the recipient list
        return array_unique($recipientEmails);
}

    /**
     * List all campaigns.
     *
     * @return \Illuminate\Http\JsonResponse
     */
public function index()
{
        $campaigns = Campaign::with('template')->get();
        return response()->json($campaigns, 200);
}

public function update(Request $request, $id)
{
    // Find the campaign by ID
    $campaign = Campaign::findOrFail($id);

    // Validate the request data
    $validatedData = $request->validate([
        'template_id' => 'required|integer|exists:jo_templates,id', // Validate template ID
        'template' => 'required|array', // Validate template structure
        'template.*.type' => 'required|string',
        'template.*.attributes' => 'required|array',
        // Add more validation rules for other required fields as necessary
    ]);

    // Update the campaign with the new template_id
    $campaign->template_id = $validatedData['template_id'];
    $campaign->save();

    // Update the template
    $template = Template::findOrFail($validatedData['template_id']);
    
    // Optionally update the template name if it's provided
    if (isset($request->template_name)) {
        $template->template_name = $request->template_name;
    }

    // Store the body as a JSON object without encoding it as a string
    $template->body = $validatedData['template'];
    
    // Save the updated template
    $template->save();

    return response()->json([
        'message' => 'Campaign updated successfully.',
        'campaign' => $campaign,
        'template' => $template
    ]);
}
}

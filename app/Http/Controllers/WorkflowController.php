<?php
// app/Http/Controllers/WorkflowController.php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\DripService;

class WorkflowController extends Controller
{
    public function index()
    {
        $workflows = Workflow::all();
        return response()->json($workflows);
    }
    
    // Display the specified action
    public function show($id)
    {
        $workflow = Workflow::find($id);

        if (!$workflow) {
            return response()->json(['message' => 'Action not found'], 404);
        }

        return response()->json($workflow);
    }

    public function store(Request $request)
    {
        // Log the incoming request data for debugging
        Log::info('Request Data: ', $request->all());
    
        try {
            // Validate the request
            $request->validate([
                'workflow_name' => 'required|string|max:255',
                'trigger_id' => 'nullable|exists:triggers,id',
                'actions_id' => 'required|array',
                'actions_id.*' => 'exists:actions,id'
            ]);
    
            // Create a new workflow
            $workflow = Workflow::create([
                'workflow_name' => $request->workflow_name,
                'trigger_id' => $request->trigger_id,
                'actions_id' => json_encode($request->actions_id) // Convert array to JSON
            ]);
    
            // Load the related trigger and actions
            $workflow->load('trigger');
    
            // Manually load actions from the actions_id array
            $actions = Action::whereIn('id', $request->actions_id)->get();
            $workflow->actions = $actions;
    
            // Return the workflow with trigger and actions
            return response()->json([
                'workflow' => $workflow,
               // 'trigger' => $workflow->trigger,
                //'actions' => $actions
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation exceptions
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            Log::error('Error creating workflow: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while creating the workflow.'
            ], 500);
        }
    }

    public function triggerWorkflow($workflowId)
    {
        $dripService = new DripService();
        $dripService->processDripWorkflow($workflowId);

        return response()->json(['message' => "Workflow {$workflowId} triggered successfully."]);
    }
    }


// namespace App\Http\Controllers;

// use App\Jobs\WaitForDuration;
// use App\Models\Customers;
// use App\Models\Workflow;
// use App\Models\WorkflowTrigger;
// use App\Models\WorkflowAction;
// use Illuminate\Support\Facades\Validator;
// use App\Models\Invoices;
// use App\Models\Opportunity;
// use App\Models\Tags;
// use App\Models\WorkflowContact as ModelsWorkflowContact;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\DB;
// use Twilio\Rest\Client;
// use Illuminate\Support\Facades\Mail;
// use App\Models\WorkflowContact;

// class WorkflowController extends Controller
// {
//     /**
//      * Store a new workflow with triggers and actions.
//      *
//      * @param \Illuminate\Http\Request $request
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function store(Request $request)
//     {
//         // Validate the request
//         $validated = $request->validate([
//             'workflow_name' => 'required|string|max:255',
//             'triggers' => 'required|array',
//             'triggers.*.type' => 'required|string',
//             'triggers.*.workflow_trigger_name' => 'required|string',
//             'triggers.*.filters' => 'nullable|array',
//             'actions' => 'required|array',
//             'actions.*.type' => 'required|string',
//             'actions.*.action_name' => 'required|string',
//             'actions.*.action_data' => 'nullable|array',
//             'actions.*.action_data.duration' => 'nullable|integer|min:0', // For wait action
//             'actions.*.action_data.test_phone_number' => 'nullable|string|max:15', // For send_sms action
//             'actions.*.action_data.message' => 'nullable|string', // For send_sms and send_whatsapp actions
//             'actions.*.action_data.attachments' => 'nullable|array', // For send_sms action
//             'actions.*.action_data.from_name' => 'nullable|string|max:255', // For send_email action
//             'actions.*.action_data.from_email' => 'nullable|email|max:255', // For send_email action
//             'actions.*.action_data.subject' => 'nullable|string|max:255', // For send_email action
//             // 'actions.*.action_data.attachments' => 'nullable|array', // For send_email action
//             'actions.*.action_data.to' => 'nullable|string|max:15', // For send_whatsapp action
//             'actions.*.action_data.template_name' => 'nullable|string|max:255', // For send_whatsapp action
//             'actions.*.action_data.template_parameters' => 'nullable|array', // For send
//             'actions.*.action_data.contact_id' => 'nullable|integer',
//             'actions.*.action_data.opportunity_id'=>'nullable|integer|exists:jo_opportunities,id',
//             'actions.*.action_data.opportunity_name' => 'nullable|string',
//             'actions.*.action_data.select_pipeline' => 'nullable|integer',  // Validate select_pipeline
//             'actions.*.action_data.select_stage' => 'nullable|string|max:255',     // Validate select_stage
//             'actions.*.action_data.lead_value' => 'nullable|numeric',               // Validate lead_value
//             'actions.*.action_data.opportunity_source' => 'nullable|string|max:255', // Validate opportunity_source
//             'actions.*.action_data.opportunity_status' => 'nullable|string|max:255', // Validate opportunity_status
//             'actions.*.action_data.action' => 'nullable|string|max:255',           // Validate action
//         ]);
    
//         // 1. Store the workflow
//         $workflow = Workflow::create([
//             'workflow_name' => $validated['workflow_name']
//         ]);
    
//         // 2. Store the triggers
//         foreach ($validated['triggers'] as $trigger) {
//             WorkflowTrigger::create([
//                 'workflow_id' => $workflow->id,
//                 'type' => $trigger['type'],
//                 'workflow_trigger_name' => $trigger['workflow_trigger_name'],
//                 'filters' => $trigger['filters'] ?? null
//             ]);
//         }
    
//         // 3. Store the actions and perform the operations
//        // Store the actions and perform the operations
//        foreach ($validated['actions'] as $action) {
//         WorkflowAction::create([
//             'workflow_id' => $workflow->id,
//             'type' => $action['type'],
//             'action_name' => $action['action_name'],
//             'action_data' => $action['action_data']// Use null if action_data is not set
//         ]);
//     }

//     // Perform the action based on its type
//     if ($action['type'] === 'add_contact_tag') {
//         $this->addContactTag($action['action_data']);
//     } elseif ($action['type'] === 'remove_contact_tag') {
//         $this->removeContactTags($action['action_data']);
//     } elseif ($action['type'] === 'add_to_workflow') {
//         $this->addContactToWorkflow($action['action_data']);
//     } elseif ($action['type'] === 'remove_from_workflow') {
//         $this->removeContactFromWorkflow($action['action_data']);
//     } elseif ($action['type'] === 'create_opportunity') {
//         $this->createOpportunity($action['action_data']);
//     } elseif ($action['type'] === 'update_opportunity') {
//         $this->updateOpportunity($action['action_data']);
//     } elseif ($action['type'] === 'remove_opportunity') {
//         $this->removeOpportunity($action['action_data']);
//     } elseif ($action['type'] === 'wait') {
//         $waitDuration = $action['action_data']['duration'] ?? 0;
//         WaitForDuration::dispatch($waitDuration, $action['action_data']); // Dispatch the job
//     }

            
        
    
//         // 4. Execute the workflow immediately after creation
//         $this->executeWorkflow($workflow);
    
//         return response()->json(['message' => 'Workflow created and executed successfully']);
//     }
//     protected function createOpportunity(array $actionData)
//     {
//         // Check if required data is present
//         if (empty($actionData['contact_id']) || 
//             empty($actionData['opportunity_name']) || 
//             empty($actionData['select_pipeline']) || 
//             empty($actionData['select_stage']) || 
//             empty($actionData['lead_value'])) {
//             throw new \Exception('Required opportunity data is missing.');
//         }
    
//         // Create a new opportunity instance
//         $opportunity = new Opportunity(); // Assuming you have an Opportunity model
//         $opportunity->contact_id = $actionData['contact_id'] ?? null;
//         $opportunity->opportunity_name = $actionData['opportunity_name'] ?? null;
//         $opportunity->select_pipeline = $actionData['select_pipeline'] ?? null;
//         $opportunity->select_stage = $actionData['select_stage'] ?? null;
//         $opportunity->lead_value = $actionData['lead_value'] ?? null;
//         $opportunity->opportunity_source = $actionData['opportunity_source'] ?? null; // Optional
//         $opportunity->opportunity_status = $actionData['opportunity_status'] ?? 'Open'; // Default to 'Open'
//         $opportunity->action = $actionData['action'] ?? null; // Optional
//         $opportunity->created_at = now();
//         $opportunity->updated_at = now();
    
//         // Save the opportunity to the database
//         $opportunity->save();
//     }
    
//     protected function updateOpportunity(array $actionData)
//     {
//         // Check if required data is present
//         if (empty($actionData['opportunity_id'])) {
//             throw new \Exception('Opportunity ID is required for update.');
//         }
    
//         // Find the opportunity by ID
//         $opportunity = Opportunity::find($actionData['opportunity_id']);
    
//         if (!$opportunity) {
//             throw new \Exception('Opportunity not found.');
//         }
    
//         // Update opportunity fields
//         if (!empty($actionData['opportunity_name'])) {
//             $opportunity->opportunity_name = $actionData['opportunity_name'];
//         }
    
//         if (isset($actionData['lead_value'])) {
//             $opportunity->lead_value = $actionData['lead_value']; // Update only if provided
//         }
    
//         if (isset($actionData['opportunity_status'])) {
//             $opportunity->opportunity_status = $actionData['opportunity_status']; // Update only if provided
//         }
    
//         // Save the updated opportunity
//         $opportunity->save();
//     }
    
// protected function removeOpportunity(array $actionData)
// {
//     // Check if required data is present
//     if (empty($actionData['opportunity_id'])) {
//         throw new \Exception('Required opportunity ID is missing for removal.');
//     }

//     // Find the opportunity by ID
//     $opportunity = Opportunity::find($actionData['opportunity_id']);

//     if (!$opportunity) {
//         throw new \Exception('Opportunity not found.');
//     }

//     // Delete the opportunity
//     $opportunity->delete();
// }


//     private function addContactToWorkflow($actionData)
//     {
//         $contactId = $actionData['contact_id'];
//         $workflowId = $actionData['workflow_id'];
    
//         // Logic to add the contact to the workflow
//         // This may involve updating a pivot table or similar structure
//         WorkflowContact::create([
//             'workflow_id' => $workflowId,
//             'contact_id' => $contactId
//         ]);
    
//         Log::info("Contact {$contactId} added to workflow {$workflowId}.");
//     }
    
//     private function removeContactFromWorkflow($actionData)
//     {
//         $contactId = $actionData['contact_id'];
//         $workflowId = $actionData['workflow_id'];
    
//         // Logic to remove the contact from the workflow
//         // This may involve deleting a record from a pivot table
//         WorkflowContact::where('workflow_id', $workflowId)
//             ->where('contact_id', $contactId)
//             ->delete();
    
//         Log::info("Contact {$contactId} removed from workflow {$workflowId}.");
//     }
        

// // Method to add a tag to a contact
// private function addContactTag($actionData)
// {
//     $contactId = $actionData['contact_id'];
//     $tagId = $this->getTagIdByName($actionData['tag_name']);

//     if ($tagId !== null) {
//         // Fetch the current tags for the contact
//         $contact = Customers::find($contactId);
//         if (!$contact) {
//             Log::error("Contact with ID {$contactId} not found.");
//             return;
//         }

//         $currentTags = json_decode($contact->tags, true);
        
//         // Ensure currentTags is an array
//         if (!is_array($currentTags)) {
//             $currentTags = []; // Fallback to an empty array if null
//         }

//         // Log current tags safely
//         Log::info("Current tags for contact {$contactId}: ", ['tags' => $currentTags]);
        
//         // Check if tagId is valid and not already present
//         if (!in_array($tagId, $currentTags)) {
//             $currentTags[] = $tagId;
//             // Update the tags field
//             $contact->tags = json_encode(array_values($currentTags)); // Re-index array
//             if ($contact->save()) {
//                 Log::info("Added tag ID {$tagId} to contact {$contactId}.");
//             } else {
//                 Log::error("Failed to save contact {$contactId} after adding tag ID {$tagId}. Errors: " . json_encode($contact->getErrors()));
//             }
//         } else {
//             Log::info("Tag ID {$tagId} is already present for contact {$contactId}. No action taken.");
//         }
//     } else {
//         Log::error("Tag with name '{$actionData['tag_name']}' not found.");
//     }
// }



// private function removeContactTags($actionData)
// {
//     $contactId = $actionData['contact_id'];
//     $tagNames = $actionData['tag_names'] ?? null; // Ensure this key is correctly named

//     // Check if tagNames is an array and not empty
//     if (!is_array($tagNames) || empty($tagNames)) {
//         Log::error("No tags provided for contact {$contactId}. Action data: ", $actionData);
//         return;
//     }

//     // Fetch the current tags for the contact
//     $contact = Customers::find($contactId);
//     if (!$contact) {
//         Log::error("Contact with ID {$contactId} not found.");
//         return;
//     }

//     $currentTags = json_decode($contact->tags, true) ?: [];

//     // Log current tags
//     Log::info("Current tags for contact {$contactId}: ", $currentTags);

//     // Remove the tags that exist
//     foreach ($tagNames as $tagName) {
//         $tagId = $this->getTagIdByName($tagName);
//         if ($tagId !== null && ($key = array_search($tagId, $currentTags)) !== false) {
//             unset($currentTags[$key]);
//             Log::info("Removed tag ID {$tagId} from contact {$contactId}.");
//         } else {
//             Log::info("Tag ID {$tagId} is not present for contact {$contactId}. No action taken.");
//         }
//     }

//     // Update the tags field
//     $contact->tags = json_encode(array_values($currentTags)); // Re-index array
//     $contact->save();
// }



// // Helper method to get the tag ID by name
// private function getTagIdByName($tagName)
// {
//     $tag = Tags::where('tags_name', $tagName)->first();
//     if (!$tag) {
//         Log::error("Tag with name '{$tagName}' not found in the tags table.");
//     }
//     return $tag ? $tag->id : null;
// }
//     /**
//      * Execute the specified workflow and its actions.
//      *
//      * @param \App\Models\Workflow $workflow
//      * @return void
//      */
//     public function executeWorkflow(Workflow $workflow)
//     {
//         Log::info("Executing workflow: {$workflow->id}");
    
//         // Ensure the customer relationship is loaded
//         $customer = $workflow->customer; // Fetch the customer relationship
    
//         // Check if the customer is loaded and has a name
//         $customerName = $customer ? $customer->name : 'Unknown Customer'; // Handle cases where customer is null
    
//         foreach ($workflow->actions as $action) {
//             try {
//                 $this->executeAction($action, $customerName); // Pass customer name
//                 Log::info("Executed action: {$action->action_name} of type: {$action->type}");
//             } catch (\Exception $e) {
//                 Log::error("Failed to execute action {$action->action_name}: " . $e->getMessage());
//             }
//         }
//     }
    

//     /**
//      * Execute a specific action.
//      *
//      * @param \App\Models\WorkflowAction $action
//      * @return void
//      */
//     private function executeAction(WorkflowAction $action, $customerName)
//     {
//         switch ($action->type) {
//             case 'send_sms':
//                 $this->sendSms($action->action_data, $customerName); // Pass customer name
//                 break;
    
//             case 'send_email':
//                 $this->sendEmail($action->action_data, $customerName); // Pass customer name
//                 break;
    
//             case 'send_whatsapp':
//                // $this->sendWhatsApp($action->action_data, $customerName); // Pass customer name
//                 break;
    
//             case 'add_invoice':
//                 $this->addInvoice($action->action_data);
//                 break;
    
//             // Add more actions here as needed
//         }
//     }
    
//     /**
//      * Send an SMS using the provided data.
//      *
//      * @param array $data
//      * @return void
//      */
//     private function sendSms(array $data, string $customerName)
//     {
//         $phoneNumber = $data['test_phone_number'];
//         $message = str_replace('{contact.name}', $customerName, $data['message']); // Update to use {contact.name}
    
//         Log::info("Sending SMS to {$phoneNumber}: {$message}");
    
//         try {
//             // Create a Twilio client
//             $client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
            
//             // Send the SMS
//             $client->messages->create($phoneNumber, [
//                 'from' => env('TWILIO_PHONE_NUMBER'),
//                 'body' => $message
//             ]);
    
//             Log::info("SMS sent successfully to {$phoneNumber}");
//         } catch (\Exception $e) {
//             Log::error("Failed to send SMS to {$phoneNumber}: " . $e->getMessage());
//         }
//     }
    

//     /**
//      * Send an email using the provided data.
//      *
//      * @param array $data
//      * @return void
//      */
//     private function sendEmail(array $data, string $customerName)
//     {
//         $fromName = $data['from_name'];
//         $fromEmail = $data['from_email'];
//         $subject = $data['subject'];
//         $message = str_replace('{contact.name}', $customerName, $data['message']); // Update to use {contact.name}
//         $attachments = $data['attachments'] ?? [];
    
//         Log::info("Sending email to {$fromEmail} with subject {$subject}");
    
//         Mail::raw($message, function ($mail) use ($fromName, $fromEmail, $subject, $attachments) {
//             $mail->from($fromEmail, $fromName)
//                  ->subject($subject)
//                  ->to($fromEmail);  // Modify recipient as needed
    
//             // Attach files if any
//             foreach ($attachments as $attachment) {
//                 if (isset($attachment['type']) && $attachment['type'] === 'file' && !empty($attachment['url'])) {
//                     $mail->attach($attachment['url']);
//                 } else {
//                     Log::warning("Attachment URL is invalid: ", $attachment);
//                 }
//             }
//         });
//     }

//     /**
//      * Add a new invoice using the provided data.
//      *
//      * @param array $data
//      * @return void
//      */
//     private function addInvoice(array $data)
//     {
//         Log::info("Attempting to add invoice with data: ", $data);

//         $validator = Validator::make($data, [
//             'invoicenumber' => 'required|numeric',
//             'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
//                 $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
//                 $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
//                 $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();

//                 if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
//                     $fail("The selected contact ID does not exist in any of the specified tables.");
//                 }
//             }],
//             'invoicedate' => 'required|date',
//             'duedate' => 'required|date',
//             'discount' => 'required|string',
//             'discount_suffix' => 'nullable|string|in:%,"flat"',
//             'currency' => 'required|string',
//             'terms' => 'nullable|string',
//             'tags' => 'nullable|array',
//             'tags.*' => 'exists:jo_tags,id',
//             'tax1' => 'nullable|numeric',
//             'tax1_suffix' => 'nullable|string',
//             'tax2' => 'nullable|numeric',
//             'tax2_suffix' => 'nullable|string',
//             'applydiscount' => 'boolean',
//             'taxtype' => 'nullable|string',
//             'subtotal' => 'nullable|numeric',
//             'total' => 'nullable|numeric',
//             'tax_percent' => 'nullable|numeric',
//             'discount_percent' => 'nullable|numeric',
//             'tax_amount' => 'nullable|numeric',
//             'invoice_status' => 'required|string',
//             'organization_name' => 'required|numeric|exists:jo_organizations,id',
//         ]);

//         if ($validator->fails()) {
//             Log::error('Validation failed for adding invoice: ', $validator->errors()->toArray());
//             return; // Handle validation errors as needed
//         }

//         try {
//             $invoice = Invoices::create($validator->validated());
//             Log::info("Invoice created with number: {$invoice->invoicenumber} for contact ID: {$data['contacts']}");
//         } catch (\Exception $e) {
//             Log::error("Failed to create invoice: " . $e->getMessage());
//         }
//     }
//     public function removeFromWorkflow(Request $request)
//     {
//         // Validate the request
//         $validated = $request->validate([
//             'workflow_id' => 'required|integer|exists:jo_workflows,id',
//             'contact_id' => 'required|integer'
//         ]);

//         // Implement logic to remove contact from the specified workflow
//         // Assuming you have a pivot table or a method to detach a contact from the workflow
//         try {
//             $workflow = Workflow::find($validated['workflow_id']);
//             // Assuming you have a many-to-many relationship defined
//             $workflow->contacts()->detach($validated['contact_id']);
//             Log::info("Removed contact ID: {$validated['contact_id']} from workflow ID: {$validated['workflow_id']}");

//             return response()->json(['message' => 'Contact removed from workflow successfully']);
//         } catch (\Exception $e) {
//             Log::error("Failed to remove contact from workflow: " . $e->getMessage());
//             return response()->json(['error' => 'Failed to remove contact from workflow'], 500);
//         }
//     }
//     public function removeFromAllWorkflows(Request $request)
//     {
//         // Validate the request
//         $validated = $request->validate([
//             'contact_id' => 'required|integer'
//         ]);

//         // Implement logic to remove contact from all workflows
//         try {
//             // Assuming you have a method to detach from all workflows
//             $workflows = Workflow::all();
//             foreach ($workflows as $workflow) {
//                 // Detach the contact from each workflow
//                 $workflow->contacts()->detach($validated['contact_id']);
//             }

//             Log::info("Removed contact ID: {$validated['contact_id']} from all workflows");

//             return response()->json(['message' => 'Contact removed from all workflows successfully']);
//         } catch (\Exception $e) {
//             Log::error("Failed to remove contact from all workflows: " . $e->getMessage());
//             return response()->json(['error' => 'Failed to remove contact from all workflows'], 500);
//         }
//     }
//     public function addToWorkflow(Request $request)
//     {
//         // Validate the request
//         $validated = $request->validate([
//             'workflow_id' => 'required|integer|exists:jo_workflows,id',
//             'customer_id' => 'required|integer|exists:jo_customers,id'
//         ]);
    
//         try {
//             // Retrieve the workflow and add the customer to it
//             $workflow = Workflow::find($validated['workflow_id']);
    
//             // Assuming a many-to-many relationship with customers
//             $workflow->customers()->attach($validated['customer_id']);
    
//             // Retrieve triggers and actions for the specific workflow
//             $actions = $workflow->actions; // Assuming you have a relationship defined in your Workflow model
    
//             // Process each action
//             foreach ($actions as $action) {
//                 // Check action type and call the respective method
//                 switch ($action->type) {
//                     case 'send_sms':
//                         $this->sendSms($action->action_data, $validated['customer_id']);
//                         break;
//                     case 'send_email':
//                         $this->sendEmail($action->action_data, $validated['customer_id']);
//                         break;
//                     case 'send_whatsapp':
//                        // $this->sendWhatsApp($action->action_data, $validated['customer_id']);
//                         break;
//                     default:
//                         Log::warning("Unknown action type: " . $action->type);
//                         break;
//                 }
//             }
    
//             return response()->json(['message' => 'Customer added to workflow and actions processed successfully']);
//         } catch (\Exception $e) {
//             // Log the error and return a failure response
//             Log::error("Failed to add customer to workflow: " . $e->getMessage());
//             return response()->json(['error' => 'Failed to add customer to workflow'], 500);
//         }
//     }
//     // public function handleWorkflow(Request $request)
//     // {
//     //     // Validate the incoming request
//     //     $validatedData = $request->validate([
//     //         'workflow_name' => 'required|string',
//     //         'triggers' => 'required|array',
//     //         'triggers.*.type' => 'required|string|in:tag_added,tag_removed',
//     //         'triggers.*.workflow_trigger_name' => 'required|string',
//     //         'triggers.*.filters' => 'required|array',
//     //         'triggers.*.filters.field' => 'required|string',
//     //         'triggers.*.filters.value' => 'required|string',
//     //         'actions' => 'required|array',
//     //         'actions.*.type' => 'required|string|in:add_contact_tag,remove_contact_tag',
//     //         'actions.*.action_name' => 'required|string',
//     //         'actions.*.action_data' => 'required|array',
//     //         'actions.*.action_data.contact_id' => 'required|integer',
//     //         'actions.*.action_data.tag_name' => 'required|string',
//     //     ]);

//     //     // Process triggers
//     //     foreach ($validatedData['triggers'] as $trigger) {
//     //         $this->handleTrigger($trigger);
//     //     }

//     //     // Process actions
//     //     foreach ($validatedData['actions'] as $action) {
//     //         $this->handleAction($action);
//     //     }

//     //     return response()->json(['message' => 'Workflow processed successfully.']);
//     // }

//     // private function handleTrigger(array $trigger)
//     // {
//     //     // Here you can add your logic to handle each trigger
//     //     // For example, log the trigger or execute specific actions based on the trigger type
//     //     Log::info('Trigger handled: ', $trigger);
//     // }

//     // private function handleAction(array $action)
//     // {
//     //     // Here you can add your logic to handle each action
//     //     // You may want to call a service to manage contacts and tags
//     //     Log::info('Action handled: ', $action);

//     //     switch ($action['type']) {
//     //         case 'add_contact_tag':
//     //             $this->addTagToContact($action['action_data']);
//     //             break;
//     //         case 'remove_contact_tag':
//     //             $this->removeTagFromContact($action['action_data']);
//     //             break;
//     //     }
//     // }

//     // private function addTagToContact(array $actionData)
//     // {
//     //     // Implement your logic to add a tag to a contact
//     //     // For example, using a Contact model to find the contact and add the tag
//     //     $contactId = $actionData['contact_id'];
//     //     $tagName = $actionData['tag_name'];

//     //     // Example implementation (replace with your actual logic)
//     //     // Contact::find($contactId)->tags()->attach($tagName);

//     //     Log::info("Added tag '{$tagName}' to contact ID {$contactId}.");
//     // }

//     // private function removeTagFromContact(array $actionData)
//     // {
//     //     // Implement your logic to remove a tag from a contact
//     //     $contactId = $actionData['contact_id'];
//     //     $tagName = $actionData['tag_name'];

//     //     // Example implementation (replace with your actual logic)
//     //     // Contact::find($contactId)->tags()->detach($tagName);

//     //     Log::info("Removed tag '{$tagName}' from contact ID {$contactId}.");
//     // }
// }    

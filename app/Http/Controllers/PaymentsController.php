<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Payments;
use App\Models\Crmentity;
//use App\Models\Tags;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve paginated payments
            $payments = Payments::paginate(10); // Adjust 10 to the number of payments per page you want

            // Check if any payments found
            if ($payments->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'payments' => $payments->items(),
                'pagination' => [
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem(),
                ],
            ], 200);

        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve payments: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve payments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'invoice_number' => 'nullable|integer|exists:jo_invoices,id',
                'contacts' => 'required|integer',
                'projects' => 'required|exists:jo_projects,id',
                'payment_date' => 'nullable|date',
                'payment_method' => 'required|string',
                'currency' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'amount' => 'required|numeric',
                'note' => 'nullable|string',
            ]);
    
            // Process the invoice_number
            // $invoiceNumber = null;
            // if (isset($validatedData['invoice_number'])) {
            //     $invoiceNumber = DB::table('jo_invoices')->where('id', $validatedData['invoice_number'])->value('invoicenumber');
            //     if (!$invoiceNumber) {
            //         throw ValidationException::withMessages(['invoice_number' => "Invoice with ID {$validatedData['invoice_number']} not found"]);
            //     }
            // }
    
            // Handle contact (clients, customers, leads)
            // if (isset($validatedData['contacts'])) {
            //     $contactName = null;
    
            //     // Check if it's a client
            //     $client = DB::table('jo_clients')->where('id', $validatedData['contacts'])->first(['name']);
            //     if ($client) {
            //         $contactName = $client->name;
            //     }
    
            //     // Check if it's a customer
            //     if (!$contactName) {
            //         $customer = DB::table('jo_customers')->where('id', $validatedData['contacts'])->first(['name']);
            //         if ($customer) {
            //             $contactName = $customer->name;
            //         }
            //     }
    
            //     // Check if it's a lead
            //     if (!$contactName) {
            //         $lead = DB::table('jo_leads')->where('id', $validatedData['contacts'])->first(['name']);
            //         if ($lead) {
            //             $contactName = $lead->name;
            //         }
            //     }
    
            //     // If no contact found, throw validation error
            //     if (!$contactName) {
            //         throw ValidationException::withMessages(['contacts' => "Contact with ID '{$validatedData['contacts']}' does not exist in any relevant table"]);
            //     }
    
            //     // Assign contact name
            //     $validatedData['contacts'] = $contactName;
            // }
    
            // // Process the projects
            // $projectId = $validatedData['projects'];
            // $projectName = DB::table('jo_projects')->where('id', $projectId)->value('project_name');
            // if (!$projectName) {
            //     throw ValidationException::withMessages(['projects' => "Project with ID '{$validatedData['projects']}' not found"]);
            // }
    
            // // Store tags names and colors as JSON
            // if (isset($validatedData['tags'])) {
            //     $tagsDetails = [];
            //     foreach ($validatedData['tags'] as $tagId) {
            //         $tagDetails = DB::table('jo_tags')->where('id', $tagId)->first(['tags_name', 'tag_color']);
            //         if ($tagDetails) {
            //             $tagsDetails[] = [
            //                 'tags_name' => $tagDetails->tags_name,
            //                 'tag_color' => $tagDetails->tag_color,
            //             ];
            //         } else {
            //             throw ValidationException::withMessages(['tags' => "Tag with ID '{$tagId}' not found"]);
            //         }
            //     }
            //     $validatedData['tags'] = json_encode($tagsDetails);
            // }
    
            // Retrieve or create a new Crmentity record for Payments
            $defaultCrmentity = Crmentity::where('setype', 'Payments')->first();
    
            if (!$defaultCrmentity) {
                // Create a default Crmentity if it doesn't exist
                $defaultCrmentity = Crmentity::create([
                    'crmid' => Crmentity::max('crmid') + 1,
                    'smcreatorid' => 0, // Replace with appropriate default
                    'smownerid' => 0, // Replace with appropriate default
                    'setype' => 'Payments',
                    'description' => '',
                    'createdtime' => now(),
                    'modifiedtime' => now(),
                    'viewedtime' => now(),
                    'status' => '',
                    'version' => 0,
                    'presence' => 0,
                    'deleted' => 0,
                    'smgroupid' => 0,
                    'source' => '',
                    'label' => '',
                ]);
    
                if (!$defaultCrmentity) {
                    throw new \Exception('Failed to create default Crmentity for Payments');
                }
            }
    
            // Create a new Crmentity record with a new crmid
            $newCrmentity = new Crmentity();
            $newCrmentity->crmid = Crmentity::max('crmid') + 1;
            $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid ?? 0; // Replace with appropriate default
            $newCrmentity->smownerid = $defaultCrmentity->smownerid ?? 0; // Replace with appropriate default
            $newCrmentity->setype = 'Payments';
            $newCrmentity->description = $defaultCrmentity->description ?? '';
            $newCrmentity->createdtime = now();
            $newCrmentity->modifiedtime = now();
            $newCrmentity->viewedtime = now();
            $newCrmentity->status = $defaultCrmentity->status ?? '';
            $newCrmentity->version = $defaultCrmentity->version ?? 0;
            $newCrmentity->presence = $defaultCrmentity->presence ?? 0;
            $newCrmentity->deleted = $defaultCrmentity->deleted ?? 0;
            $newCrmentity->smgroupid = $defaultCrmentity->smgroupid ?? 0;
            $newCrmentity->source = $defaultCrmentity->source ?? '';
            $newCrmentity->label = 'amount'; // Adjust as per your requirement
            $newCrmentity->save();
    
            // Set the new crmid as the payment ID
            $validatedData['id'] = $newCrmentity->crmid;
    
            // Create the payment record in the database
            $payment = Payments::create([
                'invoice_number' => $validatedData['invoice_number'],
                'contacts' => $validatedData['contacts'], // Assign contact name instead of ID
                'projects' => $validatedData['projects'],
                'payment_date' => $validatedData['payment_date'],
                'payment_method' => $validatedData['payment_method'],
                'currency' => $validatedData['currency'],
                'tags' => $validatedData['tags'],
                'amount' => $validatedData['amount'],
                'note' => $validatedData['note'],
                'id' => $newCrmentity->crmid, // Assign the newly created Crmentity ID to the payment entry
            ]);
    
            DB::commit();
    
            // Prepare the response with payment details
            // $paymentArray = $payment->toArray();
            // $paymentArray['invoice_number'] = $invoiceNumber; // Assign invoice number instead of ID
    
            // Return a success response with the created payment object
            return response()->json(['message' => 'Payment created successfully', 'payment' => $payment], 201);
        } catch (ValidationException $e) {
            // Return validation error response
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to create payment: ' . $e->getMessage());
    
            // Return an error response with the actual error message
            return response()->json(['error' => 'Failed to create payment: ' . $e->getMessage()], 500);
        }
    }
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $payment = Payments::findOrFail($id);
            return response()->json(['payment' => $payment], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Payment not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve payment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve payment'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'invoice_number' => 'nullable|integer|exists:jo_invoices,id',
            'contacts' => 'nullable|exists:jo_crmentity,crmid',
            'projects' => 'nullable|exists:jo_projects,id',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'currency' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);

        // Find the payment record by ID
        $payment = Payments::findOrFail($id);

        // // Process the invoice_number if provided
        // if (isset($validatedData['invoice_number'])) {
        //     $invoiceNumber = $validatedData['invoice_number'];
        //     $invoiceNumber = DB::table('jo_invoices')->where('id', $invoiceNumber)->value('invoicenumber');
        //     if (!$invoiceNumber) {
        //         throw ValidationException::withMessages(['invoice_number' => "Invoice with ID {$validatedData['invoice_number']} not found"]);
        //     }
        //     $payment->invoice_number = $invoiceNumber;
        // }

        // // Process the contacts array to fetch names based on IDs
        // $contactDetails = [];
        // foreach ($validatedData['contacts'] as $contact) {
        //     switch ($contact['type']) {
        //         case 'customer':
        //             $customer = DB::table('jo_customers')->where('id', $contact['id'])->first(['id', 'name']);
        //             if ($customer) {
        //                 $contactDetails[] = [
        //                     'type' => 'customer',
        //                     'id' => $customer->id,
        //                     'name' => $customer->name,
        //                 ];
        //             } else {
        //                 throw ValidationException::withMessages(['contacts' => "Customer with ID {$contact['id']} not found"]);
        //             }
        //             break;
        //         case 'client':
        //             $client = DB::table('jo_clients')->where('id', $contact['id'])->first(['id', 'clientsname']);
        //             if ($client) {
        //                 $contactDetails[] = [
        //                     'type' => 'client',
        //                     'id' => $client->id,
        //                     'name' => $client->clientsname,
        //                 ];
        //             } else {
        //                 throw ValidationException::withMessages(['contacts' => "Client with ID {$contact['id']} not found"]);
        //             }
        //             break;
        //         case 'lead':
        //             $lead = DB::table('jo_leads')->where('id', $contact['id'])->first(['id', 'name']);
        //             if ($lead) {
        //                 $contactDetails[] = [
        //                     'type' => 'lead',
        //                     'id' => $lead->id,
        //                     'name' => $lead->name,
        //                 ];
        //             } else {
        //                 throw ValidationException::withMessages(['contacts' => "Lead with ID {$contact['id']} not found"]);
        //             }
        //             break;
        //         default:
        //             throw ValidationException::withMessages(['contacts' => 'Invalid contact type provided']);
        //     }
        // }

        // // Encode entire contacts array as JSON
        // $payment->contacts = json_encode($contactDetails);

        // // Process the projects
        // $projectId = $validatedData['projects'];
        // $projectName = DB::table('jo_projects')->where('id', $projectId)->value('project_name');
        // if (!$projectName) {
        //     throw ValidationException::withMessages(['projects' => "Project with ID '{$validatedData['projects']}' not found"]);
        // }
        // $payment->projects = $projectName;

        // // Store tags names and colors as JSON
        // if (isset($validatedData['tags'])) {
        //     $tagsDetails = [];
        //     foreach ($validatedData['tags'] as $tagId) {
        //         $tagDetails = DB::table('jo_tags')->where('id', $tagId)->first(['tags_name', 'tag_color']);
        //         if ($tagDetails) {
        //             $tagsDetails[] = [
        //                 'tags_name' => $tagDetails->tags_name,
        //                 'tag_color' => $tagDetails->tag_color,
        //             ];
        //         } else {
        //             throw ValidationException::withMessages(['tags' => "Tag with ID '{$tagId}' not found"]);
        //         }
        //     }
        //     $payment->tags = json_encode($tagsDetails);
        // }

        // Update other fields
        $payment->payment_date = $validatedData['payment_date'] ?? $payment->payment_date;
        $payment->payment_method = $validatedData['payment_method'] ?? $payment->payment_method;
        $payment->currency = $validatedData['currency'] ?? $payment->currency;
        $payment->amount = $validatedData['amount'] ?? $payment->amount;
        $payment->note = $validatedData['note'] ?? $payment->note;

        // Save the updated payment record
        $payment->save();

        // Prepare the response with contact details
        // $paymentArray = $payment->toArray();
        // $paymentArray['invoice_number'] = $invoiceNumber; // Assign invoice number instead of ID

        // Return a success response with the updated payment object
        return response()->json(['message' => 'Payment updated successfully', 'payment' => $payment], 200);
    } catch (ValidationException $e) {
        // Return validation error response
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        // Log the error
        Log::error('Failed to update payment: ' . $e->getMessage());

        // Return an error response with the actual error message
        return response()->json(['error' => 'Failed to update payment: ' . $e->getMessage()], 500);
    }
}

   /**
 * Search for payments based on criteria.
 */
public function search(Request $request)
{
    try {
        // Validate the search input
        $validatedData = $request->validate([
            'invoice_number' => 'nullable|integer|exists:jo_invoices,invoicenumber',
            'contacts' => 'nullable|string',
            'projects' => 'nullable|string|exists:jo_projects,name',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'currency' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'amount' => 'nullable|numeric',
            'note' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1', // Add validation for per_page
        ]);

        // Initialize the query builder
        $query = Payments::query();

        // Apply search filters
        if (isset($validatedData['invoice_number'])) {
            $query->where('invoice_number', $validatedData['invoice_number']);
        }

        if (isset($validatedData['contacts'])) {
            $query->where('contacts', 'like', '%' . $validatedData['contacts'] . '%');
        }

        if (isset($validatedData['projects'])) {
            $query->where('projects', $validatedData['projects']);
        }

        if (isset($validatedData['payment_date'])) {
            $query->whereDate('payment_date', $validatedData['payment_date']);
        }

        if (isset($validatedData['payment_method'])) {
            $query->where('payment_method', $validatedData['payment_method']);
        }

        if (isset($validatedData['currency'])) {
            $query->where('currency', $validatedData['currency']);
        }

        if (isset($validatedData['amount'])) {
            $query->where('amount', $validatedData['amount']);
        }

        if (isset($validatedData['note'])) {
            $query->where('note', 'like', '%' . $validatedData['note'] . '%');
        }

        // Handle tags filter if provided
        if (isset($validatedData['tags'])) {
            foreach ($validatedData['tags'] as $tag) {
                if (isset($tag['tags_name'])) {
                    $query->whereJsonContains('tags->tags_name', $tag['tags_name']);
                }
                if (isset($tag['tag_color'])) {
                    $query->whereJsonContains('tags->tag_color', $tag['tag_color']);
                }
            }
        }

        // Paginate the search results
        $perPage = $validatedData['per_page'] ?? 10; // default per_page value
        $payments = $query->paginate($perPage);

        // Check if any payments found
        if ($payments->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No matching records found',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'payments' => $payments->items(),
            'pagination' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'from' => $payments->firstItem(),
                'to' => $payments->lastItem(),
            ],
        ], 200);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        Log::error('Failed to search payments: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to search payments: ' . $e->getMessage()], 500);
    }
}

}
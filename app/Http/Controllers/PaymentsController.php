<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Payments;
use App\Models\Crmentity;
use App\Models\Customers;
use App\Models\Employee;
use App\Models\Leads;
use App\Models\Tags;

//use App\Models\Tags;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Set the number of items per page, default is 10
            $perPage = $request->input('per_page', 10);
    
            // Retrieve paginated payments with related data
            $payments = Payments::with(['invoice', 'project'])
                ->select(
                    'id',
                    'invoice_number',
                    'contacts',
                    'projects',
                    'payment_date',
                    'payment_method',
                    'currency',
                    'amount',
                    'note',
                    //'tags',
                )
                ->paginate($perPage);
    
            // Prepare array to hold formatted payments
            $formattedPayments = [];
    
            // Iterate through each payment to format data
            foreach ($payments as $payment) {
                // Retrieve contact name from related models
                $contactName = null;
    
                // Check if contacts exist and retrieve contact name accordingly
                if ($payment->contacts) {
                    $customer = Customers::find($payment->contacts);
                    if ($customer) {
                        $contactName = $customer->name;
                    } else {
                        // If customer not found, check in clients or leads
                        $client = Clients::find($payment->contacts);
                        if ($client) {
                            $contactName = $client->name;
                        } else {
                            $lead = Leads::find($payment->contacts);
                            if ($lead) {
                                $contactName = $lead->name;
                            }
                        }
                    }
                }
    
                // Fetch tags for the current payment
                $tags = [];
                if (is_string($payment->tags)) {
                    $tagIds = json_decode($payment->tags, true);
                    $tags = Tags::whereIn('id', $tagIds)
                        ->select('tags_name', 'tag_color')
                        ->get()
                        ->toArray();
                } elseif (is_array($payment->tags)) {
                    // Assuming tags is already an array in some cases
                    $tagIds = $payment->tags;
                    $tags = Tags::whereIn('id', $tagIds)
                        ->select('tags_name', 'tag_color')
                        ->get()
                        ->toArray();
                }
    

                // Build formatted payment array
                $formattedPayments[] = [
                    'id' => $payment->id,
                    'invoice_number' => $payment->invoice ? $payment->invoice->invoicenumber : null,
                    'contact' => $contactName,
                    'project' => $payment->project ? $payment->project->project_name : null,
                    'tags' => $tags,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'currency' => $payment->currency,
                    'amount' => $payment->amount,
                    'note' => $payment->note,
                ];
            }
    
            // Return JSON response with formatted data and pagination information
            return response()->json([
                'status' => 200,
                'payments' => $formattedPayments,
                'pagination' => [
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem(),
                ],
            ], 200);
    
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to retrieve payments: ' . $e->getMessage());
    
            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve payments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'invoice_number' => 'nullable|integer|exists:jo_invoices,id',
            'contacts' => ['required', 'integer', function ($attribute, $value, $fail) {
                // Check if the contact ID exists in any of the specified tables
                $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();

                if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                    $fail("The selected contact ID does not exist in any of the specified tables.");
                }
            }],
            'projects' => 'required|exists:jo_projects,id',
            'payment_date' => 'nullable|date',
            'payment_method' => 'required|string',
            'currency' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
        ]);

        // Create Crmentity record via CrmentityController
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Payments', $validatedData['amount']);
        
        // Prepare payment data including crmid as id
        $paymentData = [
            'id' => $crmid, // Set the crmid as the id
            'invoice_number' => $validatedData['invoice_number'],
            'contacts' => $validatedData['contacts'],
            'projects' => $validatedData['projects'],
            'payment_date' => $validatedData['payment_date'],
            'payment_method' => $validatedData['payment_method'],
            'currency' => $validatedData['currency'],
            'tags' => $validatedData['tags'] ? json_encode($validatedData['tags']) : null,
            'amount' => $validatedData['amount'],
            'note' => $validatedData['note'],
        ];

        // Create the payment with the crmid
        $payment = Payments::create($paymentData);

        DB::commit();

        // Return success response
        return response()->json([
            'message' => 'Payment created successfully',
            'payment' => $payment,
        ], 201);

    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create payment: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create payment: ' . $e->getMessage()], 500);
    }
}

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
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
    
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'invoice_number' => 'nullable|integer|exists:jo_invoices,id',
                'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                    // Check if the contact ID exists in any of the specified tables
                    $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                    $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                    $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();
    
                    if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                        $fail("The selected contact ID does not exist in any of the specified tables.");
                    }
                }],
                'projects' => 'nullable|exists:jo_projects,id',
                'payment_date' => 'nullable|date',
                'payment_method' => 'nullable|string',
                'currency' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'amount' => 'nullable|numeric',
                'note' => 'nullable|string',
                'crmentity_label' => 'nullable|string|max:255', // Assuming you want to update the Crmentity label
            ]);
    
            // Update the Payments record
            $payment = Payments::findOrFail($id);
            $payment->payment_date = $validatedData['payment_date'] ?? $payment->payment_date;
            $payment->payment_method = $validatedData['payment_method'] ?? $payment->payment_method;
            $payment->currency = $validatedData['currency'] ?? $payment->currency;
            $payment->amount = $validatedData['amount'] ?? $payment->amount;
            $payment->note = $validatedData['note'] ?? $payment->note;
            $payment->save();
    
            // Update the Crmentity record if provided
            if (isset($validatedData['amount'])) {
                $crmentity = Crmentity::where('crmid', $validatedData['amount'])->first();
                if ($crmentity) {
                    $crmentity->label = $validatedData['amount'] ?? $crmentity->label;
                    $crmentity->save();
                }
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Payment and Crmentity updated successfully',
                'payment' => $payment
            ], 200);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update payment or Crmentity: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update payment or Crmentity: ' . $e->getMessage()], 500);
        }
    }
    
public function search(Request $request)
{
    try {
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
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = Payments::query();
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
        $perPage = $validatedData['per_page'] ?? 10; // default per_page value
        $payments = $query->paginate($perPage);
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
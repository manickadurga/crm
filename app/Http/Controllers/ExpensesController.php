<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Expense;
use App\Models\Leads;
use App\Models\Tags;
use App\Models\Crmentity;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve paginated expenses
            $expenses = Expense::paginate(10); // Adjust 10 to the number of expenses per page you want

            // Check if any expenses found
            if ($expenses->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'expenses' => $expenses->items(),
                'pagination' => [
                    'total' => $expenses->total(),
                    'per_page' => $expenses->perPage(),
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                    'from' => $expenses->firstItem(),
                    'to' => $expenses->lastItem(),
                ],
            ], 200);

        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve expenses: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve expenses',
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
            'amount' => 'required|numeric',
            'tax_deductible' => 'boolean',
            'purpose' => 'string|nullable',
            'contacts' => 'integer|nullable|exists:jo_crmentity,crmid',
            'categories' => 'integer|nullable|exists:jo_manage_categories,id',
            'date' => 'date|nullable',
            'vendor' => 'integer|nullable|exists:jo_vendors,id',
            'projects' => 'array|nullable',
            'projects.*' => 'exists:jo_projects,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'select_status' => 'string|nullable',
            'notes' => 'string|nullable',
            'include_taxes' => 'array|nullable',
            'attach_a_receipt' => 'file|nullable',
        ]);

        // // Convert IDs to names where necessary
        // if (isset($validatedData['employees_that_generate'])) {
        //     $employee = DB::table('jo_employees')->where('id', $validatedData['employees_that_generate'])->value('first_name');
        //     $validatedData['employees_that_generate'] = $employee;
        // }

        // if (isset($validatedData['categories'])) {
        //     $category = DB::table('jo_manage_categories')->where('id', $validatedData['categories'])->value('expense_name');
        //     $validatedData['categories'] = $category;
        // }

        // if (isset($validatedData['vendor'])) {
        //     $vendor = DB::table('jo_vendors')->where('id', $validatedData['vendor'])->value('vendor_name');
        //     $validatedData['vendor'] = $vendor;
        // }

        // // Handle contact (clients, customers, leads)
        // if (isset($validatedData['contacts'])) {
        //     $contactType = null;
        //     $contact = null;

        //     // Check if it's a client
        //     $client = DB::table('jo_clients')->where('id', $validatedData['contacts'])->first(['name']);
        //     if ($client) {
        //         $contactType = 'client';
        //         $contact = $client->name;
        //     }

        //     // Check if it's a customer
        //     if (!$contact) {
        //         $customer = DB::table('jo_customers')->where('id', $validatedData['contacts'])->first(['name']);
        //         if ($customer) {
        //             $contactType = 'customer';
        //             $contact = $customer->name;
        //         }
        //     }

        //     // Check if it's a lead
        //     if (!$contact) {
        //         $lead = DB::table('jo_leads')->where('id', $validatedData['contacts'])->first(['name']);
        //         if ($lead) {
        //             $contactType = 'lead';
        //             $contact = $lead->name;
        //         }
        //     }

        //     // If none of the above, throw validation error
        //     if (!$contact) {
        //         throw ValidationException::withMessages(['contacts' => "Contact with ID '{$validatedData['contact']}' does not exist in any relevant table"]);
        //     }

        //     // Assign contact based on the type found
        //     $validatedData['contacts'] = $contact;
        // }

        // if (isset($validatedData['project'])) {
        //     $project = DB::table('jo_projects')->whereIn('id', $validatedData['project'])->pluck('project_name');
        //     $validatedData['project'] = $project;
        // }

        // if (isset($validatedData['tags'])) {
        //     $tags = [];

        //     // Validate each tag and retrieve tag names
        //     foreach ($validatedData['tags'] as $tagId) {
        //         $tag = Tags::where('id', $tagId)->first();

        //         // If the tag doesn't exist, throw a validation exception
        //         if (!$tag) {
        //             throw ValidationException::withMessages(['tags' => "Tag with ID '$tagId' does not exist"]);
        //         }

        //         $tags[] = [
        //             'tags_name' => $tag->tags_name,
        //             'tag_color' => $tag->tag_color, // Assuming 'tag_color' exists in your 'jo_tags' table
        //         ];
        //     }

        //     // Convert the tags array to JSON
        //     $validatedData['tags'] = json_encode($tags);
        // }

        if ($request->hasFile('attach_a_receipt')) {
            $attach_a_receipt = $request->file('attach_a_receipt');
            $path = $attach_a_receipt->store('receipts');
            $validatedData['attach_a_receipt'] = $path;
        }

        // Retrieve default values from an existing Crmentity record
        $defaultCrmentity = Crmentity::where('setype', 'Customers')->first();

        // Check if defaultCrmentity exists
        if (!$defaultCrmentity) {
            throw new \Exception('Default Crmentity not found');
        }

        // Create a new Crmentity record with a new crmid
        $newCrmentity = new Crmentity();
        $newCrmentity->crmid = Crmentity::max('crmid') + 1;
        $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid;
        $newCrmentity->smownerid = $defaultCrmentity->smownerid;
        $newCrmentity->setype = 'Expenses';
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
        $newCrmentity->label = $validatedData['amount'];
        $newCrmentity->save();

        // Set the new crmid as the expense ID
        $validatedData['id'] = $newCrmentity->crmid;

        // Create a new expense record with the crmid
        $expense = Expense::create($validatedData);

        DB::commit();

        return response()->json(['message' => 'Expense created successfully', 'expense' => $expense], 201);
    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create expense: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create expense: ' . $e->getMessage()], 500);
    }
}

    

    
   
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $expense = Expense::findOrFail($id);
            return response()->json(['status' => 200, 'expense' => $expense], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 404, 'message' => 'Expense not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve expense details: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to retrieve expense details'], 500);
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
            'amount' => 'required|numeric',
            'tax_deductible' => 'boolean',
            'purpose' => 'string|nullable',
            'contacts' => 'integer|nullable|exists:jo_crmentity,crmid',
            'categories' => 'integer|nullable|exists:jo_manage_categories,id',
            'date' => 'date|nullable',
            'vendors' => 'integer|nullable|exists:jo_vendors,id',
            'projects' => 'array|nullable',
            'projects.*' => 'exists:jo_projects,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'select_status' => 'string|nullable',
            'notes' => 'string|nullable',
            'include_taxes' => 'array|nullable',
            'attach_a_receipt' => 'file|nullable',
        ]);

        // Process the contact array to fetch names based on IDs
        // foreach ($validatedData['contact'] as &$contactItem) {
        //     switch ($contactItem['type']) {
        //         case 'customer':
        //             $customer = DB::table('jo_customers')->where('id', $contactItem['id'])->first(['name']);
        //             if ($customer) {
        //                 $contactItem['name'] = $customer->name;
        //             } else {
        //                 throw ValidationException::withMessages(['contact' => 'Customer with ID '.$contactItem['id'].' not found']);
        //             }
        //             break;
        //         case 'client':
        //             $client = DB::table('jo_clients')->where('id', $contactItem['id'])->first(['clientsname']);
        //             if ($client) {
        //                 $contactItem['name'] = $client->clientsname;
        //             } else {
        //                 throw ValidationException::withMessages(['contact' => 'Client with ID '.$contactItem['id'].' not found']);
        //             }
        //             break;
        //         case 'lead':
        //             $lead = DB::table('jo_leads')->where('id', $contactItem['id'])->first(['name']);
        //             if ($lead) {
        //                 $contactItem['name'] = $lead->name;
        //             } else {
        //                 throw ValidationException::withMessages(['contact' => 'Lead with ID '.$contactItem['id'].' not found']);
        //             }
        //             break;
        //         default:
        //             throw ValidationException::withMessages(['contact' => 'Invalid contact type provided']);
        //     }
        //}

        // Find the expense record by ID
        $expense = Expense::findOrFail($id);

        // Update the expense record
        $expense->update([
            'amount' => $validatedData['amount'],
            'tax_deductible' => $validatedData['tax_deductible'] ?? false,
            'purpose' => $validatedData['purpose'] ?? null,
            'contacts' =>$validatedData['contacts'], // Store entire contact array as JSON
            'categories' => $validatedData['categories'] ?? null,
            'date' => $validatedData['date'] ?? null,
            'vendors' => $validatedData['vendors'] ?? null,
            'projects' => $validatedData['projects'] ?? null,
            'tags' => $validatedData['tags'] ?? null,
            'select_status' => $validatedData['select_status'] ?? null,
            'notes' => $validatedData['notes'] ?? null,
            'include_taxes' => $validatedData['include_taxes'] ?? null,
            'attach_a_receipt' => $validatedData['attach_a_receipt'] ?? null,
        ]);

        // Prepare the response with contact names
        $expenseArray = $expense->toArray();
        // $contactNames = [];
        // foreach ($validatedData['contact'] as $contactItem) {
        //     $contactNames[] = [
        //         'type' => $contactItem['type'],
        //         'name' => $contactItem['name'], // Ensure 'name' exists in $contactItem
        //         //'id' => $contactItem['id'],
        //     ];
        // }
        // $expenseArray['contact'] = $contactNames;

        // Return a success response with the updated expense object
        return response()->json(['message' => 'Expense updated successfully', 'expense' => $expenseArray]);
    } catch (ValidationException $e) {
        // Return validation error response
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        // Log the error
        Log::error('Failed to update expense: ' . $e->getMessage());

        // Return an error response with the actual error message
        return response()->json(['error' => 'Failed to update expense: ' . $e->getMessage()], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $expense->delete();

            return response()->json(['message' => 'Expense deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 404, 'message' => 'Expense not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete expense: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to delete expense'], 500);
        }
    }
    public function search(Request $request)
    {
        Log::info('Search request received', ['params' => $request->all()]);
    
        try {
            // Validate search parameters
            $validatedData = $request->validate([
                'tax_deductible' => 'boolean|nullable',
                'not_tax_deductible' => 'boolean|nullable',
                'billable_to_contact' => 'boolean|nullable',
                'employees_that_generate' => 'string|nullable|exists:jo_employees,first_name',
                'currency' => 'string|nullable',
                'categories' => 'string|nullable|exists:jo_manage_categories,expense_name',
                'date' => 'date|nullable',
                'vendor' => 'string|nullable|exists:jo_vendors,vendor_name',
                'amount' => 'integer|nullable',
                'purpose' => 'string|nullable',
                'contact' => 'string|nullable',
                'project' => 'string|nullable|exists:jo_projects,project_name',
                'tags' => 'string|nullable',
                'select_status' => 'string|nullable',
                'notes' => 'string|nullable',
            ]);
    
            Log::info('Validation successful', ['validatedData' => $validatedData]);
    
            // Build the query based on validated data
            $query = Expense::query();
    
            foreach ($validatedData as $key => $value) {
                if ($value !== null) {
                    Log::info('Adding to query', ['key' => $key, 'value' => $value]);
                    
                    if ($key == 'tags') {
                        $query->whereJsonContains($key, $value);
                    } elseif ($key == 'contact') {
                        $query->where(function ($q) use ($value) {
                            $q->whereIn('contact', function ($subQuery) use ($value) {
                                $subQuery->select('clientsname')
                                    ->from('jo_clients')
                                    ->where('clientsname', $value)
                                    ->union(
                                        DB::table('jo_customers')
                                            ->select('name')
                                            ->where('name', $value)
                                    )
                                    ->union(
                                        DB::table('jo_leads')
                                            ->select('name')
                                            ->where('name', $value)
                                    );
                            });
                        });
                    } else {
                        $query->where($key, $value);
                    }
                }
            }
    
            // Paginate the results
            $expenses = $query->paginate(10);
    
            if ($expenses->isEmpty()) {
                Log::info('No records found');
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            Log::info('Search results found', ['total' => $expenses->total()]);
    
            return response()->json([
                'status' => 200,
                'expenses' => $expenses->items(),
                'pagination' => [
                    'total' => $expenses->total(),
                    'per_page' => $expenses->perPage(),
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                    'from' => $expenses->firstItem(),
                    'to' => $expenses->lastItem(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->validator->errors()]);
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search expenses', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to search expenses: ' . $e->getMessage()], 500);
        }
    }
}    

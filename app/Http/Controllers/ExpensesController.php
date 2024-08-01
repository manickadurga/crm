<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Expense;
use App\Models\Crmentity;
use App\Models\ManageCategories;
use App\Models\Project;
use App\Models\Vendors;

class ExpensesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $expenses = Expense::select(
                'id',
                'amount',
                'tax_deductible',
                'purpose',
                'vendors',
                'categories',
                'employees_that_generate',
                'projects',
                'date',
                'notes',
                'select_status'
            )->paginate($perPage);
            $formattedExpenses = [];
            foreach ($expenses as $expense) {
                $decodedProjects = is_string($expense->projects) ? json_decode($expense->projects) : $expense->projects;
                $projectNames = [];
                foreach ($decodedProjects as $projectId) {
                    $project = Project::find($projectId);
                    if ($project) {
                        $projectNames[] = $project->project_name;
                    }
                }
                $formattedExpenses[] = [
                    'id' => $expense->id,
                    'amount' => $expense->amount,
                    'tax_deductible' => $expense->tax_deductible,
                    'purpose' => $expense->purpose,
                    'vendors' => $expense->vendors ? Vendors::find($expense->vendors)->vendor_name : null,
                    'categories' => $expense->categories ? ManageCategories::find($expense->categories)->expense_name : null,
                    'employees' => $expense->employees_that_generate,
                    'projects' => $projectNames,
                    'date' => $expense->date,
                    'notes' => $expense->notes,
                    'select_status' => $expense->select_status,
                ];
            }
            return response()->json([
                'status' => 200,
                'expenses' => $formattedExpenses,
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

            // Return error response
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
                'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                    // Check if the contact ID exists in any of the specified tables
                    $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                    $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                    $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();
    
                    if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                        $fail("The selected contact ID does not exist in any of the specified tables.");
                    }
                }],
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
                'attach_a_receipt' => 'file|nullable|mimes:jpeg,png,jpg,pdf|max:2048', // Ensure file validation is robust
            ]);
    
            // Handle receipt upload if present
            if ($request->hasFile('attach_a_receipt')) {
                $attach_a_receipt = $request->file('attach_a_receipt');
                $path = $attach_a_receipt->store('receipts', 'public'); // Store in 'public/receipts'
                $validatedData['attach_a_receipt'] = $path;
            }
    
            // Create a new Crmentity entry
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Expenses', $validatedData['amount']);
    
            // Check if Crmentity ID is returned correctly
            if (!$crmid) {
                throw new \Exception('Failed to create Crmentity entry.');
            }
    
            // Create the expense with the Crmentity ID
            $validatedData['id'] = $crmid;
            $expense = Expense::create($validatedData);
    
            // Commit the transaction to save changes
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

    public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'tax_deductible' => 'boolean',
            'purpose' => 'nullable|string',
            'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                // Check if the contact ID exists in any of the specified tables
                $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();

                if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                    $fail("The selected contact ID does not exist in any of the specified tables.");
                }
            }],
            'categories' => 'nullable|integer|exists:jo_manage_categories,id',
            'date' => 'nullable|date',
            'vendors' => 'nullable|integer|exists:jo_vendors,id',
            'projects' => 'nullable|array',
            'projects.*' => 'exists:jo_projects,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'select_status' => 'nullable|string',
            'notes' => 'nullable|string',
            'include_taxes' => 'nullable|array',
            'attach_a_receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048', // Specifying allowed file types and max size
        ]);

        // Find the expense by ID or throw a ModelNotFoundException
        $expense = Expense::findOrFail($id);

        // Handle file upload if a receipt is attached
        if ($request->hasFile('attach_a_receipt')) {
            $receipt = $request->file('attach_a_receipt');
            $receiptName = time() . '_' . $receipt->getClientOriginalName();
            $receiptPath = $receipt->storeAs('receipts', $receiptName, 'public'); // Store the file in the 'public/receipts' directory
            $validatedData['attach_a_receipt'] = $receiptPath;
        }

        // Convert projects and tags arrays to JSON strings for storage
        $validatedData['projects'] = isset($validatedData['projects']) ? json_encode($validatedData['projects']) : null;
        $validatedData['tags'] = isset($validatedData['tags']) ? json_encode($validatedData['tags']) : null;
        $validatedData['include_taxes'] = isset($validatedData['include_taxes']) ? json_encode($validatedData['include_taxes']) : null;

        // Update the expense fields based on the request data
        $expense->update($validatedData);

        // Update the corresponding Crmentity record
        $crmentity = Crmentity::where('crmid', $id)->where('setype', 'Expenses')->first();
        
        if ($crmentity) {
            $crmentity->update([
                'label' => $validatedData['amount'] ?? 'Updated Expense',
                'modifiedtime' => now(),
                // Optionally, update other fields like status or description
                //'status' => $validatedData['select_status'] ?? $crmentity->status,
                //'description' => $validatedData['notes'] ?? $crmentity->description,
            ]);
        } else {
            throw new Exception('Crmentity record not found.');
        }

        // Convert projects, tags, and include_taxes back to arrays for response
        $expense->projects = json_decode($expense->projects, true);
        $expense->tags = json_decode($expense->tags, true);
        $expense->include_taxes = json_decode($expense->include_taxes, true);

        // Commit the transaction
        DB::commit();

        // Return a success response with the updated expense and crmentity data
        return response()->json([
            'message' => 'Expense and Crmentity updated successfully',
            'expense' => $expense,
            'crmentity' => $crmentity,
        ]);

    } catch (ModelNotFoundException $e) {
        DB::rollBack();
        return response()->json(['error' => 'Expense not found'], 404);
    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update expense and Crmentity: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update expense and Crmentity: ' . $e->getMessage()], 500);
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

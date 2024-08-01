<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Crmentity;
use App\Models\Tags;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Set the number of items per page, default is 10
            $perPage = $request->input('per_page', 10);

            // Get paginated incomes with specific fields including 'id'
            $incomes = Income::select('id', 'Employees that generate income', 'Contact', 'pick_date',  'tags')
                ->paginate($perPage);

            // Return JSON response with incomes and pagination information
            return response()->json([
                'status' => 200,
                'incomes' => $incomes->items(),
                'pagination' => [
                    'total' => $incomes->total(),
                    'per_page' => $incomes->perPage(),
                    'current_page' => $incomes->currentPage(),
                    'last_page' => $incomes->lastPage(),
                    'from' => $incomes->firstItem(),
                    'to' => $incomes->lastItem(),
                ],
            ], 200);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to retrieve incomes: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve incomes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage for clients.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // Validate the incoming request data
            $validatedData = Validator::make($request->all(), [
                'Employees that generate income' => 'required|exists:jo_manage_employees,id',  // Employee ID
                'Contact' => ['required', 'integer', function ($attribute, $value, $fail) {
                    // Check if the contact ID exists in any of the specified tables
                    $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                    $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                    $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();
    
                    if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                        $fail("The selected contact ID does not exist in any of the specified tables.");
                    }
                }],// Contact ID  // Contact ID
                'pick_date' => 'nullable|date',
                'currency' => 'nullable|string',
                'amount' => 'required|integer',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id', // Ensure tags exist in jo_tags table
                'choose' => 'nullable|in:Bonus',
                'description' => 'nullable|string',
            ])->validate();
    
            // Prepare the income data for insertion
            $incomeData = [
                'Employees that generate income' => $validatedData['Employees that generate income'],
                'Contact' => $validatedData['Contact'],
                'pick_date' => $validatedData['pick_date'] ?? null,
                'currency' => $validatedData['currency'] ?? null,
                'amount' => $validatedData['amount'],
                'tags' => isset($validatedData['tags']) ? json_encode($validatedData['tags']) : null,  // Handle optional tags field
                'choose' => $validatedData['choose'] ?? null,
                'description' => $validatedData['description'] ?? null,
            ];
    
            // Create Crmentity record via CrmentityController
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Incomes', $validatedData['Employees that generate income']);
    
            // Set crmid in the income data
            $incomeData['id'] = $crmid;
    
            // Create the income record with the crmid
            $income = Income::create($incomeData);
    
            DB::commit();
    
            // Return success response
            return response()->json([
                'message' => 'Income created successfully',
                'income' => $income,
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            // Handle any exceptions or errors
            return response()->json([
                'error' => 'Failed to create income',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $income = Income::findOrFail($id);
            return response()->json($income, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Income not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage for clients.
     */
    public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'Employees that generate income' => 'nullable|integer|exists:jo_manage_employees,id',  // Employee ID
            'Contact' => ['required', 'integer', function ($attribute, $value, $fail) {
                // Check if the contact ID exists in any of the specified tables
                $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();

                if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                    $fail("The selected contact ID does not exist in any of the specified tables.");
                }
            }],
            'pick_date' => 'nullable|date',
            'currency' => 'nullable|string',
            'amount' => 'nullable|integer',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id', // Ensure tags exist in jo_tags table
            'choose' => 'nullable|in:Bonus',
            'description' => 'nullable|string',
        ]);

        // Fetch the existing Income record
        $income = Income::findOrFail($id);

        // Update the Income record
        $income->update([
            'Employees that generate income' => $validatedData['Employees that generate income'] ?? null,
            'Contact' => $validatedData['Contact'] ?? null,
            'pick_date' => $validatedData['pick_date'] ?? $income->pick_date,
            'currency' => $validatedData['currency'] ?? $income->currency,
            'amount' => $validatedData['amount'] ?? $income->amount,
            'tags' => json_encode($validatedData['tags'] ?? $income->tags),
            'choose' => $validatedData['choose'] ?? $income->choose,
            'description' => $validatedData['description'] ?? $income->description,
        ]);

        // Fetch the corresponding Crmentity record
        $crmentity = Crmentity::where('crmid', $id)->where('setype', 'Incomes')->first();

        if ($crmentity) {
            // Update the Crmentity record
            $crmentity->update([
                'label' => $validatedData['description'] ?? $crmentity->label, // Update the label or use an appropriate field
                'modifiedtime' => now(),
            ]);
        } else {
            // Handle case where Crmentity record does not exist
            throw new \Exception('Crmentity record not found.');
        }

        DB::commit();

        // Decode tags back to array for response
        $income->tags = json_decode($income->tags);

        return response()->json([
            'message' => 'Income and Crmentity updated successfully',
            'income' => $income,
            'crmentity' => $crmentity,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update income: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to update income', 'error' => $e->getMessage()], 500);
    }
}
    public function destroy($id)
    {
        try {
            $income = Income::findOrFail($id);
            $income->delete();
            return response()->json(['message' => 'Income deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete income', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search incomes based on query.
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q');

            // Perform search using 'like' operator on relevant columns
            $incomes = Income::where('Employees that generate income', 'like', "%$query%")
                ->orWhere('Contact', 'like', "%$query%")
                ->orWhere('pick_date', 'like', "%$query%")
                ->orWhere('currency', 'like', "%$query%")
                ->orWhere('amount', 'like', "%$query%")
                ->orWhere('tags', 'like', "%$query%")
                ->orWhere('choose', 'like', "%$query%")
                ->orWhere('description', 'like', "%$query%")
                ->get();

            return response()->json(['data' => $incomes], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to perform search', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the contact name by ID based on the determined table.
     */
    private function getContactNameById($contactId, $tableName)
    {
        return DB::table($tableName)->where('id', $contactId)->value('name');
    }

    /**
     * Get the employee first name by ID from the jo_manage_employees table.
     */
    private function getEmployeeFirstNameById($employeeId)
    {
        return DB::table('jo_manage_employees')->where('id', $employeeId)->value('first_name');
    }

    /**
     * Determine the table name based on the contact ID.
     */
    private function getTableNameByContactId($contactId)
    {
        // Implement logic to determine the table name based on the contact ID
        // This can involve checking the ranges or specific IDs to determine the corresponding table

        // Example logic (you can adjust based on your actual logic):
        if ($contactId >= 1000 && $contactId < 2000) {
            return 'jo_clients';
        } elseif ($contactId >= 2000 && $contactId < 3000) {
            return 'jo_leads';
        } elseif ($contactId >= 3000 && $contactId < 4000) {
            return 'jo_customers';
        } else {
            return null;
        }
    }
}

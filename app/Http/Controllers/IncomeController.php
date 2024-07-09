<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Crmentity;
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
                'Employees that generate income' => 'required|integer',  // Employee ID
                'Contact' => 'required|integer',  // Contact ID
                'pick_date' => 'nullable|date',
                'currency' => 'nullable|string',
                'amount' => 'required|integer',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id', // Ensure tags exist in jo_tags table
                'choose' => 'nullable|in:Bonus',
                'description' => 'nullable|string',
            ])->validate();
    
            // Ensure 'tags' is stored as JSON
            if (isset($validatedData['tags'])) {
                $tags = [];
                foreach ($validatedData['tags'] as $id) {
                    $tag = Tags::find($id);
                    if ($tag) {
                        $tags[] = [
                            'tags_name' => $tag->tags_name,
                            'tag_color' => $tag->tag_color,
                        ];
                    } else {
                        throw new \Exception("Tag with ID '$id' not found");
                    }
                }
                $validatedData['tags'] = json_encode($tags);
            }
    
            // Retrieve default values from an existing Crmentity record for Incomes
            $defaultCrmentity = Crmentity::where('setype', 'Incomes')->first();
    
            // Check if defaultCrmentity exists
            if (!$defaultCrmentity) {
                throw new \Exception('Default Crmentity not found');
            }
    
            // Create a new Crmentity record with a new crmid
            $newCrmentity = new Crmentity();
            $newCrmentity->crmid = Crmentity::max('crmid') + 1;
            $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid;
            $newCrmentity->smownerid = $defaultCrmentity->smownerid;
            $newCrmentity->setype = 'Incomes';
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
            $newCrmentity->label = 'Income Record'; // Set a label for the income record
            $newCrmentity->save();
    
            // Set the new crmid for the income
            $incomeData = [
                'crmid' => $newCrmentity->crmid,
                'employee_id' => $validatedData['Employees that generate income'],
                'contact_id' => $validatedData['Contact'],
                'pick_date' => $validatedData['pick_date'],
                'currency' => $validatedData['currency'],
                'amount' => $validatedData['amount'],
                'tags' => $validatedData['tags'],
                'choose' => $validatedData['choose'],
                'description' => $validatedData['description'],
            ];
    
            // Create a new income record
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
        try {
            $validatedData = $request->validate([
                'Employees that generate income' => 'required|integer',  // Employee ID
                'Contact' => 'required|integer',  // Contact ID
                'pick_date' => 'nullable|date',
                'currency' => 'nullable|string',
                'amount' => 'required|integer',
                'tags.*' => 'exists:jo_tags,id', // Ensure tags exist in jo_tags table
                'choose' => 'nullable|in:Bonus',
                'description' => 'nullable|string',
            ]);

            // Handle updating income details
            $response = $this->handleUpdate($validatedData, $id);

            return response()->json($response['response'], $response['status']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to update income: ' . $e->getMessage());

            return response()->json(['message' => 'Failed to update income', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle updating an income record.
     */
    private function handleUpdate(array $validatedData, $id)
    {
        // Determine the table based on the Contact ID provided
        $contactId = $validatedData['Contact'];
        $tableName = $this->getTableNameByContactId($contactId);

        if (!$tableName) {
            return [
                'status' => 400,
                'response' => ['message' => 'Invalid contact ID'],
            ];
        }

        // Fetch the contact name based on the contact ID
        $contactName = $this->getContactNameById($contactId, $tableName);
        if (!$contactName) {
            return [
                'status' => 400,
                'response' => ['message' => 'The selected contact ID is invalid.'],
            ];
        }

        $validatedData['Contact'] = $contactName;  // Store the contact name in the 'Contact' field

        // Fetch and store first_name in 'Employees that generate income'
        $employeeFirstName = $this->getEmployeeFirstNameById($validatedData['Employees that generate income']);
        if (!$employeeFirstName) {
            return [
                'status' => 400,
                'response' => ['message' => 'The selected employee ID is invalid.'],
            ];
        }
        $validatedData['Employees that generate income'] = $employeeFirstName;

        // Fetch tags names from jo_tags table based on provided IDs
        $tagsIds = $validatedData['tags'] ?? [];
        $tagsNames = DB::table('jo_tags')
            ->whereIn('id', $tagsIds)
            ->pluck('tags_names')
            ->toArray();

        // Assign tags names to the 'tags' field
        $validatedData['tags'] = json_encode($tagsNames);

        // Update the income record
        $income = Income::findOrFail($id);
        $income->fill($validatedData);
        $income->save();

        return [
            'status' => 200,
            'response' => ['message' => 'Income updated successfully', 'data' => $income],
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
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

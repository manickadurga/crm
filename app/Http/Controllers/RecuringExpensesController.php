<?php

namespace App\Http\Controllers;

use App\Models\RecuringExpenses;
use App\Models\Crmentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecuringExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve paginated recurring expenses
            $expenses = RecuringExpenses::paginate(10); // Adjust 10 to the number of expenses per page you want

            // Check if any expenses found
            if ($expenses->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            // Return paginated response
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
            Log::error('Failed to retrieve recurring expenses: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve recurring expenses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    DB::beginTransaction(); // Begin transaction

    try {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|exists:jo_manage_categories,id',
            'split_expense' => 'boolean',
            'value' => 'required|numeric',
            'currency' => 'string|max:300',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validatedData = $validator->validated();

        // Create or retrieve the Crmentity record
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Recuring Expenses', $validatedData['value']);

        if (!$crmid) {
            throw new Exception('Failed to create Crmentity');
        }

        // Add crmid to validated data
        $validatedData['id'] = $crmid;

        // Create the recurring expense with validated data
        $expense = RecuringExpenses::create($validatedData);

        DB::commit(); // Commit the transaction

        return response()->json([
            'message' => 'Recurring expense created successfully',
            'expense' => $expense
        ], 201);

    } catch (ValidationException $e) {
        DB::rollBack(); // Roll back the transaction on validation error
        return response()->json(['errors' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        DB::rollBack(); // Roll back the transaction on general error
        Log::error('Failed to create recurring expense: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json(['error' => 'Failed to create recurring expense', 'message' => $e->getMessage()], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $expense = RecuringExpenses::findOrFail($id);
            return response()->json($expense);
        } catch (Exception $e) {
            Log::error('Failed to fetch expense: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch expense'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'category_name' => 'nullable|exists:jo_manage_categories,id',
        'split_expense' => 'boolean',
        'value' => 'nullable|numeric',
        'currency' => 'string|max:300',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    try {
        // Update the RecuringExpenses record
        $expense = RecuringExpenses::findOrFail($id);
        $expense->update($validator->validated());

        // Update or create the corresponding Crmentity record
        $crmentity = Crmentity::where('crmid', $id)->first(); // Adjust the condition based on your schema

        if ($crmentity) {
            // Update existing Crmentity record
            $crmentity->label = $validator->validated()['value'] ?? $crmentity->label; // Example: Update label with value
            $crmentity->description = $validator->validated()['currency'] ?? $crmentity->description; // Example: Update description with currency
            $crmentity->save();
        } else {
            // Optionally create a new Crmentity record if it does not exist
            $crmentity = new Crmentity();
            $crmentity->crmid = $id;
            $crmentity->label = $validator->validated()['value'] ?? '';
            $crmentity->description = $validator->validated()['currency'] ?? '';
            $crmentity->save();
        }

        return response()->json([
            'message' => 'Expense and Crmentity updated successfully',
            'expense' => $expense,
            'crmentity' => $crmentity,
        ], 200);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        Log::error('Failed to update expense or Crmentity: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update expense or Crmentity'], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $expense = RecuringExpenses::findOrFail($id);
            $expense->delete();
            return response()->json(['message' => 'Expense deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete expense: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete expense'], 500);
        }
    }
}

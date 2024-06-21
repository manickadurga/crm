<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Expense;
use App\Models\Tags;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $expenses = Expense::all();
            if ($expenses->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'expenses' => $expenses,
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'tax_deductible' => 'boolean',
            'not_tax_deductible' => 'boolean',
            'billable_to_contact' => 'boolean',
            'employees_that_generate' => 'string|nullable|exists:jo_employees,first_name',
            'currency' => 'string|nullable',
            'categories'=>'string|nullable|exists:jo_manage_categories,expense_name',
            'date' => 'date|nullable',
            'vendor' => 'string|nullable|exists:jo_vendors,vendor_name',
            'amount' => 'integer|required',
            'purpose' => 'string|nullable',
            'contact' => [
                function ($attribute, $value, $fail) {
                    // Check if the value exists in any of the specified tables
                    if (!DB::table('jo_clients')->where('clientsname', $value)->exists() &&
                        !DB::table('jo_customers')->where('name', $value)->exists() &&
                        !DB::table('jo_leads')->where('name', $value)->exists()) {
                        $fail("The $attribute must exist in 'jo_clients', 'jo_customers', or 'jo_leads' table.");
                    }
                }
            ],
            'project' => 'array|nullable',
            'project.*'=>'exists:jo_projects,project_name',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'select_status' => 'string|nullable',
            'notes' => 'string|nullable',
            'include_taxes' => 'array|nullable',
            'attach_a_receipt' => 'binary|nullable',
        ]);

        if (isset($validatedData['tags'])) {
            $tags = [];

            // Validate each tag
            foreach ($validatedData['tags'] as $tagName) {
                $tag = Tags::where('tags_name', $tagName)->first();

                // If the tag doesn't exist, throw a validation exception
                if (!$tag) {
                    throw ValidationException::withMessages(['tags' => "Tag '$tagName' does not exist in the 'jo_tags' table"]);
                }

                $tags[] = $tag->tags_name;
            }

            // Convert the tags array to JSON
            $validatedData['tags'] = json_encode($tags);
        }

        if ($request->hasFile('attach_a_receipt')) {
            $attach_a_receipt = $request->file('attach_a_receipt');
            $path = $attach_a_receipt->store('receipts');
            $validatedData['attach_a_receipt'] = $path;
        }

        // Create a new expense record in the database
        Expense::create($validatedData);

        // Return a success response
        return response()->json(['message' => 'Expense created successfully']);
    } catch (ValidationException $e) {
        // Return validation error response
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        // Log the error
        Log::error('Failed to create expense: ' . $e->getMessage());

        // Return an error response with the actual error message
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
    public function update(Request $request, string $id)
{
    try {
        $expense = Expense::findOrFail($id);

        // Validate the incoming request data
        $validatedData = $request->validate([
            'tax_deductible' => 'boolean',
            'not_tax_deductible' => 'boolean',
            'billable_to_contact' => 'boolean',
            'employees_that_generate' => 'string|nullable|exists:jo_employees,first_name',
            'currency' => 'string|nullable',
            'categories'=>'string|nullable|exists:jo_manage_categories,expense_name',
            'date' => 'date|nullable',
            'vendor' => 'string|nullable|exists:jo_vendors,vendor_name',
            'amount' => 'integer|required',
            'purpose' => 'string|nullable',
            'contact' => [
                function ($attribute, $value, $fail) {
                    // Check if the value exists in any of the specified tables
                    if (!DB::table('jo_clients')->where('clientsname', $value)->exists() &&
                        !DB::table('jo_customers')->where('name', $value)->exists() &&
                        !DB::table('jo_leads')->where('name', $value)->exists()) {
                        $fail("The $attribute must exist in 'jo_clients', 'jo_customers', or 'jo_leads' table.");
                    }
                }
            ],
            'project' => 'array|nullable',
            'project.*'=>'exists:jo_projects,project_name',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'select_status' => 'string|nullable',
            'notes' => 'string|nullable',
            'include_taxes' => 'array|nullable',
            'attach_a_receipt' => 'binary|nullable',
        ]);
        if ($request->hasFile('attach_a_receipt')) {
            $attach_a_receipt = $request->file('attach_a_receipt');
            $path = $attach_a_receipt->store('receipts');
            $validatedData['attach_a_receipt'] = $path;
        }

        // Update the expense record in the database
        $expense->update($validatedData);

        // Return a success response
        return response()->json(['message' => 'Expense updated successfully']);
    } catch (ModelNotFoundException $e) {
        return response()->json(['status' => 404, 'message' => 'Expense not found'], 404);
    } catch (ValidationException $e) {
        // Return validation error response
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
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
}

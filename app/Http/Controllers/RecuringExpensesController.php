<?php

namespace App\Http\Controllers;

use App\Models\RecuringExpenses;
use App\Models\Crmentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;

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

        try {
            // Retrieve the category name based on the provided ID
            // $categoryId = $request->input('category_name');
            // $category = DB::table('jo_manage_categories')->where('id', $categoryId)->value('expense_name');

            // if (!$category) {
            //     throw new \Exception("Category with ID '$categoryId' not found");
            // }

            // Create the recurring expense with validated data
            $expense = RecuringExpenses::create([
                'category_name' => $request->input('category_name'),
                'split_expense' => $request->input('split_expense'),
                'value' => $request->input('value'),
                'currency' => $request->input('currency'),
            ]);

            // Create or retrieve the Crmentity record for RecuringExpenses
            $defaultCrmentity = Crmentity::where('setype', 'RecuringExpenses')->first();

            if (!$defaultCrmentity) {
                // Create a default Crmentity if it doesn't exist
                $defaultCrmentity = Crmentity::create([
                    'crmid' => Crmentity::max('crmid') + 1,
                    'smcreatorid' => 0, // Replace with appropriate default
                    'smownerid' => 0, // Replace with appropriate default
                    'setype' => 'RecuringExpenses',
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
                    'label' => $request->input('category_name'),
                ]);

                if (!$defaultCrmentity) {
                    throw new \Exception('Failed to create default Crmentity for RecuringExpenses');
                }
            }

            // Create a new Crmentity record with a new crmid
            $newCrmentity = new Crmentity();
            $newCrmentity->crmid = Crmentity::max('crmid') + 1;
            $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid ?? 0; // Replace with appropriate default
            $newCrmentity->smownerid = $defaultCrmentity->smownerid ?? 0; // Replace with appropriate default
            $newCrmentity->setype = 'RecuringExpenses';
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
            $newCrmentity->label = $request->input('category_name'); // Adjust as per your requirement
            $newCrmentity->save();

            // Associate the Crmentity record with the expense
            $expense->id = $newCrmentity->crmid;
            $expense->save();

            // Return a success response with the created expense object
            return response()->json(['message' => 'Recurring expense created successfully', 'expense' => $expense], 201);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to create recurring expense: ' . $e->getMessage());

            // Return an error response with the actual error message
            return response()->json(['error' => 'Failed to create recurring expense: ' . $e->getMessage()], 500);
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
            $expense = RecuringExpenses::findOrFail($id);
            $expense->update($validator->validated());
            return response()->json($expense);
        } catch (Exception $e) {
            Log::error('Failed to update expense: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update expense'], 500);
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

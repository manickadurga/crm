<?php

namespace App\Http\Controllers;

use App\Models\RecuringExpenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class RecuringExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $expenses = RecuringExpenses::all();
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
        $validator = Validator::make($request->all(), [
            'category_name' => 'string|required|exits:jo_manage_categories,expense_name',
            'split_expense' => 'boolean',
            'value' => 'required|numeric',
            'currency' => 'string|max:300',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $expense = RecuringExpenses::create($validator->validated());
            return response()->json($expense, 201);
        } catch (Exception $e) {
            Log::error('Failed to create expense: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create expense'], 500);
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
            'category_name' => 'string|required|exits:jo_manage_categories,expense_name',
            'split_expense' => 'boolean',
            'value' => 'required|numeric',
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

<?php

namespace App\Http\Controllers;

use App\Models\ManageCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ManageCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = ManageCategories::all();
            if ($categories->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'categories' => $categories,
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve categories: ' . $e->getMessage());
    
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve categories',
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
            'expense_name' => 'required|string|max:255',
            'tags' => 'nullable|array|max:5000',
            'orgid' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $category = ManageCategories::create($validator->validated());
            return response()->json($category, 201);
        } catch (Exception $e) {
            Log::error('Failed to create category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to create category'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $category = ManageCategories::findOrFail($id);
            return response()->json($category);
        } catch (Exception $e) {
            Log::error('Failed to fetch category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to fetch category'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'expense_name' => 'required|string|max:255',
            'tags' => 'nullable|array|max:5000',
            'orgid' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $category = ManageCategories::findOrFail($id);
            $category->update($validator->validated());
            return response()->json($category);
        } catch (Exception $e) {
            Log::error('Failed to update category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to update category'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $category = ManageCategories::findOrFail($id);
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to delete category'], 500);
        }
    }
}

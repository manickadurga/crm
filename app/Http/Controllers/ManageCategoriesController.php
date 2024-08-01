<?php

namespace App\Http\Controllers;

use App\Models\ManageCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Tags;

class ManageCategoriesController extends Controller
{
    public function index()
    {
        try {
            $categories = ManageCategories::paginate(10); 
            if ($categories->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'categories' => $categories->items(),
                'pagination' => [
                    'total' => $categories->total(),
                    'per_page' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
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
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'expense_name' => 'required|string|max:255',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $data = $validator->validated();
            $category = ManageCategories::create($data);
            return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to create category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create category: ' . $e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        try {
            $category = ManageCategories::findOrFail($id);
            return response()->json(['category' => $category], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve category: ' . $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'expense_name' => 'required|string|max:255',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $data = $validator->validated();
            $category = ManageCategories::findOrFail($id);
            $category->update($data);

            return response()->json(['message' => 'Category updated successfully', 'category' => $category], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update category: ' . $e->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $category = ManageCategories::findOrFail($id);
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete category: ' . $e->getMessage()], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'expense_name' => 'nullable|string',
                'tags' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);

            // Initialize the query builder
            $query = ManageCategories::query();

            // Apply search filters
            if (isset($validatedData['expense_name'])) {
                $query->where('expense_name', 'like', '%' . $validatedData['expense_name'] . '%');
            }

            if (isset($validatedData['tags'])) {
                $query->where('tags', 'like', '%' . $validatedData['tags'] . '%');
            }

            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // default per_page value
            $categories = $query->paginate($perPage);

            // Check if any categories found
            if ($categories->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'categories' => $categories->items(),
                'pagination' => [
                    'total' => $categories->total(),
                    'per_page' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search categories: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search categories: ' . $e->getMessage()], 500);
        }
    }

}

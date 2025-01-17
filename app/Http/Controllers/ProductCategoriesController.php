<?php

namespace App\Http\Controllers;

use App\Models\ProductCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ProductCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve paginated product categories
            $categories = ProductCategories::paginate(10); // Adjust 10 to the number of categories per page you want

            // Check if any categories found
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
            // Log the error
            Log::error('Failed to retrieve product categories: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve product categories',
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
            'image' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $productCategory = ProductCategories::create($validator->validated());
            return response()->json($productCategory, 201);
        } catch (Exception $e) {
            Log::error('Failed to create product category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create product category'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $productCategory = ProductCategories::findOrFail($id);
            return response()->json($productCategory);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Product category not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to fetch product category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch product category'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $productCategory = ProductCategories::findOrFail($id);
            $productCategory->update($validator->validated());
            return response()->json($productCategory);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Product category not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update product category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update product category'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $productCategory = ProductCategories::findOrFail($id);
            $productCategory->delete();
            return response()->json(['message' => 'Product category deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Product category not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete product category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete product category'], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'name' => 'nullable|string',
                'description' => 'nullable|string',
                'language' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);

            // Initialize the query builder
            $query = ProductCategories::query();

            // Apply search filters
            if (isset($validatedData['name'])) {
                $query->where('name', 'like', '%' . $validatedData['name'] . '%');
            }

            if (isset($validatedData['description'])) {
                $query->where('description', 'like', '%' . $validatedData['description'] . '%');
            }

            if (isset($validatedData['language'])) {
                $query->where('language', 'like', '%' . $validatedData['language'] . '%');
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

        } catch (Exception $e) {
            Log::error('Failed to search product categories: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search product categories'], 500);
        }
    }
}

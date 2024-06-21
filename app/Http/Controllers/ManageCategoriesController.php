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
        try {
            $validator = Validator::make($request->all(), [
                'expense_name' => 'required|string|max:255',
                'tags' => 'nullable|array',
                'tags.*.tags_name' => 'exists:jo_tags,tags_name',
                'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $data = $validator->validated();

            if (isset($data['tags'])) {
                $data['tags'] = json_encode($data['tags']);
            }

            $category = ManageCategories::create($data);

            return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to create category: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'expense_name' => 'required|string|max:255',
                'tags' => 'nullable|array',
                'tags.*.tags_name' => 'exists:jo_tags,tags_name',
                'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $data = $validator->validated();

            if (isset($data['tags'])) {
                $data['tags'] = json_encode($data['tags']);
            }

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

    /**
     * Remove the specified resource from storage.
     */
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
}

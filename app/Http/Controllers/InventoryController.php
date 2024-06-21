<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Tags;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $inventories = Inventory::all();
            if ($inventories->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'inventories' => $inventories,
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve inventories: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve inventories',
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
            'image' => 'nullable|string',
            'name' => 'required|string',
            'code' => 'required|string',
            'product_type' => 'required|string|exists:jo_product_types,name',
            'product_category' => 'required|string|exists:jo_product_categories,name',
            'description' => 'nullable|string',
            'enabled' => 'boolean',
            'options' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'add_variants' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();
        $inventory = Inventory::create($data);

        return response()->json(['message' => 'Inventory created successfully', 'inventory' => $inventory], 201);
    } catch (ValidationException $e) {
        // Return validation error response
        return response()->json(['errors' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        Log::error('Failed to create inventory: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create inventory: ' . $e->getMessage()], 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $inventory = Inventory::findOrFail($id);
            return response()->json(['inventory' => $inventory], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Inventory not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve inventory: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve inventory: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|string',
                'name' => 'required|string',
                'code' => 'required|string',
                'product_type' => 'required|string|exists:jo_product_types,name',
                'product_category' => 'required|string|exists:jo_product_categories,name',
                'description' => 'nullable|string',
                'enabled' => 'boolean',
                'options' => 'nullable|array',
                'tags' => 'nullable|array',
                'tags.*.tags_name' => 'exists:jo_tags,tags_name',
                'tags.*.tag_color' => 'exists:jo_tags,tag_color',
                'add_variants' => 'nullable|array',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
    
            $data = $validator->validated();
    
            if (isset($data['tags'])) {
                $tags = [];
    
                foreach ($data['tags'] as $tagName) {
                    // Check if the tag exists in the Tags model
                    $tag = Tags::where('tags_name', $tagName)->first();
    
                    if ($tag) {
                        // If the tag exists, add it to the array of tags
                        $tags[] = $tag->tags_name;
                    } else {
                        // If the tag doesn't exist, throw a validation exception
                        throw ValidationException::withMessages(['tags' => "Tag '$tagName' does not exist in the 'jo_tags' table"]);
                    }
                }
    
                // Convert the tags array to JSON
                $data['tags'] = json_encode($tags);
            }
    
            $inventory = Inventory::findOrFail($id);
            $inventory->update($data);
    
            return response()->json(['message' => 'Inventory updated successfully', 'inventory' => $inventory], 200);
        } catch (ValidationException $e) {
            // Return validation error response
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Inventory not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update inventory: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update inventory: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();
            return response()->json(['message' => 'Inventory deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Inventory not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete inventory: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete inventory: ' . $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

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
                'product_type' => 'required|string',
                'product_category' => 'required|string',
                'description' => 'nullable|string',
                'enabled' => 'boolean',
                'options' => 'nullable|array',
                'tags' => 'nullable|array',
                'add_variants' => 'nullable|array',
                'orgid'=>'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $inventory = Inventory::create($request->all());

            return response()->json(['message' => 'Inventory created successfully', 'inventory' => $inventory], 201);
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
                'name' => 'string|nullable',
                'code' => 'string|nullable',
                'product_type' => 'string|nullable',
                'product_category' => 'string|nullable',
                'description' => 'string|nullable',
                'enabled' => 'boolean|nullable',
                'options' => 'array|nullable',
                'tags' => 'array|nullable',
                'add_variants' => 'array|nullable',
                'orgid'=>'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $inventory = Inventory::findOrFail($id);
            $inventory->update($request->all());

            return response()->json(['message' => 'Inventory updated successfully', 'inventory' => $inventory], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 400);
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

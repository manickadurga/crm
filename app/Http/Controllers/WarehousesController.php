<?php

namespace App\Http\Controllers;

use App\Models\Warehouses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class WarehousesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $warehouses = Warehouses::all();
            if ($warehouses->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'customers' => $warehouses,
            ], 200);
        } catch (Exception $e) {
            
            // Log the error
            Log::error('Failed to retrieve customers: ' . $e->getMessage());
    
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve customers',
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
            'name' => 'required|string|max:255',
            'tags' => 'nullable|array',
            'code' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'active' => 'nullable|boolean',
            'description' => 'nullable|string',
            'location' => 'nullable|array',
            'warehouses'=>'nullable|string',
            'orgid'=>'integer|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $warehouse = Warehouses::create($validator->validated());
            return response()->json($warehouse, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create warehouse: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create warehouse'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $warehouse = Warehouses::findOrFail($id);
            return response()->json($warehouse);
        } catch (\Exception $e) {
            Log::error('Failed to fetch warehouse: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch warehouse'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'tags' => 'nullable|array',
            'code' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'active' => 'nullable|boolean',
            'description' => 'nullable|string',
            'location' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $warehouse = Warehouses::findOrFail($id);
            $warehouse->update($validator->validated());
            return response()->json($warehouse);
        } catch (\Exception $e) {
            Log::error('Failed to update warehouse: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update warehouse'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $warehouse = Warehouses::findOrFail($id);
            $warehouse->delete();
            return response()->json(['message' => 'Warehouse deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete warehouse: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete warehouse'], 500);
        }
    }
}

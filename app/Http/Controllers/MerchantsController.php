<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\Merchants;

class MerchantsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $merchants = Merchants::all();
            if ($merchants->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'customers' => $merchants,
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
            'image' => 'nullable|string',
            'name' => 'required|string',
            'code' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'currency' => 'nullable|string',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'website' => 'nullable|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'is_active' => 'boolean',
            'location' => 'nullable|array',
            'warehouses' => 'nullable|string',
            'orgid' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $merchant = Merchants::create($validator->validated());
            return response()->json($merchant, 201);
        } catch (Exception $e) {
            Log::error('Failed to create merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create merchant'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $merchant = Merchants::findOrFail($id);
            return response()->json($merchant, 200);
        } catch (Exception $e) {
            Log::error('Failed to fetch merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Merchant not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'string|nullable',
            'name' => 'string|nullable',
            'code' => 'string|nullable',
            'email' => 'email|nullable',
            'phone' => 'string|nullable',
            'currency' => 'string|nullable',
            'fax' => 'string|nullable',
            'fiscal_information' => 'string|nullable',
            'website' => 'string|nullable',
            'description' => 'string|nullable',
            'tags' => 'array|nullable',
            'is_active' => 'boolean|nullable',
            'location' => 'array|nullable',
            'warehouses' => 'string|nullable',
            'orgid' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $merchant = Merchants::findOrFail($id);
            $merchant->update($validator->validated());
            return response()->json($merchant, 200);
        } catch (Exception $e) {
            Log::error('Failed to update merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update merchant'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $merchant = Merchants::findOrFail($id);
            $merchant->delete();
            return response()->json(['message' => 'Merchant deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error('Failed to delete merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete merchant'], 500);
        }
    }
}

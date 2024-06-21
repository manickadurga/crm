<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Merchants;
use App\Models\Tags;

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
                'merchants' => $merchants,
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve merchants: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve merchants',
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
            'name' => 'nullable|string',
            'code' => 'nullable|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'currency' => 'nullable|string',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'website' => 'nullable|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'is_active' => 'boolean',
            'location' => 'nullable|array',
            'warehouses' => 'nullable|string|exists:jo_warehouses,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        if (isset($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        try {
            $merchant = Merchants::create($data);
            return response()->json(['message' => 'Merchant created successfully', 'merchant' => $merchant], 201);
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
            return response()->json(['merchant' => $merchant], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Merchant not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to fetch merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch merchant: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|string',
            'name' => 'nullable|string',
            'code' => 'nullable|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'currency' => 'nullable|string',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'website' => 'nullable|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'is_active' => 'boolean',
            'location' => 'nullable|array',
            'warehouses' => 'nullable|string|exists:jo_warehouses,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        if (isset($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        try {
            $merchant = Merchants::findOrFail($id);
            $merchant->update($data);
            return response()->json(['message' => 'Merchant updated successfully', 'merchant' => $merchant], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Merchant not found'], 404);
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
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Merchant not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete merchant'], 500);
        }
    }
}

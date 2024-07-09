<?php

namespace App\Http\Controllers;

use App\Models\ProductTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProductTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $types = ProductTypes::all();
            if ($types->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'customers' => $types,
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
            'language' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'orgid' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $productType = ProductTypes::create($validator->validated());
            return response()->json($productType, 201);
        } catch (Exception $e) {
            Log::error('Failed to create product type: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create product type'], 500);
        }
    }
}
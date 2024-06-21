<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Pipelines;
use Exception;
class PipelinesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $pipelines = Pipelines::all();
            if ($pipelines->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'customers' => $pipelines,
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'stages' => 'nullable|array|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $pipeline = Pipelines::create($validator->validated());
            return response()->json($pipeline, 201);
        } catch (Exception $e) {
            Log::error('Failed to create pipeline: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create pipeline'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $pipeline = Pipelines::findOrFail($id);
            return response()->json($pipeline);
        } catch (Exception $e) {
            Log::error('Failed to retrieve pipeline: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve pipeline'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $pipeline = Pipelines::findOrFail($id);
            $pipeline->update($validator->validated());
            return response()->json($pipeline);
        } catch (Exception $e) {
            Log::error('Failed to update pipeline: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update pipeline'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $pipeline = Pipelines::findOrFail($id);
            $pipeline->delete();
            return response()->json(['message' => 'Pipeline deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete pipeline: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete pipeline'], 500);
        }
    }
}

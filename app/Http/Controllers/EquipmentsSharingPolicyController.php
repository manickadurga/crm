<?php

namespace App\Http\Controllers;

use App\Models\EquipmentsSharingPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class EquipmentsSharingPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $policies = EquipmentsSharingPolicy::all();
            if ($policies->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'customers' => $policies,
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
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'required_fields' => ['name', 'description', 'orgid']], 400);
        }

        try {
            $policy = EquipmentsSharingPolicy::create($validator->validated());
            return response()->json($policy, 201);
        } catch (Exception $e) {
            Log::error('Failed to create equipment sharing policy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create equipment sharing policy'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $policy = EquipmentsSharingPolicy::findOrFail($id);
            return response()->json($policy);
        } catch (Exception $e) {
            Log::error('Failed to fetch equipment sharing policy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch equipment sharing policy'], 500);
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'required_fields' => ['name', 'description', 'orgid']], 400);
        }

        try {
            $policy = EquipmentsSharingPolicy::findOrFail($id);
            $policy->update($validator->validated());
            return response()->json($policy);
        } catch (Exception $e) {
            Log::error('Failed to update equipment sharing policy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update equipment sharing policy'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $policy = EquipmentsSharingPolicy::findOrFail($id);
            $policy->delete();
            return response()->json(['message' => 'Equipment sharing policy deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete equipment sharing policy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete equipment sharing policy'], 500);
        }
    }
}

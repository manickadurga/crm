<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EquipmentsSharing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class EquipmentsSharingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $equipmentssharing = EquipmentsSharing::all();
            if ($equipmentssharing->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'customers' => $equipmentssharing,
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
        'name' => 'required|string',
        'select_equipment' => 'required|string',
        'choose_approve_policy' => 'required|string',
        'choice' => 'required|in:employees,teams',
        'add_or_remove_employees' => 'nullable|array',
        'add_or_remove_employees.*' => 'exists:jo_employees,id',
        'select_request_date' => 'nullable|date',
        'select_start_date' => 'required|date',
        'select_end_date' => 'required|date',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    try {
        // Create the equipment sharing entry
        $sharing = EquipmentsSharing::create($request->all());

        // Attach employees to the equipment sharing
        if ($request->has('add_or_remove_employees')) {
            $employeeIds = $request->input('add_or_remove_employees');
            $employees = Employee::whereIn('id', $employeeIds)->get();

            // Attach each found employee to the equipment sharing
            foreach ($employees as $employee) {
                $sharing->employees()->attach($employee->id);
            }
        }

        return response()->json(['data' => $sharing], 201);
    } catch (Exception $e) {
        Log::error('Failed to create equipment sharing: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create equipment sharing'], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $sharing = EquipmentsSharing::findOrFail($id);
            return response()->json(['data' => $sharing], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Equipment sharing not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to fetch equipment sharing: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch equipment sharing'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'select_equipment' => 'required|string',
        'choose_approve_policy' => 'required|string',
        'choice' => 'required|in:employees,teams',
        'add_or_remove_employees' => 'nullable|array',
        'add_or_remove_employees.*' => 'exists:jo_employees,id',
        'select_request_date' => 'nullable|date',
        'select_start_date' => 'required|date',
        'select_end_date' => 'required|date',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    try {
        $sharing = EquipmentsSharing::findOrFail($id);
        $sharing->update($request->all());

        // Sync employees with the equipment sharing
        if ($request->has('add_or_remove_employees')) {
            $employeeIds = $request->input('add_or_remove_employees');
            $employees = Employee::whereIn('id', $employeeIds)->get();

            // Sync employees: attach new, detach removed
            $sharing->employees()->sync($employees->pluck('id')->toArray());
        } else {
            // If no employees are provided, detach all existing relationships
            $sharing->employees()->detach();
        }

        return response()->json(['data' => $sharing], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Equipment sharing not found'], 404);
    } catch (Exception $e) {
        Log::error('Failed to update equipment sharing: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update equipment sharing'], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $sharing = EquipmentsSharing::findOrFail($id);
            $sharing->delete();
            return response()->json(['message' => 'Equipment sharing deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Equipment sharing not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete equipment sharing: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete equipment sharing'], 500);
        }
    }
}

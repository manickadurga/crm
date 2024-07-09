<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EquipmentsSharing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
        // Retrieve paginated equipments sharing entries
        $equipmentssharing = EquipmentsSharing::paginate(10); // Replace 10 with the number of items per page you want

        // Check if any equipment sharing entries found
        if ($equipmentssharing->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No records found',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'equipmentssharing' => $equipmentssharing->items(),
            'pagination' => [
                'total' => $equipmentssharing->total(),
                'per_page' => $equipmentssharing->perPage(),
                'current_page' => $equipmentssharing->currentPage(),
                'last_page' => $equipmentssharing->lastPage(),
                'from' => $equipmentssharing->firstItem(),
                'to' => $equipmentssharing->lastItem(),
            ],
        ], 200);

    } catch (Exception $e) {
        // Log the error
        Log::error('Failed to retrieve equipment sharing entries: ' . $e->getMessage());

        return response()->json([
            'status' => 500,
            'message' => 'Failed to retrieve equipment sharing entries',
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
    
            // // Attach employees' first names to the sharing entry
            // if ($request->has('add_or_remove_employees')) {
            //     $employeeIds = $request->input('add_or_remove_employees');
            //     $employees = Employee::whereIn('id', $employeeIds)->get();
    
            //     $employeeNames = $employees->pluck('first_name')->toArray();
            //     $sharing->add_or_remove_employees = $employeeNames;
            //     $sharing->save();
            // }
    
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
        'name' => 'nullable|string',
        'select_equipment' => 'nullable|string',
        'choose_approve_policy' => 'nullable|string',
        'choice' => 'nullable|in:employees,teams',
        'add_or_remove_employees' => 'nullable|array',
        'add_or_remove_employees.*' => 'exists:jo_employees,id',
        'select_request_date' => 'nullable|date',
        'select_start_date' => 'nullable|date',
        'select_end_date' => 'nullable|date',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    try {
        $sharing = EquipmentsSharing::findOrFail($id);
        $sharing->update($request->all());

        // Sync employees and update their first names
        // if ($request->has('add_or_remove_employees')) {
        //     $employeeIds = $request->input('add_or_remove_employees');
        //     $employees = Employee::whereIn('id', $employeeIds)->get();

        //     $employeeNames = $employees->pluck('first_name')->toArray();
        //     $sharing->add_or_remove_employees = $employeeNames;
        //     $sharing->save();
        // } else {
        //     // If no employees are provided, clear the names
        //     $sharing->add_or_remove_employees = [];
        //     $sharing->save();
        // }

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
    public function search(Request $request)
{
    try {
        // Validate the search input
        $validatedData = $request->validate([
            'name' => 'nullable|string',
            'select_equipment' => 'nullable|string',
            'choose_approve_policy' => 'nullable|string',
            'choice' => 'nullable|in:employees,teams',
            'add_or_remove_employees' => 'nullable|array',
            'add_or_remove_employees.*' => 'exists:jo_employees,id',
            'select_request_date' => 'nullable|date',
            'select_start_date' => 'nullable|date',
            'select_end_date' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1', // Add validation for per_page
        ]);

        // Initialize the query builder
        $query = EquipmentsSharing::query();

        // Apply search filters
        foreach ($validatedData as $key => $value) {
            if ($value !== null && in_array($key, ['name', 'select_equipment', 'choose_approve_policy', 'choice'])) {
                $query->where($key, 'like', '%' . $value . '%');
            }

            if (in_array($key, ['select_request_date', 'select_start_date', 'select_end_date']) && $value !== null) {
                $query->whereDate($key, $value);
            }

            if ($key === 'add_or_remove_employees' && $value !== null) {
                $query->whereHas('employees', function ($q) use ($value) {
                    $q->whereIn('id', $value);
                });
            }
        }

        // Paginate the search results
        $perPage = $validatedData['per_page'] ?? 10; // default per_page value
        $equipmentssharing = $query->paginate($perPage);

        // Check if any equipment sharing entries found
        if ($equipmentssharing->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No matching records found',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'equipmentssharing' => $equipmentssharing->items(),
            'pagination' => [
                'total' => $equipmentssharing->total(),
                'per_page' => $equipmentssharing->perPage(),
                'current_page' => $equipmentssharing->currentPage(),
                'last_page' => $equipmentssharing->lastPage(),
                'from' => $equipmentssharing->firstItem(),
                'to' => $equipmentssharing->lastItem(),
            ],
        ], 200);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        Log::error('Failed to search equipment sharing entries: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to search equipment sharing entries: ' . $e->getMessage()], 500);
    }
}

}

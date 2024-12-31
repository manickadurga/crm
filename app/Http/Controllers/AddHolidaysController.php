<?php

namespace App\Http\Controllers;
use App\Models\AddHolidays;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AddHolidaysController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get pagination parameters from the request or set default to 15 items per page
            $perPage = $request->input('per_page', 15);

            // Fetch paginated time-off requests
            $holidays = AddHolidays::paginate($perPage);

            // Return paginated results
            return response()->json([
                'status' => 200,
                'time_off_requests' => $holidays->items(),
                'pagination' => [
                    'total' => $holidays->total(),
                    'per_page' => $holidays->perPage(),
                    'current_page' => $holidays->currentPage(),
                    'last_page' => $holidays->lastPage(),
                    'from' => $holidays->firstItem(),
                    'to' => $holidays->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching holidays: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching holidays',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
        // Validate the request input
        $validatedData = $request->validate([
            'holiday_name' => 'nullable|string',
            'employee' => 'required|array',
            'employee.*' => 'exists:jo_manage_employees,id',
            'policy' => 'required|in:default_policy',
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        // Create a new time off request
        $holiday = AddHolidays::create($validatedData);

        return response()->json([
            'status' => 201,
            'message' => 'holidays created successfully',
            'time_off_request' => $holiday,
        ], 201);

    } catch (\Exception $e) {
        // Log the error for debugging
       // \Log::error('Error creating time-off request: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 500,
            'message' => 'Error creating holidays',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        $holiday = AddHolidays::findOrFail($id);

        
        return response()->json([
            'status' => 200,
            'add_holiday' => $holiday,
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status' => 404,
            'message' => 'holiday not found',
        ], 404);

    } catch (\Exception $e) {
        // Log the error and return a 500 response for any other exceptions
     //   \Log::error('Error fetching time-off request: ' . $e->getMessage());

        return response()->json([
            'status' => 500,
            'message' => 'Server Error',
        ], 500);
    }
}

public function update(Request $request, $id)
{
    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'holiday_name' => 'nullable|string',
            'employee' => 'nullable|array',
            'employee.*'=>'exists:jo_manage_employees,id',
            'policy' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $holiday = AddHolidays::findOrFail($id);

        $holiday->update($validatedData);

        return response()->json([
            'status' => 200,
            'message' => 'Holiday updated successfully',
            'time_off_request' => $holiday,
        ], 200);

    } catch (\Exception $e) {
        // Log the error for debugging purposes
       // \Log::error('Error updating time-off request: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 500,
            'message' => 'Error updating holiday',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // Find the time-off request by ID or throw a ModelNotFoundException
        $holiday = AddHolidays::findOrFail($id);

        // Delete the time-off request
        $holiday->delete();

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'holiday deleted successfully',
        ], 200);

    } catch (\Exception $e) {
        // Log the error for debugging purposes
     //   \Log::error('Error deleting time-off request: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 500,
            'message' => 'Failed to delete holiday',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}

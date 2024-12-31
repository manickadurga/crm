<?php

namespace App\Http\Controllers;

use App\Models\RequestTimeoff;
use App\Models\Employee; // Assuming you have an Employee model for jo_manage_employee table
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestTimeOffController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get pagination parameters from the request or set default to 15 items per page
            $perPage = $request->input('per_page', 15);

            // Fetch paginated time-off requests
            $timeOffRequests = RequestTimeoff::paginate($perPage);

            // Return paginated results
            return response()->json([
                'status' => 200,
                'time_off_requests' => $timeOffRequests->items(),
                'pagination' => [
                    'total' => $timeOffRequests->total(),
                    'per_page' => $timeOffRequests->perPage(),
                    'current_page' => $timeOffRequests->currentPage(),
                    'last_page' => $timeOffRequests->lastPage(),
                    'from' => $timeOffRequests->firstItem(),
                    'to' => $timeOffRequests->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching time-off requests: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching time-off requests',
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
        // Validate the request input
        $validatedData = $request->validate([
            'employee' => 'required|array',
            'employee.*' => 'exists:jo_manage_employees,id',
            'policy' => 'required|in:default_policy',
            'from' => 'required|date',
            'to' => 'required|date',
            'download_request_form' => 'nullable|in:paid_daysoff,unpaid_daysoff',
            'upload_request_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048', // Adjust the mime types and max size as needed
            'description' => 'nullable|string|max:255'
        ]);


        if ($request->hasFile('upload_request_document')) {
            $document = $request->file('upload_request_document');
            $documentName = time() . '.' . $document->getClientOriginalExtension();
            $document->move(public_path('documents'), $documentName);
            $validatedData['upload_request_document'] = $documentName;
        }

        // Create a new time off request
        $timeOffRequest = RequestTimeoff::create($validatedData);

        return response()->json([
            'status' => 201,
            'message' => 'Time-off request created successfully',
            'time_off_request' => $timeOffRequest,
        ], 201);

    } catch (\Exception $e) {
        // Log the error for debugging
       // \Log::error('Error creating time-off request: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 500,
            'message' => 'Error creating time-off request',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show($id)
{
    try {
        // Try to find the time-off request by its ID
        $timeOffRequest = RequestTimeoff::findOrFail($id);

        // Return the time-off request as a JSON response
        return response()->json([
            'status' => 200,
            'time_off_request' => $timeOffRequest,
        ], 200);

    } catch (ModelNotFoundException $e) {
        // Return a 404 response if the time-off request is not found
        return response()->json([
            'status' => 404,
            'message' => 'Time-off request not found',
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
            'employee' => 'nullable|array',
            'employee.*'=>'exists:jo_manage_employees,id',
            'policy' => 'nullable|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'download_request_form' => 'nullable|in:paid_daysoff,unpaid_daysoff',
            'upload_request_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048', // Validate file type and size
            'description' => 'nullable|string',
        ]);

        // Find the time-off request by ID or throw a ModelNotFoundException
        $timeOffRequest = RequestTimeoff::findOrFail($id);

        // // Process the uploaded document if provided
         if ($request->hasFile('upload_request_document')) {
        //     // Get the file from the request
            $document = $request->file('upload_request_document');
            
        //     // Create a unique name for the file using the current timestamp
             $documentName = time() . '.' . $document->getClientOriginalExtension();
            
        //     // Move the file to the 'documents' directory within the 'public' folder
             $document->move(public_path('documents'), $documentName);
            
        //     // Save the document name to the validated data to be updated
             $validatedData['upload_request_document'] = $documentName;
         }

        // Update the time-off request with the validated data
        $timeOffRequest->update($validatedData);

        // Return a successful response with the updated record
        return response()->json([
            'status' => 200,
            'message' => 'Time-off request updated successfully',
            'time_off_request' => $timeOffRequest,
        ], 200);

    } catch (\Exception $e) {
        // Log the error for debugging purposes
       // \Log::error('Error updating time-off request: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 500,
            'message' => 'Error updating time-off request',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    try {
        // Find the time-off request by ID or throw a ModelNotFoundException
        $timeOffRequest = RequestTimeoff::findOrFail($id);

        // Delete the time-off request
        $timeOffRequest->delete();

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Time-off request deleted successfully',
        ], 200);

    } catch (\Exception $e) {
        // Log the error for debugging purposes
     //   \Log::error('Error deleting time-off request: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 500,
            'message' => 'Failed to delete time-off request',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}

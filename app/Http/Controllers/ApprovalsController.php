<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ApprovalsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    try {
        // Get pagination parameters from the request or set defaults
        $perPage = $request->input('per_page', 15); // Default to 15 items per page

        // Fetch paginated approvals
        $approvals = Approval::paginate($perPage);

        // Return paginated results
        return response()->json([
            'status' => 200,
            'approvals' => $approvals->items(),
            'pagination' => [
                'total' => $approvals->total(),
                'per_page' => $approvals->perPage(),
                'current_page' => $approvals->currentPage(),
                'last_page' => $approvals->lastPage(),
                'from' => $approvals->firstItem(),
                'to' => $approvals->lastItem(),
            ],
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error fetching approvals: ' . $e->getMessage());
        return response()->json(['message' => 'Error fetching approvals', 'error' => $e->getMessage()], 500);
    }
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'min_count' => 'nullable|integer',
                'approval_policy' => 'nullable|string',
                'created_by' => 'nullable|string',
                'created_at' => 'nullable|string',
                'employees' => 'nullable|string',
                'teams' => 'nullable|string',
                'status' => 'nullable|string',
            ]);

            $approval = Approval::create($validated);
            return response()->json($approval, 201);
        } catch (ValidationException $e) {
            Log::error('Validation error while creating approval: ' . $e->getMessage());
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating approval: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating approval', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $approval = Approval::findOrFail($id);
            return response()->json($approval);
        } catch (\Exception $e) {
            Log::error('Error fetching approval: ' . $e->getMessage());
            return response()->json(['message' => 'Approval not found', 'error' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'min_count' => 'nullable|integer',
                'approval_policy' => 'nullable|string',
                'created_by' => 'nullable|string',
                'created_at' => 'nullable|string',
                'employees' => 'nullable|string',
                'teams' => 'nullable|string',
                'status' => 'nullable|string',
            ]);

            $approval = Approval::findOrFail($id);
            $approval->update($validated);
            return response()->json($approval);
        } catch (ValidationException $e) {
            Log::error('Validation error while updating approval: ' . $e->getMessage());
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating approval: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating approval', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $approval = Approval::findOrFail($id);
            $approval->delete();
            return response()->json(['message' => 'Approval deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Error deleting approval: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting approval', 'error' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $query = Approval::query();

        // Apply filters based on request parameters
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('min_count')) {
            $query->where('min_count', $request->input('min_count'));
        }

        if ($request->has('approval_policy')) {
            $query->where('approval_policy', 'like', '%' . $request->input('approval_policy') . '%');
        }

        if ($request->has('created_by')) {
            $query->where('created_by', 'like', '%' . $request->input('created_by') . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // You can add more filters as needed

        // Pagination
        $approvals = $query->paginate(10);

        // Format the response with pagination details
        return response()->json([
            'status' => 200,
            'approvals' => $approvals->items(),
            'pagination' => [
                'total' => $approvals->total(),
                'per_page' => $approvals->perPage(),
                'current_page' => $approvals->currentPage(),
                'last_page' => $approvals->lastPage(),
                'from' => $approvals->firstItem(),
                'to' => $approvals->lastItem(),
            ],
        ], 200);
    }
}

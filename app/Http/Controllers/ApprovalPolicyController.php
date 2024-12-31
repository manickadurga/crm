<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPolicy;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApprovalPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Retrieve approval policies with pagination
            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified
            $approvalPolicies = ApprovalPolicy::paginate($perPage);

            return response()->json([
                'status' => 200,
                'approvals' => $approvalPolicies->items(),
                'pagination' => [
                    'total' => $approvalPolicies->total(),
                    'title' => 'Approval Policies',
                    'per_page' => $approvalPolicies->perPage(),
                    'current_page' => $approvalPolicies->currentPage(),
                    'last_page' => $approvalPolicies->lastPage(),
                    'from' => $approvalPolicies->firstItem(),
                    'to' => $approvalPolicies->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve approval policies.'], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
            ]);

            // Create the approval policy record
            $approvalPolicy = ApprovalPolicy::create($validatedData);

            return response()->json([
                'status' => 201,
                'message' => 'Approval Policy created successfully.',
                'approval_policy' => $approvalPolicy,
            ], 201);

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'status' => 422,
                'error' => 'Validation failed.',
                'message' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json([
                'status' => 500,
                'error' => 'Failed to create Approval Policy.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $approvalPolicy = ApprovalPolicy::findOrFail($id);
            return response()->json([
                'status' => 200,
                'approval_policy' => $approvalPolicy,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'error' => 'Approval Policy not found.',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
            ]);

            // Find the approval policy or fail
            $approvalPolicy = ApprovalPolicy::findOrFail($id);

            // Update the approval policy record
            $approvalPolicy->update($validatedData);

            return response()->json([
                'status' => 200,
                'message' => 'Approval Policy updated successfully.',
                'approval_policy' => $approvalPolicy,
            ], 200);

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'status' => 422,
                'error' => 'Validation failed.',
                'message' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json([
                'status' => 500,
                'error' => 'Failed to update Approval Policy.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Find the approval policy or fail
            $approvalPolicy = ApprovalPolicy::findOrFail($id);

            // Delete the approval policy record
            $approvalPolicy->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Approval Policy deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'error' => 'Approval Policy not found.',
            ], 404);
        }
    }

}
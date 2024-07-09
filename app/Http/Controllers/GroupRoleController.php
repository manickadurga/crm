<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GroupRole;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class GroupRoleController extends Controller
{
    public function index(Request $request)
    {
        try {
            $groupRoles = GroupRole::with('group', 'role')->get();

            if ($groupRoles->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'groupRoles' => $groupRoles,
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve group roles: ' . $e->getMessage());
        
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve group roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'groupid' => 'required|integer|exists:jo_groups,id',
                'roleid' => 'required|string|exists:jo_roles,roleid',
            ]);

            $groupRole = GroupRole::create($validatedData);

            return response()->json(['message' => 'Group role created successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to create group role: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create group role: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $groupRole = GroupRole::with('group', 'role')->findOrFail($id);
            return response()->json(['status' => 200, 'groupRole' => $groupRole], 200);
        } catch (ModelNotFoundException $e) {
            Log::warning('Group role not found: ' . $id);
            return response()->json(['status' => 404, 'message' => 'Group role not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve group role details: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to retrieve group role details'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $groupRole = GroupRole::findOrFail($id);

            $validatedData = $request->validate([
                'groupid' => 'required|integer|exists:jo_groups,id',
                'roleid' => 'required|string|exists:jo_roles,roleid',
            ]);

            $groupRole->update($validatedData);

            return response()->json(['message' => 'Group role updated successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Group role not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to update group role: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred while processing your request. Please try again later.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $groupRole = GroupRole::findOrFail($id);
            $groupRole->delete();
            return response()->json(['message' => 'Group role deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Group role not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete group role: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred while processing your request. Please try again later.'], 500);
        }
    }
}


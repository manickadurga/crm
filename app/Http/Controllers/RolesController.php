<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RolesController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        try {
            $roles = Role::all();
            return response()->json(['status' => 200, 'roles' => $roles], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve roles: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to retrieve roles'], 500);
        }
    }

    /**
 * Store a newly created role in storage.
 */
/**
 * Store a newly created role in storage.
 */
public function store(Request $request)
{
    try {
        // Validate incoming data
        $validatedData = $request->validate([
            'roleid' => 'required|unique:jo_roles',
            'rolename' => 'required',
            'parentrole' => 'nullable|string',
            'allowassignedrecordsto' => 'required|integer',
        ]);

        // Determine parentrole and depth
        if ($request->filled('parentrole')) {
            $parentRole = $this->appendRoleToParent($validatedData['parentrole'], $validatedData['roleid']);
            $depth = $this->calculateDepth($parentRole); // Increment depth
        } else {
            $parentRole = $this->constructParentRole($validatedData['roleid']);
            $depth = 0; // Default depth for the top-level role
        } // Default depth for the top-level role

        // Create new role
        $role = Role::create([
            'roleid' => $validatedData['roleid'],
            'rolename' => $validatedData['rolename'],
            'parentrole' => $parentRole,
            'depth' => $depth,
            'allowassignedrecordsto' => $validatedData['allowassignedrecordsto'],
        ]);

        return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
    } catch (ValidationException $e) {
        Log::error('Validation failed: ' . $e->getMessage());
        return response()->json(['status' => 422, 'error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Failed to create role: ' . $e->getMessage());
        return response()->json(['status' => 500, 'message' => 'Failed to create role'], 500);
    }
}

/**
 * Helper method to construct parentrole based on the roleid.
 */
protected function constructParentRole($roleid)
{
    // Construct parentrole based on the roleid
    return '' . $roleid;
}

/**
 * Helper method to append roleid to existing parentrole.
 */
protected function appendRoleToParent($parentRole, $roleid)
{
    // Append roleid to existing parentrole
    return rtrim($parentRole, '::') . '::' . $roleid;
}

/**
 * Helper method to calculate depth based on parentrole.
 */
protected function calculateDepth($parentRole)
{
    return substr_count($parentRole, '::');
}
    /**
     * Display the specified role.
     */
    public function show($id)
    {
        try {
            $role = Role::findOrFail($id);
            return response()->json(['status' => 200, 'role' => $role], 200);
        } catch (ModelNotFoundException $e) {
            Log::warning('Role not found: ' . $id);
            return response()->json(['status' => 404, 'message' => 'Role not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve role: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to retrieve role'], 500);
        }
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            $validatedData = $request->validate([
                'roleid' => 'required|string|unique:jo_roles,roleid,' . $id,
                'rolename' => 'required|string|unique:jo_roles,rolename,' . $id,
                'parentrole' => 'required|string',
                'depth' => 'required|string',
                'allowassignedrecordsto' => 'required|integer',
            ]);

            $role->update($validatedData);

            return response()->json(['status' => 200, 'message' => 'Role updated successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['status' => 422, 'error' => $e->validator->errors()], 422);
        } catch (ModelNotFoundException $e) {
            Log::warning('Role not found: ' . $id);
            return response()->json(['status' => 404, 'message' => 'Role not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to update role: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to update role'], 500);
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();
            return response()->json(['status' => 200, 'message' => 'Role deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            Log::warning('Role not found: ' . $id);
            return response()->json(['status' => 404, 'message' => 'Role not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete role: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to delete role'], 500);
        }
    }
}

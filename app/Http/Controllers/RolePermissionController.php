<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    public function storeRole(Request $request)
    {
        $request->validate(['name' => 'required|unique:jo_roles,name']);
        $role = Role::create(['name' => $request->name]);
        return response()->json([
            'message' => 'Role created successfully.',
            'role' => $role
        ], 201);
    }

    public function deleteRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        $role->delete();
        return response()->json([
            'message' => 'Role deleted successfully.'
        ]);
    }

    public function assignPermissions(Request $request, $roleId)
    {
        $request->validate(['permissions' => 'required|array']);
        $role = Role::findOrFail($roleId);
        $role->permissions()->sync($request->permissions);
        return response()->json([
            'message' => 'Permissions assigned successfully.',
            'role' => $role->load('permissions')
        ]);
    }

}

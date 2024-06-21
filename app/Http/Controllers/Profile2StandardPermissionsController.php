<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Profile2StandardPermissions; // Import the model

class Profile2StandardPermissionsController extends Controller
{
    public function index()
    {
        try {
            $permissions = Profile2StandardPermissions::all();
            return response()->json($permissions);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_profile2standardpermissions: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch permissions'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'profileid' => 'required|exists:jo_profiles,profileid',
                'tabid' => 'required|exists:jo_tabs,tabid',
                'operations' => 'required|integer',
                'permissions' => 'required|integer',
            ]);

            $permission = Profile2StandardPermissions::create($validatedData);
            return response()->json($permission, 201);
        } catch (\Exception $e) {
            Log::error('Error creating jo_profile2standardpermissions: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create permission'], 500);
        }
    }

    public function show($id)
    {
        try {
            $permission = Profile2StandardPermissions::findOrFail($id);
            return response()->json($permission);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_profile2standardpermissions: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch permission'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'profileid' => 'required|exists:jo_profiles,profileid',
                'tabid' => 'required|exists:jo_tabs,tabid',
                'operations' => 'required|integer',
                'permissions' => 'required|integer',
            ]);

            $permission = Profile2StandardPermissions::findOrFail($id);
            $permission->update($validatedData);
            return response()->json($permission);
        } catch (\Exception $e) {
            Log::error('Error updating jo_profile2standardpermissions: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update permission'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $permission = Profile2StandardPermissions::findOrFail($id);
            $permission->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting jo_profile2standardpermissions: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete permission'], 500);
        }
    }
    public function updatePermissions(Request $request, $id)
    {
        $permission = Profile2StandardPermissions::find($id);

        if ($permission) {
            if ($permission->updatePermissions($request->input('permissions'))) {
                return response()->json(['message' => 'Permissions updated successfully.']);
            } else {
                return response()->json(['message' => 'Record is not editable.'], 403);
            }
        }

        return response()->json(['message' => 'Record not found.'], 404);
    }
}

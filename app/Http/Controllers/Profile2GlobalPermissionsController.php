<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile2GlobalPermissions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Profile2GlobalPermissionsController extends Controller
{
    public function index()
    {
        try {
            $permissions = Profile2GlobalPermissions::all();
            return response()->json($permissions);
        } catch (\Exception $e) {
            Log::error('Error fetching global permissions: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch global permissions'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'profileid' => 'required|exists:jo_profiles,id',
                'globalactionid' => 'required|integer',
                'globalactionpermission' => 'nullable|integer',
            ]);

            $permission = Profile2GlobalPermissions::create($validatedData);
            return response()->json($permission, 201);
        } catch (\Exception $e) {
            Log::error('Error creating global permission: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create global permission'], 500);
        }
    }

    public function show($id)
    {
        try {
            $permission = Profile2GlobalPermissions::findOrFail($id);
            return response()->json($permission);
        } catch (\Exception $e) {
            Log::error('Error fetching global permission: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch global permission'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'profileid' => 'required|exists:jo_profiles,id',
                'globalactionid' => 'required|integer',
                'globalactionpermission' => 'nullable|integer',
            ]);

            $permission = Profile2GlobalPermissions::findOrFail($id);
            $permission->update($validatedData);
            return response()->json($permission);
        } catch (\Exception $e) {
            Log::error('Error updating global permission: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update global permission'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $permission = Profile2GlobalPermissions::findOrFail($id);
            $permission->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting global permission: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete global permission'], 500);
        }
    }
}

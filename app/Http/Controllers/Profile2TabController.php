<?php

namespace App\Http\Controllers;

use App\Models\Profile2Tab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Profile2TabController extends Controller
{
    public function index()
    {
        try {
            $profile2tabs = Profile2Tab::all();
            return response()->json($profile2tabs);
        } catch (\Exception $e) {
            Log::error('Error fetching profile2tabs: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch profile2tabs'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'profileid' => 'required|exists:jo_profiles,profileid',
                'tabid' => 'required|exists:jo_tabs,tabid',
                'permissions' => 'nullable|integer',
            ]);

            $profile2tab = Profile2Tab::create($validatedData);
            return response()->json($profile2tab, 201);
        } catch (\Exception $e) {
            Log::error('Error creating profile2tab: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create profile2tab'], 500);
        }
    }

    public function show($id)
    {
        try {
            $profile2tab = Profile2Tab::findOrFail($id);
            return response()->json($profile2tab);
        } catch (\Exception $e) {
            Log::error('Error fetching profile2tab: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch profile2tab'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'profileid' => 'required|exists:jo_profiles,profileid',
                'tabid' => 'required|exists:jo_tabs,tabid',
                'permissions' => 'nullable|integer',
            ]);

            $profile2tab = Profile2Tab::findOrFail($id);
            $profile2tab->update($validatedData);
            return response()->json($profile2tab);
        } catch (\Exception $e) {
            Log::error('Error updating profile2tab: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update profile2tab'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $profile2tab = Profile2Tab::findOrFail($id);
            $profile2tab->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting profile2tab: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete profile2tab'], 500);
        }
    }
    public function updatePermissions(Request $request, $id)
    {
        $profile2Tab = Profile2Tab::find($id);

        if ($profile2Tab) {
            if ($profile2Tab->updatePermissions($request->input('permissions'))) {
                return response()->json(['message' => 'Permissions updated successfully.']);
            } else {
                return response()->json(['message' => 'Record is not editable.'], 403);
            }
        }

        return response()->json(['message' => 'Record not found.'], 404);
    }
}

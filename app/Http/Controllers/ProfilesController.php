<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfilesController extends Controller
{
    public function index()
    {
        try {
            $profiles = Profile::all();
            return response()->json($profiles);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_profiles: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch profiles'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'profileid'=>'required|unique:jo_profiles',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'directly_related_to_role'=>'nullable|numeric|default:1'

            ]);

            $profile = Profile::create($validatedData);
            return response()->json($profile, 201);
        } catch (\Exception $e) {
            Log::error('Error creating jo_profile: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create profile'], 500);
        }
    }

    public function show($id)
    {
        try {
            $profile = Profile::findOrFail($id);
            return response()->json($profile);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_profile: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch profile'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'profileid'=>'required|unique:jo_profiles',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'directly_related_to_role'=>'nullable|string'
            ]);

            $profile = Profile::findOrFail($id);
            $profile->update($validatedData);
            return response()->json($profile);
        } catch (\Exception $e) {
            Log::error('Error updating jo_profile: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update profile'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $profile = Profile::findOrFail($id);
            $profile->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting jo_profile: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete profile'], 500);
        }
    }
}


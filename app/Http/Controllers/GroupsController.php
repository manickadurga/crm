<?php

namespace App\Http\Controllers;

use App\Models\Groups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupsController extends Controller
{
    public function index()
    {
        try {
            $groups = Groups::all();
            return response()->json($groups);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_groups: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch groups'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'group_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                //'group_members' => 'required|array',
                //'group_members.*' => 'exists:jo_roles,rolename',
                'group_members' => 'required|array',
                'group_members.*.Users' => 'exists:users,id',
                'group_members.*.Roles' => 'exists:jo_roles,id',
                'group_members.*.Roles and Sub Ordinates' => 'exists:jo_roles,id',
                'group_members.*.Groups' => 'exists:jo_groups,id',
            ]);
            //dd($validatedData);

            $group = Groups::create($validatedData);
            return response()->json($group, 201);
        } catch (\Exception $e) {
            Log::error('Error creating jo_group: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create group'], 500);
        }
    }

    public function show($id)
    {
        try {
            $group = Groups::findOrFail($id);
            return response()->json($group);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_group: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch group'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'group_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'group_members' => 'required|array',
            ]);

            $group = Groups::findOrFail($id);
            $group->update($validatedData);
            return response()->json($group);
        } catch (\Exception $e) {
            Log::error('Error updating jo_group: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update group'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $group = Groups::findOrFail($id);
            $group->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting jo_group: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete group'], 500);
        }
    }
}


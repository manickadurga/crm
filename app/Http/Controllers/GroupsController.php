<?php

namespace App\Http\Controllers;

use App\Models\Groups;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GroupsController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'search' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);

            // Initialize the query builder
            $query = Groups::query();

            // Apply search filters
            if (isset($validatedData['search'])) {
                $search = $validatedData['search'];
                $query->where('group_name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
            }

            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // default per_page value
            $groups = $query->paginate($perPage);

            // Check if any groups found
            if ($groups->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'groups' => $groups->items(),
                'pagination' => [
                    'total' => $groups->total(),
                    'per_page' => $groups->perPage(),
                    'current_page' => $groups->currentPage(),
                    'last_page' => $groups->lastPage(),
                    'from' => $groups->firstItem(),
                    'to' => $groups->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search groups: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search groups: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'group_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'group_members' => 'required|array',
                'group_members.*.Users' => 'exists:users,id',
                'group_members.*.Roles' => 'exists:jo_roles,id',
                'group_members.*.Roles and Sub Ordinates' => 'exists:jo_roles,id',
                'group_members.*.Groups' => 'exists:jo_groups,id',
            ]);

            // Process group members if provided
            $groupMembers = [];
            foreach ($validatedData['group_members'] as $member) {
                $memberInfo = [];

                // Retrieve and store user information
                if (isset($member['Users'])) {
                    $user =User::find($member['Users']);
                    if ($user) {
                        $memberInfo['user_name'] = $user->name;
                    } else {
                        throw ValidationException::withMessages(['group_members' => "User with ID '{$member['Users']}' not found"]);
                    }
                }

                // Retrieve and store role information
                if (isset($member['Roles'])) {
                    $role = Role::find($member['Roles']);
                    if ($role) {
                        $memberInfo['role_name'] = $role->rolename;
                    } else {
                        throw ValidationException::withMessages(['group_members' => "Role with ID '{$member['Roles']}' not found"]);
                    }
                }

                // Retrieve and store group information
                if (isset($member['Groups'])) {
                    $group = Groups::find($member['Groups']);
                    if ($group) {
                        $memberInfo['group_name'] = $group->group_name;
                    } else {
                        throw ValidationException::withMessages(['group_members' => "Group with ID '{$member['Groups']}' not found"]);
                    }
                }

                $groupMembers[] = $memberInfo;
            }

            // Prepare data for group creation
            $groupData = [
                'group_name' => $validatedData['group_name'],
                'description' => $validatedData['description'] ?? null,
                'group_members' => json_encode($groupMembers),
            ];

            // Create a new group record in the database
            Groups::create($groupData);

            // Return a success response
            return response()->json(['message' => 'Group created successfully']);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to create group: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create group: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $group = Groups::findOrFail($id);
            return response()->json($group);
        } catch (Exception $e) {
            Log::error('Error fetching jo_group: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch group'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'group_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'group_members' => 'nullable|array',
            ]);

            $group = Groups::findOrFail($id);
            $group->update($validatedData);
            return response()->json($group);
        } catch (Exception $e) {
            Log::error('Error updating jo_group: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update group'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $group = Groups::findOrFail($id);
            $group->delete();
            return response()->json(['message' => 'Group deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting group: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete group'], 500);
        }
    }
    
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'group_name' => 'nullable|string',
                'description' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);

            // Initialize the query builder
            $query = Groups::query();

            // Apply search filters
            foreach ($validatedData as $key => $value) {
                if ($value !== null && in_array($key, ['group_name', 'description'])) {
                    $query->where($key, 'like', '%' . $value . '%');
                }
            }

            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // default per_page value
            $groups = $query->paginate($perPage);

            // Check if any groups found
            if ($groups->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'groups' => $groups->items(),
                'pagination' => [
                    'total' => $groups->total(),
                    'per_page' => $groups->perPage(),
                    'current_page' => $groups->currentPage(),
                    'last_page' => $groups->lastPage(),
                    'from' => $groups->firstItem(),
                    'to' => $groups->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search groups: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search groups: ' . $e->getMessage()], 500);
        }
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Teams;
use App\Models\Employee;
use App\Models\Crmentity;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TeamsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);

            $teams = Teams::select('id', 'team_name', 'add_or_remove_managers', 'add_or_remove_members')
                ->paginate($perPage);

            $formattedTeams = $teams->map(function ($team) {
                // Initialize arrays
                $managers = [];
                $members = [];

                // Handle managers field
                if (!empty($team->add_or_remove_managers)) {
                    // Decode managers field if it's a string
                    $managerIds = is_string($team->add_or_remove_managers) ? json_decode($team->add_or_remove_managers) : $team->add_or_remove_managers;

                    // Fetch manager first names using manager IDs
                    $managerNames = Employee::whereIn('id', $managerIds)
                        ->pluck('first_name')
                        ->toArray();

                    // Assign manager names directly to $managers
                    $managers = $managerNames;
                }

                // Handle members field
                if (!empty($team->add_or_remove_members)) {
                    // Decode members field if it's a string
                    $memberIds = is_string($team->add_or_remove_members) ? json_decode($team->add_or_remove_members) : $team->add_or_remove_members;

                    // Fetch member first names using member IDs
                    $memberNames = Employee::whereIn('id', $memberIds)
                        ->pluck('first_name')
                        ->toArray();

                    // Assign member names directly to $members
                    $members = $memberNames;
                }

                return [
                    'id' => $team->id,
                    'team_name' => $team->teamName,
                    'managers' => $managers,
                    'members' => $members,
                ];
            });

            return response()->json([
                'status' => 200,
                'teams' => $formattedTeams,
                'pagination' => [
                    'total' => $teams->total(),
                    'title' => 'Teams',
                    'per_page' => $teams->perPage(),
                    'current_page' => $teams->currentPage(),
                    'last_page' => $teams->lastPage(),
                    'from' => $teams->firstItem(),
                    'to' => $teams->lastItem(),
                ],
            ], 200);

        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve teams: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve teams',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $team = Teams::findOrFail($id);
            return response()->json($team);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Team not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch team: ' . $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        DB::beginTransaction(); // Start a transaction
    
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'team_name' => 'required|string|max:255',
                'add_or_remove_projects' => 'nullable|array',
                'add_or_remove_projects.*'=>'exists:jo_projects,id',
                'add_or_remove_managers' => 'required|array',
                'add_or_remove_managers.*'=>'exists:jo_manage_employees,id',
                'add_or_remove_members' => 'nullable|array',
                'add_or_remove_members.*'=>'exists:jo_managae_employees,id',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
            ]);
    
            Log::info('Validated data:', $validatedData);
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
                $validatedData['image'] = $imageName;
            }
            // Create Crmentity record via CrmentityController
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Teams', $validatedData['team_name'] ?? ''); // Pass an empty string if team_name is null
    
            if (!$crmid) {
                throw new \Exception('Failed to create Crmentity');
            }
    
            // Add crmid to validated data
            $validatedData['id'] = $crmid;
    
            // Create the Team record with crmid
            $team = Teams::create($validatedData);
    
            DB::commit(); // Commit the transaction
    
            return response()->json($team, 201);
    
        } catch (ValidationException $e) {
            DB::rollBack(); // Rollback the transaction on validation error
            return response()->json(['error' => 'Validation error: ' . $e->getMessage()], 422);
        } catch (Exception $e) {
            DB::rollBack(); // Rollback the transaction on general error
            Log::error('Failed to create team: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create team: ' . $e->getMessage()], 500);
        }
    }
    


    public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // Find the team by ID
        $team = Teams::findOrFail($id);

        // Validate request data
        $validatedData = $request->validate([
            'image' => 'nullable|image',
            'team_name' => 'nullable|string|max:255',
            'add_or_remove_projects' => 'nullable|string',
            'add_or_remove_managers' => 'nullable|string',
            'add_or_remove_members' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
            //'orgid' => 'nullable|integer',
        ]);

        // Update the team
        $team->update($validatedData);

        // Prepare Crmentity update data
        $crmentityData = [
            'label' => $validatedData['team_name'] ?? 'null',
            //'description' => $validatedData['add_or_remove_projects'] ?? '',
            // Include other fields if needed
        ];

        // Update the corresponding Crmentity record
        $crmentity = Crmentity::where('crmid', $id)->first();

        if ($crmentity) {
            // Update existing Crmentity record
            $crmentity->update($crmentityData);
        } else {
            // Optionally create a new Crmentity record if it does not exist
            $crmentity = new Crmentity();
            $crmentity->crmid = $id; // Or use an appropriate unique identifier
            $crmentity->label = $validatedData['team_name'] ?? 'null';
            //$crmentity->description = $validatedData['add_or_remove_projects'] ?? '';
            $crmentity->save();
        }

        // Commit transaction
        DB::commit();

        return response()->json([
            'message' => 'Team and Crmentity updated successfully',
            'team' => $team,
            'crmentity' => $crmentity,
        ], 200);

    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'status' => 422,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (ModelNotFoundException $e) {
        DB::rollBack();
        return response()->json(['error' => 'Team not found'], 404);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update team and Crmentity: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
        return response()->json([
            'error' => 'Failed to update team and Crmentity',
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function destroy($id)
    {
        try {
            $team = Teams::findOrFail($id);
            $team->delete();

            return response()->json(['message' => 'Team deleted successfully']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Team not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete team: ' . $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = Teams::select('id', 'team_name', 'add_or_remove_managers', 'add_or_remove_members');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('team_name', 'LIKE', "%$search%")
                      ->orWhere('add_or_remove_managers', 'LIKE', "%$search%")
                      ->orWhere('add_or_remove_members', 'LIKE', "%$search%");
                });
            }

            $teams = $query->paginate($perPage);

            $formattedTeams = $teams->map(function ($team) {
                return [
                    'id' => $team->id,
                    'team_name' => $team->team_name,
                    'add_or_remove_managers' => $team->add_or_remove_managers,
                    'add_or_remove_members' => $team->add_or_remove_members,
                ];
            });

            return response()->json([
                'status' => 200,
                'teams' => $formattedTeams,
                'pagination' => [
                    'total' => $teams->total(),
                    'per_page' => $teams->perPage(),
                    'current_page' => $teams->currentPage(),
                    'last_page' => $teams->lastPage(),
                    'from' => $teams->firstItem(),
                    'to' => $teams->lastItem(),
                ],
            ], 200);

        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to search teams: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to search teams',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

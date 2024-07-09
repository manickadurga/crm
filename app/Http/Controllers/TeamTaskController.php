<?php

namespace App\Http\Controllers;

use App\Models\TeamTask;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TeamtaskController extends Controller
{
   
public function index(Request $request): JsonResponse
{
    try {
        // Set the number of items per page, default is 10
        $perPage = $request->input('per_page', 10);

        // Get paginated tasks with specific fields including 'id'
        $tasks = TeamTask::select('id', 'title', 'projects', 'duedate', 'status')
            ->paginate($perPage);
        return response()->json([
            'status' => 200,
            'tasks' => $tasks->items(),
            'pagination' => [
                'total' => $tasks->total(),
                'per_page' => $tasks->perPage(),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'from' => $tasks->firstItem(),
                'to' => $tasks->lastItem(),
            ],
        ], 200);

    } catch (\Exception $e) {
        // Log the error
        Log::error('Failed to retrieve tasks: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'status' => 500,
            'message' => 'Failed to retrieve tasks',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function store(Request $request): JsonResponse
{
    try {
        $validatedData = $request->validate([
            'tasknumber' => 'nullable|numeric',
            'projects' => 'nullable|string', // Accept as string of comma-separated IDs
            'status' => 'nullable|string',
            'teams' => 'nullable|array', // Ensure teams are an array
            'teams.*' => 'exists:jo_teams,id', // Ensure teams exist in jo_teams table
            'title' => 'required|string',
            'priority' => 'nullable|string',
            'size' => 'nullable|string',
            'tags' => 'nullable|array', // Ensure tags are an array
            'tags.*' => 'exists:jo_tags,id', // Ensure tags exist in jo_tags table
            'duedate' => 'nullable|date',
            'estimate' => 'nullable|array', // Ensure estimate is an array
            'estimate.days' => 'nullable|numeric',
            'estimate.hours' => 'nullable|numeric',
            'estimate.minutes' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $estimateInMinutes = ($validatedData['estimate']['days'] ?? 0) * 24 * 60
            + ($validatedData['estimate']['hours'] ?? 0) * 60
            + ($validatedData['estimate']['minutes'] ?? 0);

        // Fetch project names if provided as IDs
        $projects = $validatedData['projects'] ?? '';
        $projectsNames = DB::table('jo_projects')
            ->whereIn('id', explode(',', $projects)) // Convert to array of IDs
            ->pluck('name') // Assuming the column name is 'name'
            ->implode(', '); // Convert array to comma-separated string

        // Fetch names for teams and tags if IDs are provided
        $teams = $validatedData['teams'] ?? [];
        $tags = $validatedData['tags'] ?? [];

        $teamsNames = DB::table('jo_teams')
            ->whereIn('id', $teams)
            ->pluck('team_name') // Assuming the column name is 'team_name'
            ->toArray();

        $tagsNames = DB::table('jo_tags')
            ->whereIn('id', $tags)
            ->pluck('tags_names') // Assuming the column name is 'tags_names'
            ->toArray();

        $validatedData['estimate'] = $estimateInMinutes;
        $validatedData['projects'] = $projectsNames;
        $validatedData['teams'] = json_encode($teamsNames);
        $validatedData['tags'] = json_encode($tagsNames);

        $teamTask = TeamTask::create($validatedData);

        return response()->json($teamTask, 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to create task', 'error' => $e->getMessage()], 500);
    }
}
    public function show($id): JsonResponse
    {
        try {
            $teamTask = TeamTask::findOrFail($id);
            return response()->json($teamTask);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'TeamTask not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve task', 'error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $teamTask = TeamTask::findOrFail($id);
    
            $validatedData = $request->validate([
                'tasknumber' => 'nullable|numeric',
                'projects' => 'nullable|string', // Accept as string of comma-separated IDs
                'status' => 'nullable|string',
                'teams' => 'nullable|array', // Ensure teams are an array
                'teams.*' => 'exists:jo_teams,id', // Ensure teams exist in jo_teams table
                'title' => 'required|string',
                'priority' => 'nullable|string',
                'size' => 'nullable|string',
                'tags' => 'nullable|array', // Ensure tags are an array
                'tags.*' => 'exists:jo_tags,id', // Ensure tags exist in jo_tags table
                'duedate' => 'nullable|date',
                'estimate' => 'nullable|array', // Ensure estimate is an array
                'estimate.days' => 'nullable|numeric',
                'estimate.hours' => 'nullable|numeric',
                'estimate.minutes' => 'nullable|numeric',
                'description' => 'nullable|string',
            ]);
    
            $estimateInMinutes = ($validatedData['estimate']['days'] ?? 0) * 24 * 60
                + ($validatedData['estimate']['hours'] ?? 0) * 60
                + ($validatedData['estimate']['minutes'] ?? 0);
    
            $projects = $validatedData['projects'] ?? '';
            $teams = $validatedData['teams'] ?? [];
            $tags = $validatedData['tags'] ?? [];
    
            $projectsNames = DB::table('jo_projects')
                ->whereIn('id', explode(',', $projects)) // Convert to array of IDs
                ->pluck('name')
                ->implode(', '); // Convert array to comma-separated string
    
            $teamsNames = [];
            if (!empty($teams)) {
                $teamsNames = DB::table('jo_teams')
                    ->whereIn('id', $teams)
                    ->pluck('team_name')
                    ->toArray();
            }
    
            $tagsNames = [];
            if (!empty($tags)) {
                $tagsNames = DB::table('jo_tags')
                    ->whereIn('id', $tags)
                    ->pluck('tags_names')
                    ->toArray();
            }
    
            $validatedData['estimate'] = $estimateInMinutes;
            $validatedData['projects'] = $projectsNames;
            $validatedData['teams'] = json_encode($teamsNames);
            $validatedData['tags'] = json_encode($tagsNames);
    
            $teamTask->update($validatedData);
    
            return response()->json($teamTask, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'TeamTask not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update task', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function destroy($id): JsonResponse
    {
        try {
            $task = TeamTask::findOrFail($id);
            $task->delete();
            return response()->json(['message' => 'Task deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'TeamTask not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete task', 'error' => $e->getMessage()], 500);
        }
    }
   
public function search(Request $request): JsonResponse
{
    try {
        $query = $request->input('q');

        // Perform search using 'like' operator on 'projects' and 'title' fields
        $teamTasks = TeamTask::where('projects', 'like', "%$query%")
            ->orWhere('title', 'like', "%$query%")
            ->paginate(10);

        return response()->json([
            'status' => 200,
            'team_tasks' => $teamTasks->items(),
            'pagination' => [
                'total' => $teamTasks->total(),
                'per_page' => $teamTasks->perPage(),
                'current_page' => $teamTasks->currentPage(),
                'last_page' => $teamTasks->lastPage(),
                'from' => $teamTasks->firstItem(),
                'to' => $teamTasks->lastItem(),
            ],
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to search team tasks', 'error' => $e->getMessage()], 500);
    }
}

}
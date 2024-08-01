<?php

namespace App\Http\Controllers;

use App\Models\TeamTask;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Crmentity;

class TeamTaskController extends Controller
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
                'title' => 'TeamTasks',
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
    DB::beginTransaction(); // Start a transaction to ensure atomic operations

    try {
        $validatedData = $request->validate([
            'tasknumber' => 'nullable|numeric',
            'projects' => 'nullable|string',
            'projects.*' => 'exists:jo_projects,id',
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

        // Calculate estimate in minutes
        $estimateInMinutes = ($validatedData['estimate']['days'] ?? 0) * 24 * 60
            + ($validatedData['estimate']['hours'] ?? 0) * 60
            + ($validatedData['estimate']['minutes'] ?? 0);

        // Add estimate to validated data
        $validatedData['estimate'] = $estimateInMinutes;

        // Create Crmentity record
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Teamtask', $validatedData['title']);

        if (!$crmid) {
            throw new \Exception('Failed to create Crmentity');
        }

        // Add crmid to validated data
        $validatedData['id'] = $crmid;

        // Create the TeamTask record with crmid
        $teamTask = TeamTask::create($validatedData);

        DB::commit(); // Commit the transaction

        return response()->json($teamTask, 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack(); // Rollback the transaction on validation error
        return response()->json([
            'message' => 'Validation Error',
            'errors' => $e->errors(), // Use the errors() method directly
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction on general error
        Log::error('Failed to create task: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
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
        DB::beginTransaction();
    
        try {
            // Log the ID for debugging
            Log::info('Attempting to update TeamTask with ID: ' . $id);
    
            // Find the team task by ID
            $teamTask = TeamTask::findOrFail($id);
    
            // Validate request data
            $validatedData = $request->validate([
                'tasknumber' => 'nullable|numeric',
                'projects' => 'nullable|array',
                'projects.*' => 'exists:jo_projects,id',
                'status' => 'nullable|string',
                'teams' => 'nullable|array',
                'teams.*' => 'exists:jo_teams,id',
                'title' => 'nullable|string',
                'priority' => 'nullable|string',
                'size' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'duedate' => 'nullable|date',
                'estimate' => 'nullable|array',
                'estimate.days' => 'nullable|numeric',
                'estimate.hours' => 'nullable|numeric',
                'estimate.minutes' => 'nullable|numeric',
                'description' => 'nullable|string',
            ]);
    
            // Calculate estimate in minutes
            $estimateInMinutes = ($validatedData['estimate']['days'] ?? 0) * 24 * 60
                + ($validatedData['estimate']['hours'] ?? 0) * 60
                + ($validatedData['estimate']['minutes'] ?? 0);
    
            $validatedData['estimate'] = $estimateInMinutes;
    
            // Update the team task
            $teamTask->update($validatedData);
    
            // Prepare Crmentity update data
            $crmentityData = [
                'label' => $validatedData['title'] ?? null,
                // 'description' => $validatedData['description'] ?? '',
                // 'status' => $validatedData['status'] ?? '',
            ];
    
            // Update or create Crmentity record
            $crmentity = Crmentity::where('crmid', $id)->first();
    
            if ($crmentity) {
                // Update existing Crmentity record
                $crmentity->update($crmentityData);
            } else {
                // Create a new Crmentity record if it does not exist
                $crmentity = new Crmentity();
                $crmentity->crmid = $id; // Use a unique identifier
                $crmentity->label = $validatedData['title'] ?? null;
                // $crmentity->description = $validatedData['description'] ?? '';
                // $crmentity->status = $validatedData['status'] ?? '';
                $crmentity->save();
            }
    
            // Commit transaction
            DB::commit();
    
            return response()->json([
                'message' => 'TeamTask and Crmentity updated successfully',
                'teamTask' => $teamTask,
                'crmentity' => $crmentity,
            ], 200);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('TeamTask not found with ID: ' . $id);
            return response()->json(['message' => 'TeamTask not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update TeamTask and Crmentity: ' . $e->getMessage());
            Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
            return response()->json([
                'message' => 'Failed to update TeamTask and Crmentity',
                'error' => $e->getMessage(),
            ], 500);
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

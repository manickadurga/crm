<?php

namespace App\Http\Controllers;
// use App\Models\Invoices;

use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\TaskDueDateReminder;
use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Config\Exception\ValidationException;
use App\Models\Crmentity;

class TasksController extends Controller
{
    //
    public function index()
    {
        $tasks = Tasks::select('id', 'title', 'tags', 'projects', 'created_at', 'choose', 'duedate', 'status')->get();
    
        if ($tasks->count() > 0) {
            return response()->json([
                'status' => 200,
                'tasks' => $tasks 
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Data Found'
            ], 404);
        }
    }
    
    public function store(Request $request)
{
    DB::beginTransaction(); // Start a transaction to ensure atomic operations

    try {
        // Validate the request data
        $validatedData = $request->validate([
            'tasksnumber' => 'nullable|integer',
            'projects' => 'nullable|array|max:5000',
            'projects.*' => 'exists:jo_projects,id',
            'status' => 'nullable|string',
            'choose' => 'required|in:employees,teams',
            'addorremoveemployee' => 'nullable|array|max:5000',
            'addorremoveemployee.*' => 'exists:jo_manage_employees,id',
            'chooseteams'=>'nullable|array|max:5000',
            'chooseteams.*'=>'exists:jo_teams,id',
            'title' => 'required|string',
            'priority' => 'nullable|string',
            'size' => 'nullable|string',
            'tags' => 'nullable|array|max:5000',
            'tags.*' => 'exists:jo_tags,id',
            'duedate' => 'nullable|date',
            'estimate' => 'nullable|array',
            'estimate.days' => 'nullable|integer',
            'estimate.hours' => 'nullable|integer',
            'estimate.minutes' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);
        // if ($validatedData['choose'] === 'employees') {
        //     // Ensure 'addorremoveemployee' is set and 'chooseteams' is null
        //     $validatedData['chooseteams'] = null;
        //     if (empty($validatedData['addorremoveemployee'])) {
        //         throw new \Exception('Employees must be provided when "choose" is set to employees.');
        //     }
        // } elseif ($validatedData['choose'] === 'teams') {
        //     // Ensure 'chooseteams' is set and 'addorremoveemployee' is null
        //     $validatedData['addorremoveemployee'] = null;
        //     if (empty($validatedData['chooseteams'])) {
        //         throw new \Exception('Teams must be provided when "choose" is set to teams.');
        //     }
        // }

        // Handle estimate: convert estimate array to JSON
        $validatedData['estimate'] = json_encode([
            'days' => $validatedData['estimate']['days'] ?? 0,
            'hours' => $validatedData['estimate']['hours'] ?? 0,
            'minutes' => $validatedData['estimate']['minutes'] ?? 0,
        ]);

        // Create Crmentity record via CrmentityController
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Tasks', $validatedData['title']);

        if (!$crmid) {
            throw new \Exception('Failed to create Crmentity');
        }

        // Add crmid to validated data
        $validatedData['id'] = $crmid;

        // Create the Task record with crmid
        $task = Tasks::create($validatedData);

        // Trigger TaskCreated event
        event(new TaskCreated($task));
        // Trigger TaskDueDateReminder event for due date check
        event(new TaskDueDateReminder($task));

        DB::commit(); // Commit the transaction

        return response()->json(['message' => 'Task created successfully', 'task' => $task], 201);

    } catch (ValidationException $e) {
        DB::rollBack(); // Rollback the transaction on validation error
        return response()->json([
            'status' => 422,
            'message' => 'Validation failed',
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction on general error
        Log::error('Failed to create task: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
        return response()->json(['error' => 'Failed to create task: ' . $e->getMessage()], 500);
    }
}

public function show($id)
{
    try {
        // Find the task by ID
        $task = Tasks::findOrFail($id);

        // Return the task as JSON response
        return response()->json(['status' => 200, 'task' => $task], 200);

    } catch (\Exception $e) {
        // Handle any errors or not found exceptions
        return response()->json(['status' => 404, 'message' => 'Task not found'], 404);
    }
}

public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // Find the task by ID
        $task = Tasks::findOrFail($id);

        // Validate the request data
        $validated = $request->validate([
            'tasksnumber' => 'nullable|integer',
            'projects' => 'nullable|array|max:5000',
            'projects.*' => 'exists:jo_projects,id',
            'status' => 'nullable|string',
            'choose' => 'in:employees,teams',
            'addorremoveemployee' => 'nullable|array|max:5000',
            'addorremoveemployee.*' => 'exists:jo_manage_employees,id',
            'chooseteams' => 'nullable|array|max:5000',
            'chooseteams.*' => 'exists:jo_teams,id',
            'title' => 'required|string',
            'priority' => 'nullable|string',
            'size' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'duedate' => 'nullable|date',
            'estimate' => 'nullable|array',
            'estimate.days' => 'nullable|integer',
            'estimate.hours' => 'nullable|integer',
            'estimate.minutes' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

         // Set default empty array for nullable fields
         $validated['projects'] = $validated['projects'] ?? [];
         $validated['addorremoveemployee'] = $validated['addorremoveemployee'] ?? [];
         $validated['chooseteams'] = $validated['chooseteams'] ?? [];
         $validated['tags'] = $validated['tags'] ?? [];

        // Handle estimate
        $validated['estimate'] = [
            'days' => $validated['estimate']['days'] ?? 0,
            'hours' => $validated['estimate']['hours'] ?? 0,
            'minutes' => $validated['estimate']['minutes'] ?? 0,
        ];

        // Conditional logic based on the 'choose' field
        // if ($validated['choose'] === 'employees') {
        //     // Ensure 'addorremoveemployee' is set and 'chooseteams' is null
        //     $validated['chooseteams'] = null;
        //     if (empty($validated['addorremoveemployee'])) {
        //         throw new \Exception('Employees must be provided when "choose" is set to employees.');
        //     }
        // } elseif ($validated['choose'] === 'teams') {
        //     // Ensure 'chooseteams' is set and 'addorremoveemployee' is null
        //     $validated['addorremoveemployee'] = null;
        //     if (empty($validated['chooseteams'])) {
        //         throw new \Exception('Teams must be provided when "choose" is set to teams.');
        //     }
        // }

        // Check if the task status is 'completed' and it was not already 'completed'
        if (isset($validated['status']) && $validated['status'] === 'completed' && $task->status !== 'completed') {
            event(new TaskCompleted($task));
        }

        // Update the task
        $task->update($validated);



        // Prepare Crmentity update data
        $crmentityData = [
            'label' => $validated['title'],
            // You can include other fields if needed
        ];

        // Update the corresponding Crmentity record
        $crmentity = Crmentity::where('crmid', $id)->first();

        if ($crmentity) {
            // Update existing Crmentity record
            $crmentity->update($crmentityData);
        } else {
            // Create a new Crmentity record if it does not exist
            $crmentity = new Crmentity();
            $crmentity->crmid = $id; // Use an appropriate unique identifier
            $crmentity->label = $validated['title'];
            $crmentity->save();
        }

        // Commit transaction
        DB::commit();

        return response()->json([
            'message' => 'Task and Crmentity updated successfully',
            'task' => $task,
            'crmentity' => $crmentity,
        ], 200);

    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'status' => 422,
            'message' => 'Validation failed',
            //'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update task and Crmentity: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
        return response()->json([
            'error' => 'Failed to update task and Crmentity',
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function destroy($id)
{
    try {
        $task = Tasks::findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => 500, 'message' => 'Failed to delete task', 'error' => $e->getMessage()], 500);
    }
}   
    }



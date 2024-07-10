<?php

namespace App\Http\Controllers;
// use App\Models\Invoices;

use App\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Crmentity;
use League\Config\Exception\ValidationException;


class TasksController extends Controller
{
    //
    public function index()
    {
        
        $tasks = Tasks::all();

        if($tasks->count() > 0){

            return response()->json([

                'status' => 200,
                'tasks' => $tasks 
            ], 200);
        }else{
            return response()->json([

                'status' => 404,
                'message' => 'No Data Found'
            ], 404);

        }
    }
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the request data
        $validated = $request->validate([
            'tasksnumber' => 'nullable|integer',
            'projects' => 'nullable|string',
            'status' => 'nullable|string',
            'choose' => 'in:employees,teams',
            'addorremoveemployee' => 'nullable|string',
            'title' => 'required|string',
            'priority' => 'nullable|string',
            'size' => 'nullable|string',
            'tags' => 'nullable|array',
            'duedate' => 'nullable|date',
            'estimate' => 'nullable|array',
            'estimate.days' => 'nullable|integer',
            'estimate.hours' => 'nullable|integer',
            'estimate.minutes' => 'nullable|integer',
            'description' => 'nullable|string',
            // 'orgid' => 'integer',
        ]);

        // Handle estimate
        $validated['estimate'] = [
            'days' => $validated['estimate']['days'] ?? 0,
            'hours' => $validated['estimate']['hours'] ?? 0,
            'minutes' => $validated['estimate']['minutes'] ?? 0,
        ];

        // Retrieve or create a new Crmentity record
        $defaultCrmentity = Crmentity::where('setype', 'Estimates')->first();

        if (!$defaultCrmentity) {
            // Log an error if default Crmentity not found
            Log::error('Default Crmentity for Tasks not found');
            throw new \Exception('Default Crmentity not found');
        }

        // Create a new Crmentity record with a new crmid
        $newCrmentity = new Crmentity();
        $newCrmentity->crmid = Crmentity::max('crmid') + 1;
        $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid ?? 0; // Replace with appropriate default
        $newCrmentity->smownerid = $defaultCrmentity->smownerid ?? 0; // Replace with appropriate default
        $newCrmentity->setype = 'Tasks';
        $newCrmentity->description = $defaultCrmentity->description ?? '';
        $newCrmentity->createdtime = now();
        $newCrmentity->modifiedtime = now();
        $newCrmentity->viewedtime = now();
        $newCrmentity->status = $defaultCrmentity->status ?? '';
        $newCrmentity->version = $defaultCrmentity->version ?? 0;
        $newCrmentity->presence = $defaultCrmentity->presence ?? 0;
        $newCrmentity->deleted = $defaultCrmentity->deleted ?? 0;
        $newCrmentity->smgroupid = $defaultCrmentity->smgroupid ?? 0;
        $newCrmentity->source = $defaultCrmentity->source ?? '';
        $newCrmentity->label = $validated['title'];
        $newCrmentity->save();

        // Set the new crmid as the task ID
        $validated['id'] = $newCrmentity->crmid;

        // Create the task entry
        $task = Tasks::create($validated);

        DB::commit();

        return response()->json(['message' => 'Task created successfully', 'task' => $task], 201);

    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'status' => 500,
            'message' => 'Estimate addition failed',
            'error' => $e->getMessage(),
        ], 500);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create task: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
        return response()->json(['error' => 'Failed to create task: ' . $e->getMessage()], 500);
    }
}

     
    }



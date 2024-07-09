<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Crmentity;

class ProjectController extends Controller
{
    //
    public function index()
    {   
        try {
            // Retrieve all employees
            $projects = Project::all();
            return response()->json($projects);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve projects.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    DB::beginTransaction();
    
    try {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // Add more validation rules as needed
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Retrieve or create a new Crmentity record
        $defaultCrmentity = Crmentity::where('setype', 'Invoices')->first();

        if (!$defaultCrmentity) {
            // Log an error if default Crmentity not found
            Log::error('Default Crmentity for Projects not found');
            throw new \Exception('Default Crmentity not found');
        }

        // Create a new Crmentity record with a new crmid
        $newCrmentity = new Crmentity();
        $newCrmentity->crmid = Crmentity::max('crmid') + 1;
        $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid ?? 0; // Replace with appropriate default
        $newCrmentity->smownerid = $defaultCrmentity->smownerid ?? 0; // Replace with appropriate default
        $newCrmentity->setype = 'Projects';
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
        $newCrmentity->label = $request->projects;
        $newCrmentity->save();

        // Set the new crmid as the project ID
        $projectData = $request->all();
        $projectData['id'] = $newCrmentity->crmid;

        // Create the project entry
        $project = Project::create($projectData);

        DB::commit();

        return response()->json(['message' => 'Project created successfully', 'project' => $project], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create project: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create project: ' . $e->getMessage()], 500);
    }
}

}

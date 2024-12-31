<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Projects;
use App\Models\Crmentity;
use Illuminate\Support\Facades\DB;
use App\Models\Tags;

class ProjectsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $projects = Projects::select('id', 'project_name', 'add_or_remove_employees','add_or_remove_teams')
            ->paginate($perPage);
            $formattedprojects = [];
            foreach ($projects as $project) {
                $formattedprojects[] = [
                    'id' => $project->id,
                    'project_name' => $project->project_name,
                    'add_or_remove_employees' =>$project->add_or_remove_employees,
                    'add_or_remove_teams'=>$project->add_or_remove_teams,
                ];
            }
            return response()->json([
                    'status' => 200,
                    'projects' => $formattedprojects,
                    'pagination' => [
                    'total' => $projects->total(),
                    'title' => 'Projects',
                    'per_page' => $projects->perPage(),
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem(),
                ],
            ], 200);

        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve projects: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve projects',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projects::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the request data
        $validatedData = $request->validate([
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'project_name' => 'required|string|max:255',
            'code' => 'nullable|string',
            'project_url' => 'nullable|string',
            'owner' => 'nullable|string',
            'clients' => 'nullable|exists:jo_clients,id',
            'add_or_remove_employees' => 'nullable|array',
            'add_or_remove_employees.*'=>'exists:jo_manage_employees,id',
            'add_or_remove_teams' => 'nullable|array',
            'add_or_remove_teams.*'=>'exists:jo_manage_employees,id',
            'project_start_date' => 'nullable|date',
            'project_end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'billing' => 'nullable|string',
            'currency' => 'nullable|string',
            'type' => 'nullable|string',
            'cost' => 'nullable|integer',
            'open_source' => 'nullable|boolean',
            'open_source_url' => 'nullable|string',
            'color' => 'nullable|string',
            'task_view_mode' => 'nullable|string',
            'public' => 'nullable|boolean',
            'billable' => 'nullable|boolean',
        ]);

        // Handle image upload if an image is provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName); // Move the file to public/images directory
            $validatedData['image'] = 'images/' . $imageName; // Store relative path
        }

        // Create Crmentity record via CrmentityController
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Projects', $validatedData['project_name']);

        // Add crmid to validated data
        $validatedData['id'] = $crmid;

        // Create the project entry
        $project = Projects::create($validatedData);

        DB::commit();

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project
        ], 201);

    } catch (ValidationException $e) {
        DB::rollBack();
        Log::error('Validation failed while creating project: ' . $e->getMessage());
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Failed to create project: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
        return response()->json(['error' => 'Failed to create project: ' . $e->getMessage()], 500);
    }
}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $projects = Projects::findOrFail($id);
            return response()->json($projects);
        //  } catch (ModelNotFoundException $e) {
            // return response()->json(['message' => 'tasks not found'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'project_name'=>'required|string',
                'code'=>'nullable|string',
                'project_url'=>'nullable|string',
                'owner'=>'nullable|string',
                'clients' => 'nullable|exists:jo_clients,id',
                'add_or_remove_employees' => 'nullable|array',
                'add_or_remove_employees.*'=>'exists:jo_manage_employees,id',
                'add_or_remove_teams' => 'nullable|array',
                'add_or_remove_teams.*'=>'exists:jo_manage_employees,id',
                'project_start_date'=>'nullable|date',
                'project_end_date'=>'nullable|date',
                'description'=>'nullable|string',
                'tags' => 'nullable|array|max:5000',
                'billing'=>'nullable|string',
                'currency'=>'nullable|string',
                'type'=>'nullable|string',
                'cost'=>'nullable|integer',
                'open_source'=>'nullable|boolean',
                'color'=>'nullable|string',
                'task_view_mode'=>'nullable|string',
                'public'=>'nullable|boolean',
                'billable'=>'nullable|boolean',
            ]);
            $projects = Projects::findOrFail($id); // Retrieve the task instance
            $projects->update($validated); // Update the instance with the validated data
            return response()->json($projects, 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        {

            try {
                $projects = Projects::findOrFail($id); // Ensure you use the correct model
                $projects->delete();
                return response()->json(['message' => 'Projects deleted successfully'], 200);
            } catch (Exception $e) {
                return response()->json(['message' => 'Failed to delete Projects', 'error' => $e->getMessage()], 500);
            }
        }

    }
}

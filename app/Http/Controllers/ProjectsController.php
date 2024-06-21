<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Projects;
use App\Models\Tags;

class ProjectsController extends Controller
{
    public function index()
    {
        try {
            $projects = Projects::all();
            return response()->json($projects, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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
        try {
            $validated = $request->validate([
                'image'=>'nullable|image',
                'project_name'=>'required|string',
                'code'=>'nullable|string',
                'project_url'=>'nullable|string',
                'owner'=>'nullable|string',
                'clients'=>'nullable|string',
                'add_or_remove_employees'=>'nullable|string',
                'add_or_remove_teams'=>'nullable|string',
                'project_start_date'=>'nullable|date',
                'project_end_date'=>'nullable|date',

                //DESCRIPTION

                'description'=>'nullable|string',
                'tags' => 'nullable|array',
                'tags.*.tags_name' => 'exists:jo_tags,tags_name',
                'tags.*.tag_color' => 'exists:jo_tags,tag_color',

                //bILLING

                'billing'=>'nullable|string',
                'currency'=>'nullable|string',
                //BUDGET
                'type'=>'nullable|string',
                'cost'=>'nullable|integer',

                //open source

                'open_source'=>'nullable|boolean',
                'open_source_url'=>'nullable|string',

                //SETTINGS

                'color'=>'nullable|string',
                'task_view_mode'=>'nullable|string',
                'public'=>'nullable|boolean',
                'billable'=>'nullable|boolean',
                'orgid' => 'integer',
            ]);

            Projects::create($validated);

            return response()->json(['message' => 'Project created successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
    


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('projects::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'image'=>'nullable|image',
                'project_name'=>'required|string',
                'code'=>'nullable|string',
                'project_url'=>'nullable|string',
                'owner'=>'nullable|string',
                'clients'=>'nullable|string',
                'add_or_remove_employees'=>'nullable|string',
                'add_or_remove_teams'=>'nullable|string',
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
                'orgid' => 'integer',
            ]);
            if (isset($validated['tags'])) {
                $tags = [];

                foreach ($validated['tags'] as $tagName) {
                    // Check if the tag exists in the Tags model
                    $tag = Tags::where('tags_name', $tagName)->first();

                    if ($tag) {
                        // If the tag exists, add it to the array of tags
                        $tags[] = $tag->tags_name;
                    } else {
                        // Handle the case when the tag doesn't exist
                        // For example, log the missing tag and continue
                        Log::warning("Tag '$tagName' does not exist in the 'Tags' table");
                    }
                }
                $validated['tags'] = json_encode($tags);
            }
            $projects = Projects::findOrFail($id); // Retrieve the task instance
            $projects->update($validated); // Update the instance with the validated data
            return response()->json($projects, 200);
        } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to delete Projects', 'error' => $e->getMessage()], 500);
            }
        }
    
    }
}
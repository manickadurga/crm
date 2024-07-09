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
        DB::beginTransaction();
    
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'project_name' => 'required|string|max:255',
                'code' => 'nullable|string',
                'project_url' => 'nullable|string',
                'owner' => 'nullable|string',
                'clients' => 'nullable|string',
                'add_or_remove_employees' => 'nullable|string',
                'add_or_remove_teams' => 'nullable|string',
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
                'orgid' => 'integer',
            ]);
    
            // Handle image upload if an image is provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName); // Move the file to public/images directory
                $validatedData['image'] = 'images/' . $imageName; // Store relative path
            } else {
                // If no image is provided, set a default value or null as needed
                // $validatedData['image'] = null; // or handle accordingly
            }
    
            // Handle tags
            if (isset($validatedData['tags'])) {
                $tags = [];
                foreach ($validatedData['tags'] as $id) {
                    $tag = Tags::find($id);
                    if ($tag) {
                        $tags[] = [
                            'tags_name' => $tag->tags_name,
                            'tag_color' => $tag->tag_color,
                        ];
                    } else {
                        throw ValidationException::withMessages(['tags' => "Tag with ID '$id' not found"]);
                    }
                }
                $validatedData['tags'] = json_encode($tags);
            }
    
            // Retrieve or create a new Crmentity record
            $defaultCrmentity = Crmentity::where('setype', 'Customers')->first();
    
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
            $newCrmentity->label = $validatedData['project_name'];
            $newCrmentity->save();
    
            // Set the new crmid as the project ID
            $validatedData['id'] = $newCrmentity->crmid;
    
            // Create the project entry
            $project = Projects::create($validatedData);
    
            DB::commit();
    
            return response()->json(['message' => 'Project created successfully', 'project' => $project], 201);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation failed while creating project: ' . $e->getMessage());
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
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
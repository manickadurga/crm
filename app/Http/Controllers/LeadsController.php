<?php

namespace App\Http\Controllers;
use App\Models\Leads;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tags;
use Illuminate\Validation\ValidationException;


class LeadsController extends Controller
{
    //
    public function index()
    {
        try {
            $leads = Leads::all();
            return response()->json($leads);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve leads: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve leads'], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'name' => 'required|string',
                'primary_email' => 'nullable|string',
                'primary_phone' => 'nullable|string',
                'website' => 'nullable|string',
                'fax' => 'nullable|string',
                'fiscal_information' => 'nullable|string',
                'projects' => 'nullable|array',
                'contact_type' => 'nullable|string',
                'tags' => 'nullable|array',
                'location' => 'nullable|array',
                'type' => 'nullable|string',
                'type_suffix' => 'nullable|numeric',
                'org_id' => 'nullable|numeric'
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
    
            $data = $validator->validated();
    
            if (isset($data['tags'])) {
                $tags = [];
    
                foreach ($data['tags'] as $tagName) {
                    // Check if the tag exists in the Tags model
                    $tag = Tags::where('tags_name', $tagName)->first();
    
                    if ($tag) {
                        // If the tag exists, add it to the array of tags
                        $tags[] = $tag->tags_name;
                    } else {
                        // If the tag doesn't exist, throw a validation exception
                        throw ValidationException::withMessages(['tags' => "Tag '$tagName' does not exist in the 'jo_tags' table"]);
                    }
                }
    
                // Convert the tags array to JSON
                $data['tags'] = json_encode($tags);
            }
    
            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('images', 'public'); // Store the image in the 'public/images' directory
                $data['image_path'] = $path; // Assuming you have an 'image_path' column in your leads table
            }
    
            Leads::create($data);
    
            return response()->json(['message' => 'Leads created successfully'], 201);
        } catch (ValidationException $e) {
            // Return validation error response
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create lead: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create lead: ' . $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request, int $id)
{
    try {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string',
            'primary_email' => 'nullable|string',
            'primary_phone' => 'nullable|string',
            'website' => 'nullable|string',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'projects' => 'nullable|array',
            'contact_type' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'location' => 'nullable|array',
            'type' => 'nullable|string',
            'type_suffix' => 'nullable|numeric',
            'org_id' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        if (isset($data['tags'])) {
            $tags = [];

            foreach ($data['tags'] as $tagName) {
                // Check if the tag exists in the Tags model
                $tag = Tags::where('tags_name', $tagName)->first();

                if ($tag) {
                    // If the tag exists, add it to the array of tags
                    $tags[] = $tag->tags_name;
                } else {
                    // If the tag doesn't exist, throw a validation exception
                    throw ValidationException::withMessages(['tags' => "Tag '$tagName' does not exist in the 'jo_tags' table"]);
                }
            }

            // Convert the tags array to JSON
            $data['tags'] = json_encode($tags);
        }

        // Find the lead by ID, or throw a ModelNotFoundException if not found
        $lead = Leads::findOrFail($id);

        // Update the lead fields with the validated data
        $lead->fill($data);

        // Handle the optional fields that are arrays
        if ($request->has('projects')) {
            $lead->projects = $request->input('projects');
        }
        if ($request->has('tags')) {
            $lead->tags = $request->input('tags');
        }
        if ($request->has('location')) {
            $lead->location = $request->input('location');
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('images', 'public'); // Store the image in the 'public/images' directory
            $lead->image_path = $path; // Assuming you have an 'image_path' column in your leads table
        }

        // Save the updated lead
        $lead->save();

        // Return a JSON response indicating successful update
        return response()->json([
            'status' => 200,
            'message' => 'Lead updated successfully',
            'lead' => $lead
        ], 200);
    } catch (ValidationException $e) {
        // Return validation error response
        return response()->json(['errors' => $e->validator->errors()], 422);
    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Lead not found'], 404);
    } catch (\Exception $e) {
        Log::error('Failed to update lead: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update lead: ' . $e->getMessage()], 500);
    }
}


    public function show($id)
    {
        try {
            $leads = Leads::findOrFail($id);
            return response()->json($leads);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Leads not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $leads = Leads::findOrFail($id);
            $leads->delete();
            return response()->json(['message' => 'Leads deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Leads not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete Leads', 'message' => $e->getMessage()], 500);
        }
    }

}
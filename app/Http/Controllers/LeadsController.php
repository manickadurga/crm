<?php

namespace App\Http\Controllers;
use App\Models\Leads;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


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
                'image'=>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'name'=>'required|string',
                'primary_email' => 'nullable|string',
                'primary_phone' => 'nullable|string',
                'website'=>'nullable|string',
                'fax'=>'nullable|string',
                'fiscal_information' => 'nullable|string',
                'projects'=>'nullable|array',
                'contact_type'=>'nullable|string',
                'tags'=>'nullable|array',
                'location'=>'nullable|array',
                'type'=>'nullable|string',
                'type_suffix'=>'nullable|numeric',
                'org_id'=>'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Create estimate
            Leads::create($request->all());

            return response()->json(['message' => 'Leads created successfully'], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create estimate: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create Leads: ' . $e->getMessage()], 500);
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
            'location' => 'nullable|array',
            'type' => 'nullable|string',
            'type_suffix' => 'nullable|numeric',
            'org_id' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        // Find the lead by ID, or throw a ModelNotFoundException if not found
        $lead = Leads::findOrFail($id);

        // Update the lead fields with the validated data
        $lead->fill($request->all());

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
            'message' => 'Lead updated successfully'
        ], 200);

    } catch (ModelNotFoundException $ex) {
        return response()->json([
            'status' => 404,
            'message' => 'Lead not found'
        ], 404);

    } catch (\Exception $ex) {
        return response()->json([
            'status' => 500,
            'message' => 'An error occurred while updating the lead',
            'error' => $ex->getMessage()
        ], 500);
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
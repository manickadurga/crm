<?php

// app/Http/Controllers/leadsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\File;
use Exception;
use App\Models\Leads;
use App\Models\Project;
use App\Models\Projects;
use App\Models\Tags;
use App\Models\Crmentity;
use App\Models\SharingAccess;
use Illuminate\Support\Facades\DB;

class LeadsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    try {
        // Set the number of items per page, default is 10
        $perPage = $request->input('per_page', 10);

        // Get paginated leads with specific fields including 'id', 'name', 'primary_phone', 'primary_email', 'projects', 'location'
        $leads = Leads::select('id', 'name', 'primary_phone', 'primary_email', 'projects', 'country','city')
            ->paginate($perPage);

        // Prepare array to hold formatted leads
        $formattedLeads = [];

        // Iterate through each customer to format data
        foreach ($leads as $lead) {
            // Initialize arrays
            $projects = [];
            //$location = [];

            // Handle projects field
            if (!empty($lead->projects)) {
                // Decode projects field if it's a string
                $projectIds = is_string($lead->projects) ? json_decode($lead->projects) : $lead->projects;

                // Fetch project names using project IDs
                $projectNames = Projects::whereIn('id', $projectIds)
                    ->pluck('project_name')
                    ->toArray();

                // Combine project names into a comma-separated string
                $projects = implode(',', $projectNames);
            }

            // Decode location field if it's a string
            // if (!empty($lead->location)) {
            //     $location = json_decode($lead->location, true);
            //     if (!is_array($location)) {
            //         throw new \RuntimeException('Invalid JSON format for location');
            //     }
            //}

            // Build formatted customer array and embed 'id'
            $formattedleads[] = [
                'id' => $lead->id,
                'name' => $lead->name,
                'primary_phone' => $lead->primary_phone,
                'primary_email' => $lead->primary_email,
                'projects' => $projects,
                'country' => $lead->country,
                'city' => $lead->city,
            ];
        }

        // Return JSON response with formatted data and pagination information
        return response()->json([
            'status' => 200,
            'leads' => $formattedleads,
            'pagination' => [
                'total' => $leads->total(),
                'per_page' => $leads->perPage(),
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'from' => $leads->firstItem(),
                'to' => $leads->lastItem(),
            ],
        ], 200);

    } catch (\Exception $e) {
        // Log the error
        Log::error('Failed to retrieve leads: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'status' => 500,
            'message' => 'Failed to retrieve leads',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validatedData = Validator::make($request->all(), [
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string',
            'primary_email' => 'nullable|email',
            'primary_phone' => 'nullable|string',
            'website' => 'nullable|url',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'projects' => 'nullable|array|max:5000',
            'projects.*' => 'exists:jo_projects,id',
            'contact_type' => 'nullable|string|max:5000',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            //'location' => 'nullable|array|max:5000',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'post_code' => 'nullable|string',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'type' => 'nullable|integer',
            'type_suffix' => 'nullable|in:cost,hours',
        ])->validate();

        Log::info('Validated data:', $validatedData);

        // Process image if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = $imageName; // Save $imageName to database
        }

        // Ensure 'location' is stored as JSON
        if (isset($validatedData['location'])) {
            $validatedData['location'] = json_encode($validatedData['location']);
        }

        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Leads', $validatedData['name']);
        
        // Log the crmid
        Log::info('Created Crmentity ID:', ['crmid' => $crmid]);

        if (!$crmid) {
            throw new Exception('Failed to create Crmentity entry.');
        }

        // Create the lead with the crmid
        $validatedData['id'] = $crmid;
        $lead = Leads::create($validatedData);

        // Commit the transaction
        DB::commit();

        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => $lead,
        ], 201);
    } catch (ValidationException $e) {
        DB::rollBack();
        Log::error('Validation error:', $e->validator->errors());
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create lead: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to create lead',
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function show(string $id)
    {
        try {
            $lead = Leads::findOrFail($id);
            $lead->location = is_string($lead->location) ? json_decode($lead->location, true) : [];
            $lead->tags = is_string($lead->tags) ? json_decode($lead->tags, true) : [];
            $lead->projects = is_string($lead->projects) ? json_decode($lead->projects, true) : [];

            return response()->json(['status' => 200, 'lead' => $lead], 200);
        } catch (ModelNotFoundException $e) {
            Log::warning('lead not found: ' . $id);
            return response()->json(['status' => 404, 'message' => 'lead not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve lead details: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to retrieve lead details'], 500);
        }
    }
    public function update(Request $request, string $id)
{
    try {
        // Begin a database transaction
        DB::beginTransaction();

        // Find the lead by ID or fail
        $lead = Leads::findOrFail($id);

        // Log the incoming request data
        Log::info('Request data:', $request->all());

        // Validate the incoming request data
        $validatedData = $request->validate([
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'nullable|string',
            'primary_email' => 'nullable|email',
            'primary_phone' => 'nullable|string',
            'website' => 'nullable|url',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'projects' => 'nullable|array|max:5000',
            'projects.*' => 'exists:projects,id',
            'contact_type' => 'nullable|string|max:5000',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'post_code' => 'nullable|string',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'type' => 'nullable|integer',
            'type_suffix' => 'nullable|in:cost,hours',
        ]);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = $imageName; // Save the image name to the database
        }

        // Update the lead with validated data
        $lead->fill($validatedData);
        $lead->save();

        // Update the related Crmentity record
        $crmentity = Crmentity::where('crmid', $lead->id)->where('setype', 'Leads')->first();
        
        if ($crmentity) {
            // Update the Crmentity record with new data
            $crmentity->update([
                'label' => $validatedData['name'] ?? $crmentity->label,
                //'status' => 'Updated', // or use any specific status logic
            ]);
        } else {
            throw new Exception('Crmentity record not found.');
        }

        // Commit the transaction
        DB::commit();

        return response()->json(['message' => 'Lead and Crmentity updated successfully'], 200);
    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (ModelNotFoundException $e) {
        DB::rollBack();
        return response()->json(['error' => 'Lead not found'], 404);
    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Failed to update lead: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update lead: ' . $e->getMessage()], 500);
    }
}

    public function destroy(string $id)
    {
        try {

            $lead = Leads::findOrFail($id);
            $lead->delete();
            return response()->json(['message' => 'Lead deleted successfully'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Lead not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete lead: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete lead: ' . $e->getMessage()], 500);
        }
    }
    public function search(Request $request)
{
    try {
        $perPage = $request->input('per_page', 10);
        
        // Get all query parameters
        $queryParams = $request->all();

        // Log incoming query parameters
        Log::info('Query parameters:', $queryParams);

        $query = Leads::select('id', 'name', 'primary_phone', 'primary_email', 'projects', 'location', 'website', 'fax', 'fiscal_information', 'contact_type', 'type', 'type_suffix');
        foreach ($queryParams as $key => $value) {
            if (in_array($key, ['name', 'primary_phone', 'primary_email', 'projects', 'location', 'website', 'fax', 'fiscal_information', 'contact_type', 'type', 'type_suffix'])) {
                $query->where($key, 'LIKE', "%{$value}%");
            }
        }

        $leads = $query->paginate($perPage);

        // Log retrieved leads
        Log::info('Retrieved leads:', $leads->toArray());

        $formattedLeads = [];
        foreach ($leads as $lead) {
            $projects = [];
            $location = [];

            if (!empty($lead->projects)) {
                $projectIds = is_string($lead->projects) ? json_decode($lead->projects) : $lead->projects;
                $projectNames = Projects::whereIn('id', $projectIds)
                    ->pluck('project_name')
                    ->toArray();
                $projects = implode(',', $projectNames);
            }

            if (!empty($lead->location)) {
                $location = json_decode($lead->location, true);
                if (!is_array($location)) {
                    throw new \RuntimeException('Invalid JSON format for location');
                }
            }

            $formattedLeads[] = [
                'id' => $lead->id,
                'name' => $lead->name,
                'primary_phone' => $lead->primary_phone,
                'primary_email' => $lead->primary_email,
                'projects' => $projects,
                'country' => $location['country'] ?? null,
                'city' => $location['city'] ?? null,
                'website' => $lead->website,
                'fax' => $lead->fax,
                'fiscal_information' => $lead->fiscal_information,
                'contact_type' => $lead->contact_type,
                'type' => $lead->type,
                'type_suffix' => $lead->type_suffix,
            ];
        }

        return response()->json([
            'status' => 200,
            'leads' => $formattedLeads,
            'pagination' => [
                'total' => $leads->total(),
                'per_page' => $leads->perPage(),
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'from' => $leads->firstItem(),
                'to' => $leads->lastItem(),
            ],
        ], 200);

    } catch (\Exception $e) {
        Log::error('Failed to search leads: ' . $e->getMessage());
        return response()->json([
            'status' => 500,
            'message' => 'Failed to search leads',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
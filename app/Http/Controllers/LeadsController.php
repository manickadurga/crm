<?php

// app/Http/Controllers/CustomersController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\File;
use Exception;
use App\Models\Customers;
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
            // Check if the user has permission to read customers data
            //$this->checkAccessPermissions('Customers', 'Read');

            // Set the number of items per page, default is 10
            $perPage = $request->input('per_page', 10);

            // Get paginated customers with specific fields including 'id'
            $leads = Leads::select('id', 'name', 'primary_phone', 'primary_email', 'projects', 'location')
                ->paginate($perPage);

            // Prepare array to hold formatted customers
            $formattedCustomers = [];

            // Iterate through each customer to format data
            foreach ($leads as $lead) {
                // Ensure projects and location are valid JSON strings before decoding
                $projectsArray = is_string($lead->projects) ? json_decode($lead->projects, true) : [];
                $locationArray = is_string($lead->location) ? json_decode($lead->location, true) : [];

                

                // Build formatted lead array and embed 'id'
                $formattedCustomers[] = [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'primary_phone' => $lead->primary_phone,
                    'primary_email' => $lead->primary_email,
                    'projects' => $projectsArray,
                    'country' => $locationArray['country'] ?? null,
                    'city' => $locationArray['city'] ?? null,
                ];
            }

            // Return JSON response with formatted data and pagination information
            return response()->json([
                'status' => 200,
                'leads' => $formattedCustomers,
                'pagination' => [
                    'total' => $leads->total(),
                    'per_page' => $leads->perPage(),
                    'current_page' => $leads->currentPage(),
                    'last_page' => $leads->lastPage(),
                    'from' => $leads->firstItem(),
                    'to' => $leads->lastItem(),
                ],
            ], 200);

        } catch (Exception $e) {
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
                'location' => 'nullable|array|max:5000',
                'location.country' => 'nullable|string',
                'location.city' => 'nullable|string',
                'location.address' => 'nullable|string',
                'location.postal_code' => 'nullable|string',
                'location.longitude' => 'nullable|numeric',
                'location.latitude' => 'nullable|numeric',
                'type' => 'nullable|integer',
                'type_suffix' => 'nullable|in:cost,hours',
            ])->validate();
    
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
    
            // Ensure 'projects' is stored as JSON
            if (isset($validatedData['projects'])) {
                $projects = [];
                foreach ($validatedData['projects'] as $id) {
                    $project = Projects::find($id);
                    if ($project) {
                        $projects[] = $project->project_name;
                    } else {
                        throw new \Exception("Project with ID '$id' not found");
                    }
                }
                $validatedData['projects'] = json_encode($projects);
            }
    
            // Ensure 'tags' is stored as JSON
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
                        throw new \Exception("Tag with ID '$id' not found");
                    }
                }
                $validatedData['tags'] = json_encode($tags);
            }
    
            // Retrieve default values from an existing Crmentity record
            $defaultCrmentity = Crmentity::where('setype', 'Leads')->first();
    
            // Check if defaultCrmentity exists
            if (!$defaultCrmentity) {
                throw new \Exception('Default Crmentity not found');
            }
    
            // Create a new Crmentity record with a new crmid
            $newCrmentity = new Crmentity();
            $newCrmentity->crmid = Crmentity::max('crmid') + 1;
            $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid;
            $newCrmentity->smownerid = $defaultCrmentity->smownerid;
            $newCrmentity->setype = 'Leads';
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
            $newCrmentity->label = $validatedData['name'];
            $newCrmentity->save();
    
            // Set the new crmid as the lead ID
            $validatedData['id'] = $newCrmentity->crmid;
    
            // Create a new lead record with the crmid
            $lead = Leads::create($validatedData);
    
            DB::commit();
    
            // Return success response
            return response()->json([
                'message' => 'Lead created successfully',
                'lead' => $lead,
            ], 201);
    
        } catch (ValidationException $e) {
            DB::rollBack();
    
            // Return validation error response
            return response()->json(['error' => $e->validator->errors()], 422);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            // Handle any exceptions or errors
            return response()->json([
                'error' => 'Failed to create lead',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Check if the user has permission to read customers data
            //$this->checkAccessPermissions('Customers', 'Read');

            $lead = Leads::findOrFail($id);

            // Decode JSON fields if they are stored as JSON strings
            $lead->location = is_string($lead->location) ? json_decode($lead->location, true) : [];
            $lead->tags = is_string($lead->tags) ? json_decode($lead->tags, true) : [];
            $lead->projects = is_string($lead->projects) ? json_decode($lead->projects, true) : [];

            return response()->json(['status' => 200, 'customer' => $lead], 200);
        } catch (ModelNotFoundException $e) {
            Log::warning('lead not found: ' . $id);
            return response()->json(['status' => 404, 'message' => 'lead not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve lead details: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to retrieve lead details'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Check if the user has permission to write customers data
            //$this->checkAccessPermissions('Customers', 'Write');

            $lead = Leads::findOrFail($id);

            // Log incoming request data
            Log::info('Request data:', $request->all());

            // Validate the incoming request data
            $validatedData = $request->validate([
                'image' => 'nullable|string', // Expecting a base64 string
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
                'location' => 'nullable|array|max:5000',
                'location.country' => 'nullable|string',
                'location.city' => 'nullable|string',
                'location.address' => 'nullable|string',
                'location.postal_code' => 'nullable|string',
                'location.longitude' => 'nullable|numeric',
                'location.latitude' => 'nullable|numeric',
                'type' => 'nullable|integer',
                'type_suffix' => 'nullable|in:cost,hours',
            ]);

            // Process image if provided
            if ($request->has('image')) {
                // Save or update the image in the database or storage
            }

            // Update customer fields based on validated data
            $lead->fill($validatedData);
            $lead->save();

            // Return success response
            return response()->json(['message' => 'Lead updated successfully'], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Lead not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update lead: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update lead: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Check if the user has permission to delete customers data
            //$this->checkAccessPermissions('Customers', 'Write');

            $lead = Leads::findOrFail($id);
            $lead->delete();

            // Return success response
            return response()->json(['message' => 'Lead deleted successfully'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Lead not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete lead: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete lead: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check access permissions for the specified module and required permission.
     */
   
}
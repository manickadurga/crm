<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Clients;
use App\Models\Projects;
use App\Models\Tags;
use App\Models\Crmentity;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\ValidationException;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Set the number of items per page, default is 10
            $perPage = $request->input('per_page', 10);

            // Get paginated clients
            $clients = Clients::select('name','primary phone','primary email','country','city')
            ->paginate($perPage);

            // Return JSON response with clients and pagination information
            return response()->json([
                'status' => 200,
                'clients' => $clients->items(),
                'pagination' => [
                    'total' => $clients->total(),
                    'per_page' => $clients->perPage(),
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'from' => $clients->firstItem(),
                    'to' => $clients->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to retrieve clients: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve clients',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
   

     public function store(Request $request): JsonResponse
     {
         try {
             // Validate the incoming request data
             $validatedData = $request->validate([
                 'image' => 'nullable|string',
                 'name' => 'required|string|max:255',
                 'primary_email' => 'nullable|string|max:255',
                 'primary_phone' => 'nullable|string|max:255',
                 'website' => 'nullable|string|max:255',
                 'fax' => 'nullable|string|max:255',
                 'fiscal_information' => 'nullable|string',
                 'projects.*' => 'exists:jo_projects,id', // Validate project IDs
                 'contact_type' => 'nullable|string|max:255',
                 'tags.*' => 'exists:jo_tags,id', // Validate tag IDs
                 'location' => 'nullable|array',
                 'type' => 'nullable|string|max:255',
                 'type_suffix' => 'nullable|integer',
             ]);
     
             // Fetch project names if provided
             if (isset($validatedData['projects'])) {
                 $projectNames = Projects::whereIn('id', $validatedData['projects'])->pluck('project_name')->toArray();
                 $validatedData['projects'] = $projectNames;
             }
     
             // Fetch tag names if provided
             if (isset($validatedData['tags'])) {
                 $tagNames = Tags::whereIn('id', $validatedData['tags'])->pluck('tags_name')->toArray();
                 $validatedData['tags'] = $tagNames;
             }
     
             // Create a new client record
             $client = Clients::create($validatedData);
     
             // Create a Crmentity record for the new client
            // Create a Crmentity record for the new client
            $crmentity = new Crmentity();
            $crmentity->crmid = $client->id; // Assuming 'id' is the primary key of Clients table
            $crmentity->smcreatorid = auth()->id() ?? 1; // Fallback to default user ID if auth()->id() is null
            $crmentity->smownerid = auth()->id() ?? 1;   // Fallback to default user ID if auth()->id() is null
            $crmentity->setype = 'Clients';              // Set the entity type
            $crmentity->description = $client->name;     // Set description from client name
            $crmentity->createdtime = now();
            $crmentity->modifiedtime = now();
            $crmentity->viewedtime = now();
            $crmentity->status = 'Active';               // Set default status
            $crmentity->version = 1;                     // Set default version
            $crmentity->presence = 1;                    // Set default presence
            $crmentity->deleted = 0;                     // Not deleted
            $crmentity->smgroupid = 0;                   // Set group ID if applicable
            $crmentity->source = 'Web';                  // Set source if applicable
            $crmentity->label = 'Client Record';         // Set label if applicable
            $crmentity->save();

             // Return success response with the created client data
             return response()->json($client, 201);
     
         } catch (\Exception $e) {
             // Log the error
             Log::error('Failed to create client: ' . $e->getMessage());
     
             // Return error response
             return response()->json([
                 'status' => 500,
                 'message' => 'Failed to create client',
                 'error' => $e->getMessage(),
             ], 500);
         }
     }
     
    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $client = Clients::findOrFail($id);
            return response()->json($client);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to retrieve client: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 404,
                'message' => 'Client not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'image' => 'nullable|string',
                'name' => 'required|string|max:255',
                'primary_email' => 'nullable|string|max:255',
                'primary_phone' => 'nullable|string|max:255',
                'website' => 'nullable|string|max:255',
                'fax' => 'nullable|string|max:255',
                'fiscal_information' => 'nullable|string',
                'projects' => 'nullable|array',
                'projects.*' => 'exists:jo_projects,id', // Validate project IDs
                'contact_type' => 'nullable|string|max:255',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id', // Validate tag IDs
                'location' => 'nullable|array',
                'type' => 'nullable|string|max:255',
                'type_suffix' => 'nullable|integer',
            ]);
    
            // Fetch and store project names
            if (isset($validatedData['projects'])) {
                $projectNames = Projects::whereIn('id', $validatedData['projects'])->pluck('name')->toArray();
                $validatedData['projects'] = $projectNames;
            }
    
            // Fetch and store tag names
            if (isset($validatedData['tags'])) {
                $tagNames = Tags::whereIn('id', $validatedData['tags'])->pluck('name')->toArray();
                $validatedData['tags'] = $tagNames;
            }
    
            // Update client record
            $client = Clients::findOrFail($id);
            $client->update($validatedData);
    
            return response()->json($client);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to update client: ' . $e->getMessage());
    
            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to update client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $client = Clients::findOrFail($id);
            $client->delete();
            return response()->json(['message' => 'Client deleted successfully'], 200);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to delete client: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to delete client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function search(Request $request): JsonResponse
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'name' => 'nullable|string',
                'primary_email' => 'nullable|string',
                'primary_phone' => 'nullable|string',
                'website' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);
    
            // Initialize the query builder
            $query = Clients::query();
    
            // Apply search filters
            foreach ($validatedData as $key => $value) {
                if ($value !== null && in_array($key, ['name', 'primary_email', 'primary_phone', 'website'])) {
                    $query->where($key, 'like', '%' . $value . '%');
                }
            }
    
            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // default per_page value
            $clients = $query->paginate($perPage);
    
            // Check if any clients found
            if ($clients->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'clients' => $clients->items(),
                'pagination' => [
                    'total' => $clients->total(),
                    'per_page' => $clients->perPage(),
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'from' => $clients->firstItem(),
                    'to' => $clients->lastItem(),
                ],
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to search clients: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search clients: ' . $e->getMessage()], 500);
        }
    }
}    

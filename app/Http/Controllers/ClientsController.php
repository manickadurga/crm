<?php

namespace App\Http\Controllers;

use App\Events\ContactCreated;
use App\Events\ContactUpdated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Clients;
use App\Models\Projects;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Crmentity;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ClientsController extends Controller
{

    public function index(Request $request)
{
    try {
        $perPage = $request->input('per_page', 10);
        $clients = Clients::select('id', 'name', 'primary_phone', 'primary_email', 'projects','country','city')
            ->paginate($perPage);
        $formattedClients = [];
        foreach ($clients as $client) {
            $projects = [];
            //$location = [];
            if (!empty($client->projects)) {
                $projectIds = is_string($client->projects) ? json_decode($client->projects) : $client->projects;
                $projectNames = Projects::whereIn('id', $projectIds)
                    ->pluck('project_name')
                    ->toArray();
                $projects = implode(',', $projectNames);
            }
            // if (!empty($client->location) && is_string($client->location)) {
            //     $location = json_decode($client->location, true);
            //     if (!is_array($location)) {
            //         throw new \RuntimeException('Invalid JSON format for location');
            //     }
            // }
            $formattedClients[] = [
                'id' => $client->id,
                'name' => $client->name,
                'primary_phone' => $client->primary_phone,
                'primary_email' => $client->primary_email,
                'projects' => $projects,
                'country' => $client->country,
                'city' => $client->city,
            ];
        }
        return response()->json([
            'status' => 200,
            'client' => $formattedClients,
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
        Log::error('Failed to retrieve client: ' . $e->getMessage());
        return response()->json([
            'status' => 500,
            'message' => 'Failed to retrieve client',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function store(Request $request)
{
    try {
        // Validate the incoming request data
        $validatedData = Validator::make($request->all(), [
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'primary_email' => 'nullable|string|email|max:255',
            'primary_phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:20',
            'fiscal_information' => 'nullable|string',
            'projects' => 'nullable|json',
            'contact_type' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'post_code' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'type' => 'nullable|integer',
            'type_suffix' => 'nullable|in:cost,hours',
        ])->validate();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = $imageName;
        }

        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Clients', $validatedData['name']);

        // Create the customer with the crmid
        $validatedData['id'] = $crmid; // Add crmid to customer data
        $client = Clients::create($validatedData);
        event(new ContactCreated($client));
        return response()->json([
            'message' => 'Client created successfully',
            'client' => $client,
        ], 201);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to create client',
            'message' => $e->getMessage(),
        ], 500);
    }
}


    public function show($id): JsonResponse
    {
        try {
            $client = Clients::findOrFail($id);
            return response()->json($client);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve client: ' . $e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Client not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    public function update(Request $request, $id): JsonResponse
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
            'projects' => 'nullable|array',
            'projects.*' => 'exists:jo_projects,id', // Validate project IDs
            'contact_type' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id', // Validate tag IDs
            'location' => 'nullable|array',
            'location.country' => 'nullable|string',
            'location.city' => 'nullable|string',
            'location.address' => 'nullable|string',
            'location.postal_code' => 'nullable|string',
            'location.longitude' => 'nullable|numeric',
            'location.latitude' => 'nullable|numeric',
            'type' => 'nullable|string|max:255',
            'type_suffix' => 'nullable|integer',
        ]);

        // Update the client record
        $client = Clients::findOrFail($id);
        $client->update($validatedData);
        event(new ContactUpdated($client));

        // Update the associated Crmentity record
        $crmentity = Crmentity::where('crmid', $client->id)->firstOrFail();
        $crmentity->update([
            'label' => $validatedData['name'],
            'modifiedby' => auth()->id(), // Assuming you want to set the current user's ID as the modifier
            'modifiedtime' => now(),
            // Add other fields to update in Crmentity as necessary
            'status' => 'Updated', // Example status update, customize as needed
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Client and Crmentity updated successfully',
            'client' => $client,
            'crmentity' => $crmentity,
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 422,
            'message' => 'Validation Error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Failed to update client: ' . $e->getMessage());
        return response()->json([
            'status' => 500,
            'message' => 'Failed to update client and Crmentity',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function destroy($id): JsonResponse
    {
        try {
            $client = Clients::findOrFail($id);
            $client->delete();
            return response()->json(['message' => 'Client deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete client: ' . $e->getMessage());
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
                'location.country' => 'nullable|string',
                'location.city' => 'nullable|string',
                'location.address' => 'nullable|string',
                'location.postal_code' => 'nullable|string',
                'location.longitude' => 'nullable|numeric',
                'location.latitude' => 'nullable|numeric',
                'per_page' => 'nullable|integer|min:1', 
                // Add validation for per_page
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
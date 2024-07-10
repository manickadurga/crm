<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Clients;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use Exception;


class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Set the number of items per page, default is 10
            $perPage = $request->input('per_page', 10);

            // Get paginated organizations
            $organizations = Organization::select('organization name','currency')->paginate($perPage);

            // Return JSON response with organizations and pagination information
            return response()->json([
                    'status' => 200,
                    'organizations' => $organizations->items(),
                    'pagination' => [
                    'total' => $organizations->total(),
                    'per_page' => $organizations->perPage(),
                    'current_page' => $organizations->currentPage(),
                    'last_page' => $organizations->lastPage(),
                    'from' => $organizations->firstItem(),
                    'to' => $organizations->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to retrieve organizations: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve organizations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $organization = Organization::find($id);
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }
            return response()->json($organization);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'image' => 'nullable|image',
                'organization_name' => 'required|string|max:255',
                'currency' => 'nullable|string|max:255',
                'official_name' => 'nullable|string|max:255',
                'tax_id' => 'nullable|string|max:255',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'location' => 'nullable|array',
                'employee_bonus_type' => 'nullable|string|max:255',
                'choose_time_zone' => 'nullable|string|max:255',
                'start_week_on' => 'nullable|string|max:255',
                'default_date_type' => 'nullable|string|max:255',
                'regions' => 'nullable|string|max:255',
                'select_number_format' => 'nullable|string|max:255',
                'date_format' => 'nullable|string|max:255',
                'fiscal_year_start_date' => 'nullable|string|max:255',
                'fiscal_year_end_date' => 'nullable|string|max:255',
                'enable_disable_invites' => 'nullable|string|max:255',
                'invite_expiry_period' => 'nullable|string|max:255',
            ]);

            $tagsIds = $validatedData['tags'] ?? [];
            $tagsNames = DB::table('jo_tags')
                ->whereIn('id', $tagsIds)
                ->pluck('tags_name')
                ->toArray();
            $validatedData['tags'] = json_encode($tagsNames);

            $organization = Organization::create($validatedData);
            return response()->json($organization, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $organization = Organization::find($id);
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $validatedData = $request->validate([
                'image' => 'nullable|image',
                'organization_name' => 'required|string|max:255',
                'currency' => 'nullable|string|max:255',
                'official_name' => 'nullable|string|max:255',
                'tax_id' => 'nullable|string|max:255',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'location' => 'nullable|array',
                'employee_bonus_type' => 'nullable|string|max:255',
                'choose_time_zone' => 'nullable|string|max:255',
                'start_week_on' => 'nullable|string|max:255',
                'default_date_type' => 'nullable|string|max:255',
                'regions' => 'nullable|string|max:255',
                'select_number_format' => 'nullable|string|max:255',
                'date_format' => 'nullable|string|max:255',
                'fiscal_year_start_date' => 'nullable|string|max:255',
                'fiscal_year_end_date' => 'nullable|string|max:255',
                'enable_disable_invites' => 'nullable|string|max:255',
                'invite_expiry_period' => 'nullable|string|max:255',
            ]);

            $tagsIds = $validatedData['tags'] ?? [];
            $tagsNames = DB::table('jo_tags')
                ->whereIn('id', $tagsIds)
                ->pluck('tags_name')
                ->toArray();
            $validatedData['tags'] = json_encode($tagsNames);

            $organization->update($validatedData);
            return response()->json($organization);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $organization = Organization::find($id);
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $organization->delete();
            return response()->json(['message' => 'Organization deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function employeeDetails($id)
    {
        try {
            $organization = Organization::findOrFail($id);

            $data = [
                'image' => $organization->image,
                'official_name' => $organization->official_name,
                'employee_bonus_type' => $organization->employee_bonus_type,
                'organization_name' => $organization->organization_name,
                'start_week_on' => $organization->start_week_on,
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getClientData()
    {
        try {
            // Fetch required client data
            $clientsData = Clients::select('id', 'image', 'name', 'primary_email')->get();

            return response()->json([
                'status' => 200,
                'clientsData' => $clientsData
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeClientData()
    {
        try {
            // Fetch client data from the clients table
            $clients = Clients::all();

            // Loop through each client and create a new organization record
            foreach ($clients as $client) {
                // Create a new organization record if it doesn't exist
                $organization = Organization::firstOrNew(['id' => $client->orgid]);

                // Populate organization fields with client data
                $organization->image = $client->image;
                $organization->official_name = $client->name; // Store client name as official_name
                $organization->primary_email = $client->primary_email;

                // Ensure organization_name is populated
                if (empty($organization->organization_name)) {
                    $organization->organization_name = 'Default Organization Name'; // Replace with a sensible default
                }

                // Save the organization record
                $organization->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Client data stored in organization successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   
    
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'organization_name' => 'nullable|string',
                'official_name' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);
    
            // Initialize the query builder
            $query = Organization::query();
    
            // Apply search filters
            foreach ($validatedData as $key => $value) {
                if ($value !== null && in_array($key, ['organization_name', 'official_name'])) {
                    $query->where($key, 'like', '%' . $value . '%');
                }
            }
    
            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // default per_page value
            $organizations = $query->paginate($perPage);
    
            // Check if any organizations found
            if ($organizations->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'organizations' => $organizations->items(),
                'pagination' => [
                    'total' => $organizations->total(),
                    'per_page' => $organizations->perPage(),
                    'current_page' => $organizations->currentPage(),
                    'last_page' => $organizations->lastPage(),
                    'from' => $organizations->firstItem(),
                    'to' => $organizations->lastItem(),
                ],
            ], 200);
    
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search Organizations: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search Organization: ' . $e->getMessage()], 500);
        }
    }
}    
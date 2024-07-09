<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SharingAccess;
use App\Models\Groups;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Query\Expression;



class SharingAccessController extends Controller
{
    public function index()
    {
        try {
            // Retrieve paginated sharing accesses
            $accesses = SharingAccess::paginate(10); // Adjust 10 to the number of accesses per page you want

            // Check if any accesses found
            if ($accesses->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            // Return paginated response
            return response()->json([
                'status' => 200,
                'accesses' => $accesses->items(),
                'pagination' => [
                    'total' => $accesses->total(),
                    'per_page' => $accesses->perPage(),
                    'current_page' => $accesses->currentPage(),
                    'last_page' => $accesses->lastPage(),
                    'from' => $accesses->firstItem(),
                    'to' => $accesses->lastItem(),
                ],
            ], 200);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to fetch sharing accesses: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to fetch sharing accesses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */

     public function store(Request $request)
     {
         try {
             // Validate the incoming request data
             $validatedData = $request->validate([
                 'calendar_of' => 'required|string|max:255',
                 'can_be_accessed_by' => 'required|string|max:255',
                 'with_permissions' => 'required|in:Read,Read and write',
             ]);
 
             // Log the incoming request data
             Log::info('Storing sharing access record:', $validatedData);
 
             // Create SharingAccess record
             $access = SharingAccess::create([
                 'calendar_of' => $validatedData['calendar_of'],
                 'can_be_accessed_by' => $validatedData['can_be_accessed_by'],
                 'with_permissions' => $validatedData['with_permissions'],
             ]);
 
             // Update customers based on permissions
             $this->updateCustomers($validatedData['with_permissions'], $validatedData['calendar_of'], $validatedData['can_be_accessed_by']);
 
             // Return success response
             return response()->json(['message' => 'Sharing access created successfully', 'data' => $access], 201);
         } catch (ValidationException $e) {
             Log::error('Validation error:', ['errors' => $e->validator->errors()]);
             return response()->json(['error' => $e->validator->errors()], 422);
         } catch (\Exception $e) {
             Log::error('Failed to create sharing access record: ' . $e->getMessage());
             return response()->json(['error' => 'Failed to create record: ' . $e->getMessage()], 500);
         }
     }
 
     /**
      * Update customers based on permissions granted.
      */
      private function updateCustomers($permissions, $calendarOf, $canBeAccessedBy)
{
    try {
        // Determine which columns to update based on permissions
        $allowedColumns = [
            'Read' => ['read' => true, 'write' => false], // Update 'read' column for 'Read' permission
            'Read and write' => ['read' => true, 'write' => true], // Update 'read' and 'write' columns for 'Read and write' permission
        ];

        // Determine which columns to update
        $updateData = $allowedColumns[$permissions] ?? [];

        if (empty($updateData)) {
            throw new \Exception('Invalid permission specified.');
        }

        // Log update data
        Log::info('Updating customers with data:', [
            'permissions' => $permissions,
            'calendar_of' => $calendarOf,
            'can_be_accessed_by' => $canBeAccessedBy,
            'update_data' => $updateData
        ]);

        // Update customers based on calendar_of and can_be_accessed_by
        Customers::where('calendar_of', $calendarOf)
                 ->where('can_be_accessed_by', $canBeAccessedBy)
                 ->update($updateData);

        return true;
    } catch (\Exception $e) {
        Log::error('Failed to update customers: ' . $e->getMessage());
        return false; // Return false or handle the error as appropriate for your application
    }
}

     
     private function grantTableAccess($permissions, $calendarOf, $canBeAccessedBy, $tableName)
     {
         try {
             // Fetch customers based on calendar_of and can_be_accessed_by
             $customers = Customers::where('calendar_of', $calendarOf)
                                  ->where('can_be_accessed_by', $canBeAccessedBy)
                                  ->get();
     
             return $customers;
         } catch (\Exception $e) {
             // Handle any exceptions
             Log::error('Failed to fetch customers: ' . $e->getMessage());
             return null; // Return null or handle the error as appropriate for your application
         }
     }
     

private function updateRelatedGroups($calendarOf, $canBeAccessedBy)
{
    // Update group_members in Groups where calendar_of or can_be_accessed_by match
    Groups::where('group_name', $calendarOf)
          ->orWhere('group_name', $canBeAccessedBy)
          ->update(['group_members' => $this->getUpdatedGroupMembers($calendarOf, $canBeAccessedBy)]);
}

private function getUpdatedGroupMembers($calendarOf, $canBeAccessedBy)
{
    $updatedGroupMembers = [];

    // Add members from calendar_of
    $calendarOfGroup = Groups::where('group_name', $calendarOf)->first();
    if ($calendarOfGroup) {
        $updatedGroupMembers = array_merge($updatedGroupMembers, json_decode($calendarOfGroup->group_members, true));
    }

    // Add members from can_be_accessed_by
    $canBeAccessedByGroup = Groups::where('group_name', $canBeAccessedBy)->first();
    if ($canBeAccessedByGroup) {
        $updatedGroupMembers = array_merge($updatedGroupMembers, json_decode($canBeAccessedByGroup->group_members, true));
    }

    // Remove duplicates if necessary
    $updatedGroupMembers = array_unique($updatedGroupMembers);

    return json_encode($updatedGroupMembers);
}

private function getMemberInfo($inputString)
{
    $memberInfo = '';

    // Check if the input string matches a User
    $user = User::where('name', $inputString)->first();
    if ($user) {
        $memberInfo = $user->name;
    }

    // Check if the input string matches a Role
    $role = Role::where('rolename', $inputString)->first();
    if ($role) {
        $memberInfo = $role->rolename;
    }

    // Check if the input string matches a Role and Subordinate
    $roleSubordinate = Role::where('rolename', $inputString)->first();
    if ($roleSubordinate) {
        $memberInfo = $roleSubordinate->rolename;
    }

    // Check if the input string matches a Group
    $group = Groups::where('group_name', $inputString)->first();
    if ($group) {
        $memberInfo = $group->group_name;
    }

    if (!$memberInfo) {
        throw ValidationException::withMessages(["Invalid name provided: '{$inputString}'"]);
    }

    return $memberInfo;
}
public function update(Request $request, $id)
{
    try {
        // Find the SharingAccess record to update
        $access = SharingAccess::findOrFail($id);

        // Validate the incoming request data
        $validatedData = $request->validate([
            'calendar_of' => 'sometimes|required|string|max:255',
            'can_be_accessed_by' => 'sometimes|required|string|max:255',
            'with_permissions' => 'required|in:Read,Read and write',
            'update_data' => 'required|array', // Assuming update_data contains fields to update in Customers table
        ]);

        Log::info('Updating sharing access record:', $request->all());

        // Check if permission allows updating calendar_of and can_be_accessed_by
        if ($validatedData['with_permissions'] === 'Read') {
            // If with_permissions is 'Read', return a JSON response indicating inability to edit
            return response()->json(['error' => 'Unable to edit. Permission denied.'], 403);
        }

        // If with_permissions is 'Read and write', proceed with the update
        $calendarOfData = $validatedData['calendar_of'] ?? $access->calendar_of;
        $canBeAccessedByData = $validatedData['can_be_accessed_by'] ?? $access->can_be_accessed_by;

        // Update the SharingAccess record
        $access->update([
            'calendar_of' => $calendarOfData,
            'can_be_accessed_by' => $canBeAccessedByData,
        ]);

        // Update customers based on permissions
        $updateData = $validatedData['update_data'];
        $this->updateCustomers($validatedData['with_permissions'], $calendarOfData, $canBeAccessedByData, $updateData);

        // Prepare success response with updated data
        $responseData = [
            'message' => 'Sharing access updated successfully',
            'data' => [
                'id' => $access->id,
                'calendar_of' => $calendarOfData,
                'can_be_accessed_by' => $canBeAccessedByData,
                'with_permissions' => $access->with_permissions,
                'created_at' => $access->created_at,
                'updated_at' => $access->updated_at,
            ]
        ];

        return response()->json($responseData);
    } catch (ValidationException $e) {
        Log::error('Validation error:', ['errors' => $e->validator->errors()]);
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Failed to update sharing access record: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update record: ' . $e->getMessage()], 500);
    }
}


    public function show($id)
    {
        try {
            $access = SharingAccess::findOrFail($id);
            return response()->json($access);
        } catch (\Exception $e) {
            Log::error('Failed to fetch jo_sharing_access record: ' . $e->getMessage());
            return response()->json(['error' => 'Record not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $access = SharingAccess::findOrFail($id);
            $access->delete();
            return response()->json(['message' => 'Record deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete jo_sharing_access record: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete record'], 500);
        }
    }
    // Example usage in a controller method
    public function getCustomersBySharingAccessId(Request $request, $sharingAccessId)
    {
        try {
            // Log the sharing access ID being queried
            Log::info('Fetching customers for Sharing Access ID: ' . $sharingAccessId);

            // Retrieve sharing access record
            $sharingAccess = SharingAccess::findOrFail($sharingAccessId);
            
            // Log the retrieved sharing access record
            Log::info('Sharing Access Record: ', $sharingAccess->toArray());

            // Example query to retrieve customers based on sharing access
            $customers = Customers::whereExists(function ($query) use ($sharingAccess) {
                $query->select('id')
                      ->from('jo_sharing_access')
                      ->where('jo_sharing_access.can_be_accessed_by', $sharingAccess->can_be_accessed_by)
                      ->where('jo_sharing_access.id', $sharingAccess->id);
            })->get();

            // Log the customers found
            Log::info('Customers Found: ', $customers->toArray());

            if ($customers->isEmpty()) {
                return response()->json(['status' => 404, 'message' => 'No customers found for sharing access ID ' . $sharingAccessId], 404);
            }

            return response()->json(['status' => 200, 'customers' => $customers]);
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Failed to retrieve customers: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to retrieve customers: ' . $e->getMessage()], 500);
        }
    }
    public function updateCustomersData(Request $request, $sharingAccessId)
    {
        try {
            // Retrieve sharing access record
            $sharingAccess = SharingAccess::findOrFail($sharingAccessId);

            // Check if permissions allow updating data
            if ($sharingAccess->with_permissions !== 'Read and write') {
                return response()->json(['status' => 403, 'message' => 'Permission denied. Cannot update customers.'], 403);
            }

            // Validate the incoming request data
            $validatedData = $request->validate([
                'customers' => 'required|array',
                'customers.*.id' => 'required|exists:jo_customers,id',
                'customers.*.image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'customers.*.name' => 'required|string',
                'customers.*.primary_email' => 'nullable|email',
                'customers.*.primary_phone' => 'nullable|string',
                'customers.*.website' => 'nullable|url',
                'customers.*.fax' => 'nullable|string',
                'customers.*.fiscal_information' => 'nullable|string',
                'customers.*.projects' => 'nullable|array|max:5000',
                'customers.*.projects.*' => 'exists:projects,id',
                'customers.*.contact_type' => 'nullable|string|max:5000',
                'customers.*.tags' => 'nullable|array',
                'customers.*.tags.*' => 'exists:tags,id',
                'customers.*.location' => 'nullable|array|max:5000',
                'customers.*.location.country' => 'nullable|string',
                'customers.*.location.city' => 'nullable|string',
                'customers.*.location.address' => 'nullable|string',
                'customers.*.location.postal_code' => 'nullable|string',
                'customers.*.location.longitude' => 'nullable|numeric',
                'customers.*.location.latitude' => 'nullable|numeric',
                'customers.*.type' => 'nullable|integer',
            ]);

            // Log the update request
            Log::info('Updating customers data:', $validatedData);

            // Update each customer
            foreach ($validatedData['customers'] as $customerData) {
                $customer = Customers::find($customerData['id']);
                if ($customer) {
                    $customer->update($customerData);
                }
            }

            return response()->json(['status' => 200, 'message' => 'Customers updated successfully.']);
        } catch (ValidationException $e) {
            Log::error('Validation error:', ['errors' => $e->validator->errors()]);
            return response()->json(['status' => 422, 'error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update customers: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Failed to update customers: ' . $e->getMessage()], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            // Define the searchable fields
            $searchableFields = [
                'calendar_of',
                'can_be_accessed_by',
                'with_permissions',
            ];

            // Start a query on the SharingAccess model
            $query = SharingAccess::query();

            // Loop through the searchable fields and apply filters
            foreach ($searchableFields as $field) {
                if ($request->has($field)) {
                    $query->where($field, 'like', '%' . $request->input($field) . '%');
                }
            }

            // Paginate the results
            $accesses = $query->paginate(10); // Adjust the number per page as needed

            // Check if any accesses found
            if ($accesses->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            // Return paginated response
            return response()->json([
                'status' => 200,
                'accesses' => $accesses->items(),
                'pagination' => [
                    'total' => $accesses->total(),
                    'per_page' => $accesses->perPage(),
                    'current_page' => $accesses->currentPage(),
                    'last_page' => $accesses->lastPage(),
                    'from' => $accesses->firstItem(),
                    'to' => $accesses->lastItem(),
                ],
            ], 200);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to search sharing accesses: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to search sharing accesses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    }
    





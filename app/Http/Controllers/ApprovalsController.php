<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Crmentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ApprovalsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    try {
        // Get pagination parameters from the request or set defaults
        $perPage = $request->input('per_page', 15); // Default to 15 items per page

        // Fetch paginated approvals with relationships and select specific columns
        $approvals = DB::table('jo_approvals')
            ->select(
                'jo_approvals.name',
                'jo_approvals.min_count',
                'jo_approval_policy.name as approval_policy_name',
                'jo_approvals.created_at'
            )
            // Join with jo_approval_policy to get the approval policy name
            ->leftJoin('jo_approval_policy', 'jo_approvals.approval_policy', '=', 'jo_approval_policy.id')

            // Join with jo_manage_employees to get employee first names
            ->leftJoin('jo_manage_employees', function ($join) {
                // Extract and compare employee IDs from the choose_employees JSON array
                $join->on(DB::raw('jo_manage_employees.id'), '=', DB::raw('ANY(SELECT jsonb_array_elements_text(jo_approvals.choose_employees)::bigint)'));
            })

            // Join with jo_teams to get team names
            ->leftJoin('jo_teams', 'jo_approvals.choose_teams', '=', 'jo_teams.id')

            // Paginate the results
            ->paginate($perPage);

        // Return paginated results
        return response()->json([
            'status' => 200,
            'approvals' => $approvals->items(),
            'pagination' => [
                'total' => $approvals->total(),
                'per_page' => $approvals->perPage(),
                'current_page' => $approvals->currentPage(),
                'last_page' => $approvals->lastPage(),
                'from' => $approvals->firstItem(),
                'to' => $approvals->lastItem(),
            ],
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error fetching approvals: ' . $e->getMessage());
        return response()->json(['message' => 'Error fetching approvals', 'error' => $e->getMessage()], 500);
    }
}



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction(); // Start a transaction to ensure atomic operations
    
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string',
                'min_count' => 'required|integer',
                'approval_policy' => 'nullable|exists:jo_approval_policy,id',
                'choose' => 'nullable|in:employees,teams',
                'choose_employees' => 'nullable|array|max:5000',
                'choose_employees.*' => 'exists:jo_manage_employees,id',
                'choose_teams' => 'nullable|array|max:5000',
                'choose_teams.*' => 'exists:jo_teams,id',
                'tags' => 'nullable|array|max:5000',
                'tags.*' => 'exists:jo_tags,id',
            ]);
            
            // if ($validatedData['choose'] === 'employees') {
            //     $validatedData['choose_teams'] = null;
            //     if (empty($validatedData['choose_employees']) || count($validatedData['choose_employees']) == 0) {
            //         throw new \Exception('Employees must be provided when "choose" is set to employees.');
            //     }
            // } elseif ($validatedData['choose'] === 'teams') {
            //     $validatedData['choose_employees'] = null;
            //     if (empty($validatedData['choose_teams']) || count($validatedData['choose_teams']) == 0) {
            //         throw new \Exception('Teams must be provided when "choose" is set to teams.');
            //     }
            // }
            
            //Create Crmentity record via CrmentityController
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Approvals',$validatedData['name']);
    
            if (!$crmid) {
                throw new \Exception('Failed to create Crmentity');
            }
    
            // Add crmid to validated data
            $validatedData['id'] = $crmid;
    
            // Create the Approval record with crmid
            $approval = Approval::create($validatedData);
    
            DB::commit(); // Commit the transaction
    
            return response()->json(['message' => 'Approval created successfully', 'approval' => $approval], 201);
    
        } catch (ValidationException $e) {
            DB::rollBack(); // Rollback the transaction on validation error
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors(), // Capture and return validation errors
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on general error
            Log::error('Failed to create approval: ' . $e->getMessage());
            Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
            return response()->json(['error' => 'Failed to create approval: ' . $e->getMessage()], 500);
        }
    }
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $approval = Approval::findOrFail($id);
            return response()->json($approval);
        } catch (\Exception $e) {
            Log::error('Error fetching approval: ' . $e->getMessage());
            return response()->json(['message' => 'Approval not found', 'error' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Start a transaction to ensure atomic operations
    
        try {
            // Find the existing approval record
            $approval = Approval::findOrFail($id); // Fails if no record is found
    
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'nullable|string',
                'min_count' => 'nullable|integer',
                'approval_policy' => 'nullable|exists:jo_approval_policy,id',
                'choose' => 'required|in:employees,teams',
                'choose_employees' => 'nullable|array|max:5000',
                'choose_employees.*' => 'exists:jo_manage_employees,id',
                'choose_teams' => 'nullable|array|max:5000',
                'choose_teams.*' => 'exists:jo_teams,id',
                'tags' => 'nullable|array|max:5000',
                'tags.*' => 'exists:jo_tags,id',
            ]);
    
            // Check the 'choose' field and validate related fields
            // if ($validatedData['choose'] === 'employees') {
            //     $validatedData['choose_teams'] = null;
            //     if (empty($validatedData['choose_employees']) || count($validatedData['choose_employees']) == 0) {
            //         throw new \Exception('Employees must be provided when "choose" is set to employees.');
            //     }
            // } elseif ($validatedData['choose'] === 'teams') {
            //     $validatedData['choose_employees'] = null;
            //     if (empty($validatedData['choose_teams']) || count($validatedData['choose_teams']) == 0) {
            //         throw new \Exception('Teams must be provided when "choose" is set to teams.');
            //     }
            // }
    
            // Update Crmentity record if necessary
            $approval->update($validatedData);

        // Prepare Crmentity update data
        $crmentityData = [
            'label' => $validatedData['name'],
            // You can include other fields if needed
        ];

        // Update the corresponding Crmentity record
        $crmentity = Crmentity::where('crmid', $id)->first();

        if ($crmentity) {
            // Update existing Crmentity record
            $crmentity->update($crmentityData);
        } else {
            // Create a new Crmentity record if it does not exist
            $crmentity = new Crmentity();
            $crmentity->crmid = $id; // Use an appropriate unique identifier
            $crmentity->label = $validatedData['name'];
            $crmentity->save();
        }
    
            // Update the approval record with validated data
            $approval->update($validatedData);
    
            DB::commit(); // Commit the transaction
    
            return response()->json(['message' => 'Approval updated successfully', 'approval' => $approval], 200);
    
        } catch (ValidationException $e) {
            DB::rollBack(); // Rollback the transaction on validation error
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors(), // Capture and return validation errors
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on general error
            Log::error('Failed to update approval: ' . $e->getMessage());
            Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
            return response()->json(['error' => 'Failed to update approval: ' . $e->getMessage()], 500);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $approval = Approval::findOrFail($id);
            $approval->delete();
            return response()->json(['message' => 'Approval deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Error deleting approval: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting approval', 'error' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $query = Approval::query();

        // Apply filters based on request parameters
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('min_count')) {
            $query->where('min_count', $request->input('min_count'));
        }

        if ($request->has('approval_policy')) {
            $query->where('approval_policy', 'like', '%' . $request->input('approval_policy') . '%');
        }

        if ($request->has('created_by')) {
            $query->where('created_by', 'like', '%' . $request->input('created_by') . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // You can add more filters as needed

        // Pagination
        $approvals = $query->paginate(10);

        // Format the response with pagination details
        return response()->json([
            'status' => 200,
            'approvals' => $approvals->items(),
            'pagination' => [
                'total' => $approvals->total(),
                'per_page' => $approvals->perPage(),
                'current_page' => $approvals->currentPage(),
                'last_page' => $approvals->lastPage(),
                'from' => $approvals->firstItem(),
                'to' => $approvals->lastItem(),
            ],
        ], 200);
    }
}

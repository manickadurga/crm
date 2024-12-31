<?php

namespace App\Http\Controllers;

use App\Models\Candidates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Crmentity;

class CandidatesController extends Controller
{
    public function index(Request $request)
{
    try {
        // Get pagination parameters from the request or set defaults
        $perPage = $request->input('per_page', 15); // Default to 15 items per page

        // Fetch paginated approvals
        $candidates = Candidates::paginate($perPage);

        // Return paginated results
        return response()->json([
            'status' => 200,
            'candidates' => $candidates->items(),
            'pagination' => [
                'total' => $candidates->total(),
                'per_page' => $candidates->perPage(),
                'current_page' => $candidates->currentPage(),
                'last_page' => $candidates->lastPage(),
                'from' => $candidates->firstItem(),
                'to' => $candidates->lastItem(),
            ],
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error fetching candidates: ' . $e->getMessage());
        return response()->json(['message' => 'Error fetching candidates', 'error' => $e->getMessage()], 500);
    }
}
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validatedData = Validator::make($request->all(), [
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'username' => 'nullable|string',
            'email' => 'required|email|unique:jo_manage_employees',
            'password' => 'required|string',
            'applied_date' => 'nullable|date',
            'reject_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'source' => 'nullable|string',
            'cv_url' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ])->validate();

        // Check if an existing record with the same email already exists
        $existingCandidate = Candidates::where('email', $request->email)->first();

        if ($existingCandidate) {
            // If the record exists, return a response indicating the duplicate
            DB::rollBack();
            return response()->json(['error' => 'Candidate with this email already exists.'], 400);
        }

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = $imageName;
        }

        // Create the employee record
        $candidate = Candidates::create($validatedData);

        // Create or update Crmentity
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Customers', $validatedData['first_name']);

        // Update candidate record with the new crmid
        $candidate->update(['id' => $crmid]);

        DB::commit();

        return response()->json([
            'message' => 'Candidate and Crmentity created successfully',
            'candidate' => $candidate,
        ], 201);

    } catch (ValidationException $e) {
        DB::rollBack();
        Log::error('Validation failed while creating candidate: ' . $e->getMessage());
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create candidate and Crmentity: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); 
        return response()->json(['error' => 'Failed to create candidate and Crmentity: ' . $e->getMessage()], 500);
    }
}


    public function show($id)
    {
        try {
            $candidate = Candidates::findOrFail($id);
            return response()->json($candidate);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Candidate not found.'], 404);
        }
    }
    public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'username' => 'nullable|string',
            'email' => 'nullable|email|unique:jo_manage_employees,email,' . $id, // Exclude current employee from unique validation
            'password' => 'nullable|string', // Make password nullable in case it's not being updated
            'applied_date' => 'nullable|date',
            'reject_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'source' => 'nullable|string',
            'cv_url' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the validated data
        $validatedData = $validator->validated();

        // Find and update the candidate record
        $candidate = Candidates::findOrFail($id);
        $candidate->update($validatedData);

        // Find the corresponding Crmentity entry
        $crmentity = Crmentity::where('crmid', $id)->where('setype', 'Customers')->first();

        if ($crmentity) {
            // Update existing Crmentity record
            $crmentity->update([
                'label' => $validatedData['first_name'] ?? $crmentity->label, // Use existing label if first_name is not provided
                'modifiedtime' => now(),
                // 'modifiedby' => auth()->id(), // Assuming you have authentication setup
            ]);
        } else {
            // If no existing Crmentity, create a new record
            Crmentity::create([
                'crmid' => $id,
                'setype' => 'Candidates',
                'label' => $validatedData['first_name'] ?? 'No Name', // Default to 'No Name' if first_name is not provided
                'createdtime' => now(),
                'modifiedtime' => now(),
                // 'createdby' => auth()->id(), // Assuming you have authentication setup
                // 'modifiedby' => auth()->id(),
            ]);
        }

        // Commit the transaction
        DB::commit();

        return response()->json([
            'message' => 'Candidate and Crmentity updated successfully',
            'candidate' => $candidate,
        ], 200);

    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update candidate and Crmentity: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update candidate and Crmentity', 'message' => $e->getMessage()], 500);
    }
}


    public function destroy($id)
    {
        try {
            $candidate = Candidates::findOrFail($id);
            $candidate->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete candidate.'], 500);
        }
    }

    public function search(Request $request)
{
    $query = Candidates::query();

    // Apply filters based on request parameters
    if ($request->has('first_name')) {
        $query->where('first_name', 'like', '%' . $request->input('first_name') . '%');
    }

    if ($request->has('last_name')) {
        $query->where('last_name', 'like', '%' . $request->input('last_name') . '%');
    }

    if ($request->has('username')) {
        $query->where('username', 'like', '%' . $request->input('username') . '%');
    }

    if ($request->has('email')) {
        $query->where('email', 'like', '%' . $request->input('email') . '%');
    }

    if ($request->has('tags')) {
        $tags = $request->input('tags');
        // Ensure tags is an array
        if (is_array($tags)) {
            $query->whereIn('tags', $tags);
        }
    }

    if ($request->has('source')) {
        $query->where('source', 'like', '%' . $request->input('source') . '%');
    }

    // You can add more filters as needed

    // Pagination
    $candidates = $query->paginate(10);

    // Format the response with pagination details
    return response()->json([
        'status' => 200,
        'candidates' => $candidates->items(),
        'pagination' => [
            'total' => $candidates->total(),
            'per_page' => $candidates->perPage(),
            'current_page' => $candidates->currentPage(),
            'last_page' => $candidates->lastPage(),
            'from' => $candidates->firstItem(),
            'to' => $candidates->lastItem(),
        ],
    ], 200);
}

}


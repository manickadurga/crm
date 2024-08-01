<?php

namespace App\Http\Controllers;

use App\Models\ProposalTemplates;
use App\Models\Crmentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProposalTemplatesController extends Controller
{
    public function index(Request $request)
{
    try {
        // Fetch the per_page parameter from the request, default to 10 if not provided
        $perPage = $request->input('per_page', 10);

        // Retrieve paginated proposal templates
        $proposalTemplates = ProposalTemplates::select('select_employee', 'name', 'content')
                                              ->paginate($perPage);

        // Return JSON response with paginated proposal templates and pagination information
        return response()->json([
            'status' => 200,
            'proposal_templates' => $proposalTemplates->items(), // Retrieve items from the paginator
            'pagination' => [
                'total' => $proposalTemplates->total(),
                'title' => 'ProposalTemplates',
                'per_page' => $proposalTemplates->perPage(),
                'current_page' => $proposalTemplates->currentPage(),
                'last_page' => $proposalTemplates->lastPage(),
                'from' => $proposalTemplates->firstItem(),
                'to' => $proposalTemplates->lastItem(),
            ],
        ], 200);
    } catch (\Exception $e) {
        // Log the error
        Log::error('Failed to retrieve proposalTemplates: ' . $e->getMessage());
        Log::error($e->getTraceAsString());

        // Return error response
        return response()->json([
            'status' => 500,
            'message' => 'Failed to retrieve proposalTemplates',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * Store a newly created proposal template in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // Validate the request data
            $validatedData = Validator::make($request->all(), [
                'select_employee' => 'required|exists:jo_employees,id',
                'name' => 'required|string|max:255',
                'content' => 'nullable|string',
            ])->validate();
    
            // Create Crmentity record via CrmentityController
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Proposal Template', $validatedData['name']);
    
            // Ensure Crmentity creation was successful
            if (!$crmid) {
                throw new \Exception('Failed to create Crmentity');
            }
    
            // Add crmid to validated data
            $validatedData['id'] = $crmid;
    
            // Create the ProposalTemplates record with crmid
            $proposalTemplate = ProposalTemplates::create($validatedData);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Proposal template created successfully',
                'proposal_template' => $proposalTemplate,
            ], 201);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create proposal template: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to create proposal template', 'message' => $e->getMessage()], 500);
        }
    }
    

    public function show($id)
    {
        try {
            // Find the proposal template by ID
            $proposalTemplate = ProposalTemplates::find($id);

            if (!$proposalTemplate) {
                return response()->json(['error' => 'Proposal template not found'], 404);
            }

            return response()->json(['proposal_template' => $proposalTemplate], 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch proposal template: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to fetch proposal template'], 500);
        }
    }
    public function update(Request $request, $id)
{
    try {
        // Validate the request data
        $validatedData = Validator::make($request->all(), [
            'select_employee' => 'nullable|exists:jo_employees,id',
            'name' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ])->validate();

        // Find the proposal template by ID
        $proposalTemplate = ProposalTemplates::find($id);

        if (!$proposalTemplate) {
            return response()->json(['error' => 'Proposal template not found'], 404);
        }

        // Update the proposal template
        $proposalTemplate->update($validatedData);

        // Handle Crmentity update
        $crmentity = Crmentity::where('crmid', $id)->first(); // Assuming 'crmid' is the identifier for Crmentity
        if ($crmentity) {
            // Update the Crmentity record with data from the proposal template
            $crmentity->label = $validatedData['name'] ?? $crmentity->label; // Example: Update the label with the name
            $crmentity->description = $validatedData['content'] ?? $crmentity->description; // Example: Update the description with the content
            $crmentity->save();
        } else {
            // Optionally handle the case where the Crmentity record does not exist
            Log::warning("Crmentity record not found for proposal template ID {$id}");
        }

        return response()->json([
            'message' => 'Proposal template and Crmentity updated successfully',
            'proposal_template' => $proposalTemplate
        ], 200);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Failed to update proposal template or Crmentity: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        return response()->json(['error' => 'Failed to update proposal template or Crmentity'], 500);
    }
}


    /**
     * Remove the specified proposal template from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Find the proposal template by ID
            $proposalTemplate = ProposalTemplates::find($id);

            if (!$proposalTemplate) {
                return response()->json(['error' => 'Proposal template not found'], 404);
            }

            // Delete the proposal template
            $proposalTemplate->delete();

            return response()->json(['message' => 'Proposal template deleted successfully'], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete proposal template: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to delete proposal template'], 500);
        }
    }
    public function search(Request $request)
{
    try {
        // Validate the search input
        $validatedData = $request->validate([
            'select_employee' => 'nullable|string',
            'name' => 'nullable|string',
            'content' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1', // Add validation for per_page
        ]);

        // Initialize the query builder
        $query = ProposalTemplates::query();

        // Apply search filters
        if (!empty($validatedData['select_employee'])) {
            $query->where('select_employee', 'like', '%' . $validatedData['select_employee'] . '%');
        }
        if (!empty($validatedData['name'])) {
            $query->where('name', 'like', '%' . $validatedData['name'] . '%');
        }
        if (!empty($validatedData['content'])) {
            $query->where('content', 'like', '%' . $validatedData['content'] . '%');
        }

        // Paginate the search results
        $perPage = $validatedData['per_page'] ?? 10; // default per_page value
        $proposalTemplates = $query->paginate($perPage);

        // Check if any proposal templates found
        if ($proposalTemplates->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No matching records found',
            ], 404);
        }

        // Return JSON response with search results and pagination information
        return response()->json([
            'status' => 200,
            'proposal_templates' => $proposalTemplates->items(),
            'pagination' => [
                'total' => $proposalTemplates->total(),
                'per_page' => $proposalTemplates->perPage(),
                'current_page' => $proposalTemplates->currentPage(),
                'last_page' => $proposalTemplates->lastPage(),
                'from' => $proposalTemplates->firstItem(),
                'to' => $proposalTemplates->lastItem(),
            ],
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Failed to search proposal templates: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        // Return a generic server error response
        return response()->json(['message' => 'Server Error'], 500);
    }
}

}

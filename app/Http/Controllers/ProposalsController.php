<?php

namespace App\Http\Controllers;
use App\Models\Crmentity;
use App\Models\Employee;
use App\Models\Proposals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ProposalsController extends Controller


{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    try {
        $perPage = $request->input('per_page', 10); // Set default per_page to 10

        // Fetch proposals with selected fields
        $proposals = Proposals::select('id', 'author', 'template', 'job_post_url', 'proposal_date')
            ->paginate($perPage);

        // Initialize an empty array to store formatted proposals
        $formattedProposals = [];

        // Loop through each proposal to format and add additional fields
        foreach ($proposals as $proposal) {
            // Initialize arrays for managers and members
            $managers = [];

            // Fetch manager first names based on author field
            $managerNames = Employee::where('id', $proposal->author) // Assuming author field holds employee ID
                ->pluck('first_name')
                ->toArray();

            // Assign manager names to $managers array
            $managers = $managerNames;

            // Build formatted proposal array
            $formattedProposals[] = [
                'id' => $proposal->id,
                'author' => $proposal->author,
                'template' => $proposal->template,
                'job_post_url' => $proposal->job_post_url,
                'proposal_date' => $proposal->proposal_date,
                'managers' => $managers, // Add managers to the formatted output
            ];
        }

        // Return JSON response with formatted proposals and pagination information
        return response()->json([
            'status' => 200,
            'proposals' => $formattedProposals,
            'pagination' => [
                'total' => $proposals->total(),
                'title' => 'Proposals',
                'per_page' => $proposals->perPage(),
                'current_page' => $proposals->currentPage(),
                'last_page' => $proposals->lastPage(),
                'from' => $proposals->firstItem(),
                'to' => $proposals->lastItem(),
            ],
        ], 200);

    } catch (\Exception $e) {
        // Handle exceptions and return error response
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tasks::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'author' => 'nullable|string|exists:jo_employment_types,id',
                'template' => 'nullable|string|exists:jo_proposal_templates,id',
                'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                    // Check if the contact ID exists in any of the specified tables
                    $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                    $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                    $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();
    
                    if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                        $fail("The selected contact ID does not exist in any of the specified tables.");
                    }
                }],
                'job_post_url' => 'nullable|url',
                'proposal_date' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'job_post_content' => 'required|string',
                'proposal_content' => 'nullable|string',
                // 'orgid' => 'integer',
            ]);
    
            // Create Crmentity record via CrmentityController
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Proposals', $validatedData['job_post_content']); // Use relevant data for Crmentity creation
    
            // Add crmid to validated data
            $validatedData['id'] = $crmid;
    
            // Create the Proposals record
            $proposal = Proposals::create($validatedData);
    
            DB::commit();
    
            return response()->json(['message' => 'Proposal created successfully', 'proposal' => $proposal], 201);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create proposal: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create proposal', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $proposals = Proposals::findOrFail($id);
            return response()->json($proposals);
        //  } catch (ModelNotFoundException $e) {
            // return response()->json(['message' => 'tasks not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('proposals::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'author' => 'nullable|string|exists:jo_employment_types,id',
            'template' => 'nullable|string|exists:jo_proposal_templates,name',
            'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                // Check if the contact ID exists in any of the specified tables
                $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();

                if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                    $fail("The selected contact ID does not exist in any of the specified tables.");
                }
            }],
            'job_post_url' => 'nullable|url',
            'proposal_date' => 'nullable|string',
            'tags.*' => 'exists:jo_tags,tags_names',
            'job_post_content' => 'nullable|string',
            'proposal_content' => 'nullable|string',
        ]);

        // Retrieve and update the Proposals record
        $proposals = Proposals::findOrFail($id);
        $proposals->update($validatedData);

        // Update the corresponding Crmentity record
        $crmentity = Crmentity::where('crmid', $id)->first(); // Assuming 'crmid' is the identifier for Crmentity
        if ($crmentity) {
            $crmentity->label = $validatedData['job_post_content'] ?? $crmentity->label; // Update Crmentity label with Proposal content
            // Optionally update other Crmentity fields if needed
            $crmentity->save();
        } else {
            // Handle the case where the Crmentity record does not exist (if needed)
            Log::warning("Crmentity record not found for proposal ID {$id}");
        }

        return response()->json([
            'message' => 'Proposal and Crmentity updated successfully',
            'proposal' => $proposals,
        ], 200);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Failed to update proposal or Crmentity: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update proposal or Crmentity: ' . $e->getMessage()], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        {

            try {
                $proposals = Proposals::findOrFail($id); // Ensure you use the correct model
                $proposals->delete();
                return response()->json(['message' => 'Proposals deleted successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to delete Proposals', 'error' => $e->getMessage()], 500);
            }
        }

    }
    public function search(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'author' => 'nullable|string',
                'template' => 'nullable|string',
                'contacts' => [
                    function ($attribute, $value, $fail) {
                        // Check if the value exists in any of the specified tables
                        // if (!DB::table('jo_clients')->where('clientsname', $value)->exists() &&
                        //     !DB::table('jo_customers')->where('name', $value)->exists() &&
                        //     !DB::table('jo_leads')->where('name', $value)->exists()) {
                        //     $fail("The $attribute must exist in 'jo_clients', 'jo_customers', or 'jo_leads' table.");
                        // }
                    }
                ],
                'job_post_url' => 'nullable|url',
                'proposal_date' => 'nullable|string',
                'job_post_content' => 'nullable|string',

                'per_page' => 'nullable|integer|min:1', // Ensure per_page is a valid integer with minimum 1
            ]);

            $query = Proposals::query();

            // Apply search filters
            if (!empty($validatedData['author'])) {
                $query->where('author', $validatedData['author']);
            }
            if (!empty($validatedData['template'])) {
                $query->where('template', $validatedData['template']);
            }
            // Add other search conditions as needed

            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // Default per_page value
            $proposals = $query->paginate($perPage);

            if ($proposals->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching proposals found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'proposals' => $proposals->items(),
                'pagination' => [
                    'total' => $proposals->total(),
                    'per_page' => $proposals->perPage(),
                    'current_page' => $proposals->currentPage(),
                    'last_page' => $proposals->lastPage(),
                    'from' => $proposals->firstItem(),
                    'to' => $proposals->lastItem(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    }



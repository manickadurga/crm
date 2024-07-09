<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Pipelines;
use App\Models\Crmentity;
use Exception;
use Illuminate\Validation\ValidationException;

class PipelinesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve paginated pipelines
            $pipelines = Pipelines::paginate(10); // Adjust 10 to the number of pipelines per page you want

            // Check if any pipelines found
            if ($pipelines->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'pipelines' => $pipelines->items(),
                'pagination' => [
                    'total' => $pipelines->total(),
                    'per_page' => $pipelines->perPage(),
                    'current_page' => $pipelines->currentPage(),
                    'last_page' => $pipelines->lastPage(),
                    'from' => $pipelines->firstItem(),
                    'to' => $pipelines->lastItem(),
                ],
            ], 200);

        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve pipelines: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve pipelines',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
        'stages' => 'nullable|array|max:5000',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    try {
        // Create the pipeline
        $pipeline = Pipelines::create($validator->validated());

        // Retrieve or create a new Crmentity record for Pipelines
        $defaultCrmentity = Crmentity::where('setype', 'Pipelines')->first();

        if (!$defaultCrmentity) {
            // Create a default Crmentity if it doesn't exist
            $defaultCrmentity = Crmentity::create([
                'crmid' => Crmentity::max('crmid') + 1,
                'smcreatorid' => 0, // Replace with appropriate default
                'smownerid' => 0, // Replace with appropriate default
                'setype' => 'Pipelines',
                'description' => '',
                'createdtime' => now(),
                'modifiedtime' => now(),
                'viewedtime' => now(),
                'status' => '',
                'version' => 0,
                'presence' => 0,
                'deleted' => 0,
                'smgroupid' => 0,
                'source' => '',
                'label' => $pipeline->name, // Adjust as per your requirement
            ]);

            if (!$defaultCrmentity) {
                throw new \Exception('Failed to create default Crmentity for Pipelines');
            }
        }

        // Create a new Crmentity record with a new crmid
        $newCrmentity = new Crmentity();
        $newCrmentity->crmid = Crmentity::max('crmid') + 1;
        $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid ?? 0; // Replace with appropriate default
        $newCrmentity->smownerid = $defaultCrmentity->smownerid ?? 0; // Replace with appropriate default
        $newCrmentity->setype = 'Pipelines';
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
        $newCrmentity->label = $pipeline->name; // Adjust as per your requirement
        $newCrmentity->save();

        // Set the new crmid as the pipeline ID
        $pipeline->id = $newCrmentity->crmid;
        $pipeline->save();

        // Return a success response with the created pipeline object
        return response()->json(['message' => 'Pipeline created successfully', 'pipeline' => $pipeline], 201);
    } catch (\Exception $e) {
        // Log the error
        Log::error('Failed to create pipeline: ' . $e->getMessage());

        // Return an error response with the actual error message
        return response()->json(['error' => 'Failed to create pipeline: ' . $e->getMessage()], 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $pipeline = Pipelines::findOrFail($id);
            return response()->json($pipeline);
        } catch (Exception $e) {
            Log::error('Failed to retrieve pipeline: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve pipeline'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $pipeline = Pipelines::findOrFail($id);
            $pipeline->update($validator->validated());
            return response()->json($pipeline);
        } catch (Exception $e) {
            Log::error('Failed to update pipeline: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update pipeline'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $pipeline = Pipelines::findOrFail($id);
            $pipeline->delete();
            return response()->json(['message' => 'Pipeline deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete pipeline: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete pipeline'], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'name' => 'nullable|string',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'stages' => 'nullable|array',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);

            // Initialize the query builder
            $query = Pipelines::query();

            // Apply search filters
            if (isset($validatedData['name'])) {
                $query->where('name', 'like', '%' . $validatedData['name'] . '%');
            }

            if (isset($validatedData['description'])) {
                $query->where('description', 'like', '%' . $validatedData['description'] . '%');
            }

            if (isset($validatedData['is_active'])) {
                $query->where('is_active', $validatedData['is_active']);
            }

            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // default per_page value
            $pipelines = $query->paginate($perPage);

            // Check if any pipelines found
            if ($pipelines->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'pipelines' => $pipelines->items(),
                'pagination' => [
                    'total' => $pipelines->total(),
                    'per_page' => $pipelines->perPage(),
                    'current_page' => $pipelines->currentPage(),
                    'last_page' => $pipelines->lastPage(),
                    'from' => $pipelines->firstItem(),
                    'to' => $pipelines->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search pipelines: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search pipelines: ' . $e->getMessage()], 500);
        }
    }
}

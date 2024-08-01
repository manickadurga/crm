<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Pipelines;
use App\Models\Crmentity;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PipelinesController extends Controller
{
    public function index()
    {
        try {
            // Retrieve paginated pipelines
            $pipelines = Pipelines::paginate(10); // Adjust 10 to the number of pipelines per page you want
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
    public function store(Request $request)
    {
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
    
        DB::beginTransaction();
    
        try {
            // Create Crmentity record via CrmentityController
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Pipelines', $validator->validated()['name']);
    
            // Prepare pipeline data including crmid as id
            $pipelineData = array_merge(
                $validator->validated(),
                ['id' => $crmid] // Set the crmid as the id
            );
    
            // Create the Pipeline with the crmid
            $pipeline = Pipelines::create($pipelineData);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Pipeline created successfully',
                'pipeline' => $pipeline
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create pipeline: ' . $e->getMessage());
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
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'stages' => 'nullable|array|max:5000',
            'crmentity_label' => 'nullable|string|max:255', // Add this if you want to update Crmentity label
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
    
        try {
            // Find and update the Pipeline record
            $pipeline = Pipelines::findOrFail($id);
            $validatedData = $validator->validated();
            $pipeline->update($validatedData);
    
        // Create an instance of CrmentityController
        $crmentityController = new CrmentityController();
        $crmid = $pipeline->id;

        // Update the corresponding Crmentity record
        $updated = $crmentityController->updateCrmentity($crmid, [
            'label' => $validatedData['name'],
            //'description' => $validatedData['fiscal_information'] ?? ''
        ]);

        if (!$updated) {
            throw new Exception('Failed to update Crmentity');
        }
    
            return response()->json([
                'message' => 'Pipeline updated successfully',
                'pipeline' => $pipeline,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update pipeline: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update pipeline'], 500);
        }
    }
    

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

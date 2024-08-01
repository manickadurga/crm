<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Tags;
use App\Models\Crmentity;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Set default per_page to 10

            // Retrieve paginated tags
            $tags = Tags::select('id', 'tags_name', 'tag_color', 'tenant_level', 'description')
                        ->paginate($perPage);

            // Return JSON response with paginated tags and pagination information
            return response()->json([
                'status' => 200,
                'tags' => $tags->items(), // Retrieve items from the paginator
                'pagination' => [
                    'total' => $tags->total(),
                    'title' => 'Tags',
                    'per_page' => $tags->perPage(),
                    'current_page' => $tags->currentPage(),
                    'last_page' => $tags->lastPage(),
                    'from' => $tags->firstItem(),
                    'to' => $tags->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to retrieve tags: ' . $e->getMessage());
            // Return a generic server error response
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
    public function store(Request $request)
{
    DB::beginTransaction(); // Begin a database transaction

    try {
        Log::info('Incoming request data:', $request->all());

        // Validate the request data
        $validatedData = $request->validate([
            'tags_name' => 'required|string|max:255',
            'tag_color' => 'required|string|max:255',
            'tenant_level' => 'nullable|boolean',
            'description' => 'nullable|string|max:255',
            //'orgid' => 'nullable|integer',
        ]);

        Log::info('Validated data:', $validatedData);

        // Create the Crmentity record using CrmentityController
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Tags', $validatedData['tags_name']);

        if (!$crmid) {
            throw new \Exception('Failed to create Crmentity');
        }

        // Add crmid to the validated data
        $validatedData['id'] = $crmid;

        // Create the Tags record with the crmid
        $tags = Tags::create($validatedData);

        DB::commit(); // Commit the transaction

        return response()->json(['message' => 'Created successfully', 'tags' => $tags], 201);

    } catch (ValidationException $e) {
        DB::rollBack(); // Rollback the transaction on validation error
        Log::error('Validation error:', ['error' => $e->getMessage()]);
        return response()->json(['errors' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction on general error
        Log::error('Error creating tag:', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Failed to create tag', 'error' => $e->getMessage()], 500);
    }
}

    public function show($id)
    {
        try {
            $tag = Tags::findOrFail($id);
            return response()->json($tag, 200);
        } catch (\Exception $e) {
            Log::error('Failed to find tag: ' . $e->getMessage());
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            // Validate request data
            $validatedData = $request->validate([
                'tags_name' => 'nullable|string',
                'tag_color' => 'nullable|string',
                'tenant_level' => 'nullable|boolean',
                'description' => 'nullable|string|max:1000',
            ]);
    
            // Find the tag or fail
            $tag = Tags::findOrFail($id);
    
            // Update tag data
            $tag->update($validatedData);
    
            // Update or create the corresponding Crmentity record
            $crmentity = Crmentity::where('crmid', $id)->first();
    
            if ($crmentity) {
                // Update existing Crmentity record
                $crmentity->label = $validatedData['tags_name'];
                //$crmentity->description = $validatedData['description'] ?? $crmentity->description;
                $crmentity->save();
            } else {
                // Optionally create a new Crmentity record if it does not exist
                $crmentity = new Crmentity();
                $crmentity->crmid = $id;
                $crmentity->label = $validatedData['tags_name'];
                //$crmentity->description = $validatedData['description'] ?? '';
                $crmentity->save();
            }
    
            // Return success response with updated tag
            return response()->json([
                'message' => 'Tag and Crmentity updated successfully',
                'tag' => $tag,
                'crmentity' => $crmentity,
            ], 200);
    
        } catch (ModelNotFoundException $e) {
            // Log the error for debugging
            Log::error('Tag not found: ' . $e->getMessage());
            // Return a 404 not found response
            return response()->json(['message' => 'Tag not found'], 404);
        } catch (ValidationException $e) {
            // Log the error for debugging
            Log::error('Validation failed: ' . $e->getMessage());
            // Return a 422 Unprocessable Entity response
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to update tag: ' . $e->getMessage());
            // Return a generic server error response
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $tag = Tags::findOrFail($id);
            $tag->delete();

            return response()->json(['message' => 'Deleted successfully'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to delete tag: ' . $e->getMessage());
            // Return a generic server error response
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'tags_name' => 'nullable|string',
                'tag_color' => 'nullable|string',
                'tenant_level' => 'nullable|boolean',
                'description' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);

            // Initialize the query builder
            $query = Tags::query();

            // Apply search filters
            if (!empty($validatedData['tags_name'])) {
                $query->where('tags_name', 'like', '%' . $validatedData['tags_name'] . '%');
            }
            if (!empty($validatedData['tag_color'])) {
                $query->where('tag_color', $validatedData['tag_color']);
            }
            if (!is_null($validatedData['tenant_level'])) {
                $query->where('tenant_level', $validatedData['tenant_level']);
            }
            if (!empty($validatedData['description'])) {
                $query->where('description', 'like', '%' . $validatedData['description'] . '%');
            }

            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // default per_page value
            $tags = $query->paginate($perPage);

            // Check if any tags found
            if ($tags->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }

            // Return JSON response with search results and pagination information
            return response()->json([
                'status' => 200,
                'tags' => $tags->items(),
                'pagination' => [
                    'total' => $tags->total(),
                    'per_page' => $tags->perPage(),
                    'current_page' => $tags->currentPage(),
                    'last_page' => $tags->lastPage(),
                    'from' => $tags->firstItem(),
                    'to' => $tags->lastItem(),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to search tags: ' . $e->getMessage());
            // Return a generic server error response
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
}

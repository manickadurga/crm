<?php

namespace App\Http\Controllers;

use App\Models\Warehouses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Models\Tags;

class WarehousesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve paginated warehouses
            $warehouses = Warehouses::paginate(10); // Adjust 10 to the number of warehouses per page you want

            // Check if any warehouses found
            if ($warehouses->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            // Return paginated response
            return response()->json([
                'status' => 200,
                'warehouses' => $warehouses->items(),
                'pagination' => [
                    'total' => $warehouses->total(),
                    'per_page' => $warehouses->perPage(),
                    'current_page' => $warehouses->currentPage(),
                    'last_page' => $warehouses->lastPage(),
                    'from' => $warehouses->firstItem(),
                    'to' => $warehouses->lastItem(),
                ],
            ], 200);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to retrieve warehouses: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve warehouses',
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
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|string|max:255',
                'name' => 'required|string|max:255',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id', // Ensure each tag ID exists in jo_tags table
                'code' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255',
                'active' => 'nullable|boolean',
                'description' => 'nullable|string',
                'location' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Get validated data
            $data = $validator->validated();

            // Handle tags
            // if (isset($data['tags'])) {
            //     $tags = Tags::whereIn('id', $data['tags'])->get(); // Retrieve tags by IDs
            //     $tagData = $tags->map(function ($tag) {
            //         return [
            //             'tags_name' => $tag->tags_name,
            //             'tag_color' => $tag->tag_color,
            //         ];
            //     });

            //     $data['tags'] = $tagData->toJson(); // Convert to JSON format for storage
            // }

            // Create the warehouse entry
            $warehouse = Warehouses::create($data);

            return response()->json(['message' => 'Warehouse created successfully', 'warehouse' => $warehouse], 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to create warehouse: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create warehouse'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $warehouse = Warehouses::findOrFail($id);
            return response()->json($warehouse);
        } catch (Exception $e) {
            Log::error('Failed to fetch warehouse: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch warehouse'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|string|max:255',
                'name' => 'nullable|string|max:255',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id', // Ensure each tag ID exists in jo_tags table
                'code' => 'nullable|string|max:255',
                'email' => 'nullable|string|email|max:255',
                'active' => 'nullable|boolean',
                'description' => 'nullable|string',
                'location' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Get validated data
            $data = $validator->validated();

            // Handle tags
            // if (isset($data['tags'])) {
            //     $tags = Tags::whereIn('id', $data['tags'])->get(); // Retrieve tags by IDs
            //     $tagData = $tags->map(function ($tag) {
            //         return [
            //             'tags_name' => $tag->tags_name,
            //             'tag_color' => $tag->tag_color,
            //         ];
            //     });

            //     $data['tags'] = $tagData->toJson(); // Convert to JSON format for storage
            // }

            // Update the warehouse entry
            $warehouse = Warehouses::findOrFail($id);
            $warehouse->update($data);

            return response()->json(['message' => 'Warehouse updated successfully', 'warehouse' => $warehouse], 200);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to update warehouse: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update warehouse'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $warehouse = Warehouses::findOrFail($id);
            $warehouse->delete();
            return response()->json(['message' => 'Warehouse deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete warehouse: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete warehouse'], 500);
        }
    }
    public function search(Request $request)
    {
        Log::info('Search method called', ['params' => $request->all()]);
    
        try {
            // Validate search parameters
            $validatedData = $request->validate([
                'name' => 'string|nullable',
                'code' => 'string|nullable',
                'email' => 'string|nullable|email',
                'active' => 'boolean|nullable',
                'tags' => 'array|nullable', // Updated to accept an array of tag IDs
                'location' => 'string|nullable',
            ]);
    
            Log::info('Validation successful', ['validatedData' => $validatedData]);
    
            // Build the query based on validated data
            $query = Warehouses::query();
    
            foreach ($validatedData as $key => $value) {
                if ($value !== null) {
                    Log::info('Adding to query', ['key' => $key, 'value' => $value]);
    
                    if ($key === 'tags') {
                        // If 'tags' is present, filter by each tag ID
                        foreach ($value as $tagId) {
                            $query->whereJsonContains('tags', $tagId);
                        }
                    } else {
                        // Regular where clause for other fields
                        $query->where($key, 'like', "%$value%");
                    }
                }
            }
    
            // Paginate the results
            $warehouses = $query->paginate(10);
    
            if ($warehouses->isEmpty()) {
                Log::info('No records found');
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            Log::info('Search results found', ['total' => $warehouses->total()]);
    
            return response()->json([
                'status' => 200,
                'warehouses' => $warehouses->items(),
                'pagination' => [
                    'total' => $warehouses->total(),
                    'per_page' => $warehouses->perPage(),
                    'current_page' => $warehouses->currentPage(),
                    'last_page' => $warehouses->lastPage(),
                    'from' => $warehouses->firstItem(),
                    'to' => $warehouses->lastItem(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search warehouses', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to search warehouses: ' . $e->getMessage()], 500);
        }
    }
}
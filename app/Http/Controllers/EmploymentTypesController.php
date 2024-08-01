<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Tags;
use App\Models\Crmentity; // Ensure this model exists
use App\Models\EmploymentTypes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmploymentTypesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); 
            $employmentTypes = EmploymentTypes::select('id', 'employment_type_name', 'tags')
                            ->paginate($perPage);
            if ($employmentTypes->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
            $formattedEmploymentTypes = $employmentTypes->map(function ($employmentType) {
                $formattedTags = [];
                if (!empty($employmentType->tags)) {
                    $tagIds = is_string($employmentType->tags) ? json_decode($employmentType->tags) : $employmentType->tags;
                    if (is_array($tagIds) && !empty($tagIds)) {
                        $tagNames = Tags::whereIn('id', $tagIds)
                                        ->pluck('tags_name')
                                        ->toArray();
                        $formattedTags = $tagNames;
                    }
                }
                return [
                    'id' => $employmentType->id,
                    'employment_type_name' => $employmentType->employment_type_name,
                    'formattedTags' => $formattedTags,
                ];
            });
            return response()->json([
                'status' => 200,
                'employment_types' => $formattedEmploymentTypes,
                'pagination' => [
                    'total' => $employmentTypes->total(),
                    'title' => 'EmploymentTypes',
                    'per_page' => $employmentTypes->perPage(),
                    'current_page' => $employmentTypes->currentPage(),
                    'last_page' => $employmentTypes->lastPage(),
                    'from' => $employmentTypes->firstItem(),
                    'to' => $employmentTypes->lastItem(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve employment types: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to retrieve employment types'], 500);
        }
    }
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'employment_type_name' => 'required|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
        ]);

        // Create Crmentity record via CrmentityController
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Employment Types', $validatedData['employment_type_name']);

        // Set crmid as the id in the employment type data
        $validatedData['id'] = $crmid; // Assuming you have a column for Crmentity ID

        // Create the EmploymentTypes record with the Crmentity ID
        $employmentType = EmploymentTypes::create([
            'employment_type_name' => $validatedData['employment_type_name'],
            'tags' => json_encode($validatedData['tags'] ?? null),
            'id' => $validatedData['id'], // Store Crmentity ID
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Employment type and Crmentity created successfully',
            'employment_type' => $employmentType,
        ], 201);

    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create employment type and Crmentity: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        return response()->json(['error' => 'Failed to create employment type and Crmentity'], 500);
    }
}


    public function show($id)
    {
        try {
            $employmentType = EmploymentTypes::findOrFail($id);
            return response()->json(['employment_type' => $employmentType], 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch employment type: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to fetch employment type'], 500);
        }
    }
    public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'employment_type_name' => 'required|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
        ]);

        // Find and update the EmploymentTypes record
        $employmentType = EmploymentTypes::findOrFail($id);
        $employmentType->update([
            'employment_type_name' => $validatedData['employment_type_name'],
            'tags' => json_encode($validatedData['tags'] ?? null),
        ]);

        // Find or create the corresponding Crmentity record
        $crmentity = Crmentity::where('crmid', $id)->where('setype', 'Employment Types')->first();

        if ($crmentity) {
            // Update existing Crmentity record
            $crmentity->update([
                'label' => $validatedData['employment_type_name'],
                'modifiedtime' => now(),
                'status' => 'Active', // Or any status you prefer
            ]);
        } else {
            // Create a new Crmentity record if it does not exist
            Crmentity::create([
                'crmid' => $id,
                'setype' => 'Employment Types',
                'label' => $validatedData['employment_type_name'],
                'createdtime' => now(),
                'modifiedtime' => now(),
                'createdby' => auth()->id(), // Assuming you have authentication setup
                'modifiedby' => auth()->id(),
                'status' => 'Active', // Or any status you prefer
            ]);
        }

        DB::commit();

        return response()->json([
            'message' => 'Employment type and Crmentity updated successfully',
            'employment_type' => $employmentType,
            'crmentity' => $crmentity,
        ], 200);

    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update employment type and Crmentity: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        return response()->json(['error' => 'Failed to update employment type and Crmentity'], 500);
    }
}
    public function destroy($id)
    {
        try {
            $employmentType = EmploymentTypes::findOrFail($id);
            $employmentType->delete();
            return response()->json(['message' => 'Employment type deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete employment type: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to delete employment type'], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'employment_type_name' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'per_page' => 'nullable|integer|min:1',
            ]);
            $query = EmploymentTypes::query();
            if (!empty($validatedData['employment_type_name'])) {
                $query->where('employment_type_name', 'like', '%' . $validatedData['employment_type_name'] . '%');
            }
            if (!empty($validatedData['tags'])) {
                $tags = $validatedData['tags'];
                $query->where(function ($q) use ($tags) {
                    foreach ($tags as $tag) {
                        $q->orWhere('tags', 'like', '%"'.$tag.'"%');
                    }
                });
            }
            $perPage = $validatedData['per_page'] ?? 10;
            $employmentTypes = $query->paginate($perPage);
            if ($employmentTypes->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }
            return response()->json([
                'status' => 200,
                'employment_types' => $employmentTypes->items(),
                'pagination' => [
                    'total' => $employmentTypes->total(),
                    'per_page' => $employmentTypes->perPage(),
                    'current_page' => $employmentTypes->currentPage(),
                    'last_page' => $employmentTypes->lastPage(),
                    'from' => $employmentTypes->firstItem(),
                    'to' => $employmentTypes->lastItem(),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to search employment types: ' . $e->getMessage());
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
}

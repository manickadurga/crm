<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Equipments;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Tags;
use App\Models\Crmentity;
use Illuminate\Support\Facades\DB;

class EquipmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Retrieve paginated equipments
            $equipments = Equipments::paginate(10); // Replace 10 with the number of items per page you want
    
            // Check if any equipments found
            if ($equipments->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'equipments' => $equipments->items(),
                'pagination' => [
                    'total' => $equipments->total(),
                    'per_page' => $equipments->perPage(),
                    'current_page' => $equipments->currentPage(),
                    'last_page' => $equipments->lastPage(),
                    'from' => $equipments->firstItem(),
                    'to' => $equipments->lastItem(),
                ],
            ], 200);
    
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve equipments: ' . $e->getMessage());
    
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve equipments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'name' => 'required|string|max:255',
                'type' => 'nullable|string',
                'manufactured_year' => 'nullable|integer',
                'sn' => 'nullable|string',
                'max_share_period' => 'nullable|integer',
                'initial_cost' => 'nullable|integer',
                'currency' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'auto_approve' => 'boolean|nullable',
            ]);
    
            // Handle image upload if an image is provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName); // Move the file to public/images directory
                $validatedData['image'] = 'images/' . $imageName; // Store relative path
            } else {
                // If no image is provided, set a default value or null as needed
                // $validatedData['image'] = null; // or handle accordingly
            }
    
            // Retrieve or create a new Crmentity record
            $defaultCrmentity = Crmentity::where('setype', 'Equipments')->first();
            
            if (!$defaultCrmentity) {
                // Log an error if default Crmentity not found
                Log::error('Default Crmentity for Equipments not found');
                throw new \Exception('Default Crmentity not found');
            }
    
            // Create a new Crmentity record with a new crmid
            $newCrmentity = new Crmentity();
            $newCrmentity->crmid = Crmentity::max('crmid') + 1;
            $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid ?? 0; // Replace with appropriate default
            $newCrmentity->smownerid = $defaultCrmentity->smownerid ?? 0; // Replace with appropriate default
            $newCrmentity->setype = 'Equipments';
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
            $newCrmentity->label = $validatedData['name'];
            $newCrmentity->save();
    
            // Set the new crmid as the equipment ID
            $validatedData['id'] = $newCrmentity->crmid;
    
            // Create the equipment entry
            $equipment = Equipments::create($validatedData);
    
            DB::commit();
    
            return response()->json(['message' => 'Equipment created successfully', 'equipment' => $equipment], 201);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation failed while creating equipment: ' . $e->getMessage());
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create equipment: ' . $e->getMessage());
            Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
            return response()->json(['error' => 'Failed to create equipment: ' . $e->getMessage()], 500);
        }
    }
    

    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // Retrieve the equipment by ID
            $equipment = Equipments::findOrFail($id);

            // Optionally decode JSON fields like location, tags, etc.
            $equipment->location = json_decode($equipment->location, true);
            $equipment->tags = json_decode($equipment->tags, true);
            // Add more fields as needed

            return response()->json([
                'status' => 200,
                'equipment' => $equipment,
            ], 200);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to fetch equipment: ' . $e->getMessage());

            return response()->json([
                'status' => 404,
                'message' => 'Equipment not found or failed to fetch equipment',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
{
    try {
        // Validate the request data
        $validatedData = $request->validate([
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string',
            'type' => 'nullable|string',
            'manufactured_year' => 'nullable|integer',
            'sn' => 'nullable|string',
            'max_share_period' => 'nullable|integer',
            'initial_cost' => 'nullable|integer',
            'currency' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'auto_approve' => 'boolean|nullable',
        ]);

        // Find the existing equipment
        $equipment = Equipments::findOrFail($id);

        // Handle image upload if an image is provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName); // Move the file to public/images directory
            $validatedData['image'] = 'images/' . $imageName; // Store relative path
        }

        // // Handle tags
        // if (isset($validatedData['tags'])) {
        //     $tags = [];
        //     foreach ($validatedData['tags'] as $id) {
        //         $tag = Tags::find($id);
        //         if ($tag) {
        //             $tags[] = [
        //                 'tags_name' => $tag->tags_name,
        //                 'tag_color' => $tag->tag_color,
        //             ];
        //         } else {
        //             throw ValidationException::withMessages(['tags' => "Tag with ID '$id' not found"]);
        //         }
        //     }
        //     $validatedData['tags'] = json_encode($tags);
        // }

        // Update the equipment entry
        $equipment->update($validatedData);
        return response()->json($equipment, 200);

    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Equipment not found'], 404);
    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        Log::error('Failed to update equipment: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to update equipment'], 500);
    }
}

    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $equipment = Equipments::findOrFail($id);
            $equipment->delete();
            return response()->json(['message' => 'Equipment deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete equipment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete equipment'], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'name' => 'nullable|string',
                'type' => 'nullable|string',
                'manufactured_year' => 'nullable|integer',
                'sn' => 'nullable|string',
                'max_share_period' => 'nullable|integer',
                'initial_cost' => 'nullable|integer',
                'currency' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id', // Validate each tag ID exists in the tags table
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);
    
            // Initialize the query builder
            $query = Equipments::query();
    
            // Apply search filters
            foreach ($validatedData as $key => $value) {
                if ($value !== null && in_array($key, ['name', 'type', 'sn', 'currency'])) {
                    $query->where($key, 'like', '%' . $value . '%');
                }
    
                if ($key === 'manufactured_year' && $value !== null) {
                    $query->where('manufactured_year', $value);
                }
    
                if ($key === 'max_share_period' && $value !== null) {
                    $query->where('max_share_period', $value);
                }
    
                if ($key === 'initial_cost' && $value !== null) {
                    $query->where('initial_cost', $value);
                }
    
                if ($key === 'tags' && $value !== null) {
                    $query->whereHas('tags', function ($q) use ($value) {
                        $q->whereIn('id', $value);
                    });
                }
    
                if ($key === 'location' && $value !== null) {
                    foreach ($value as $locationKey => $locationValue) {
                        $query->where("location->$locationKey", 'like', '%' . $locationValue . '%');
                    }
                }
            }
    
            // Paginate the search results
            $perPage = $validatedData['per_page'] ?? 10; // default per_page value
            $equipments = $query->paginate($perPage);
    
            // Optionally decode JSON fields like location, tags, etc.
            foreach ($equipments as $equipment) {
                // Assuming 'location', 'tags', and other JSON fields need decoding
                $equipment->location = json_decode($equipment->location, true);
                $equipment->tags = json_decode($equipment->tags, true);
                // Add more fields as needed
            }
    
            // Check if any equipments found
            if ($equipments->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'equipments' => $equipments->items(),
                'pagination' => [
                    'total' => $equipments->total(),
                    'per_page' => $equipments->perPage(),
                    'current_page' => $equipments->currentPage(),
                    'last_page' => $equipments->lastPage(),
                    'from' => $equipments->firstItem(),
                    'to' => $equipments->lastItem(),
                ],
            ], 200);
    
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search equipments: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search equipments: ' . $e->getMessage()], 500);
        }
    }
    

}

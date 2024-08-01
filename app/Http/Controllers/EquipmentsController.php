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
    
            // Handle image upload if present
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $validatedData['image'] = 'images/' . $imageName;
            }
    
            $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Equipments', $validatedData['name']);

        // Create the customer with the crmid
        $validatedData['id'] = $crmid; // Add crmid to customer data
            $equipment = Equipments::create($validatedData);
            
            DB::commit();
    
            return response()->json([
                'message' => 'Equipment created successfully',
                'equipment' => $equipment,
               // 'crmentity' => Crmentity::where('crmid', $crmid)->first(),
            ], 201);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation failed while creating equipment and Crmentity: ' . $e->getMessage());
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create equipment and Crmentity: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to create equipment and Crmentity'], 500);
        }
    }
    

    public function show($id)
    {
        try {
            $equipment = Equipments::findOrFail($id);
            $equipment->location = json_decode($equipment->location, true);
            $equipment->tags = json_decode($equipment->tags, true);
            return response()->json([
                'status' => 200,
                'equipment' => $equipment,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch equipment: ' . $e->getMessage());

            return response()->json([
                'status' => 404,
                'message' => 'Equipment not found or failed to fetch equipment',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
    
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
                'auto_approve' => 'nullable|boolean',
            ]);
    
            // Find and update the Equipment record
            $equipment = Equipments::findOrFail($id);
    
            // Handle image upload if present
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $validatedData['image'] = 'images/' . $imageName;
            }
    
            $equipment->update($validatedData);
    
            // Find or create the Crmentity record
            $crmentity = Crmentity::where('crmid', $id)->where('setype', 'Equipments')->first();
    
            if ($crmentity) {
                // Update existing Crmentity record
                $crmentity->update([
                    'label' => $validatedData['name'],
                    'modifiedtime' => now(),
                    'status' => $validatedData['status'] ?? $crmentity->status, // Update status if provided
                    // Update other fields if necessary
                ]);
            } else {
                // Create a new Crmentity record if not found
                Crmentity::create([
                    'crmid' => $id,
                    'setype' => 'Equipments',
                    'label' => $validatedData['name'],
                    'createdtime' => now(),
                    'modifiedtime' => now(),
                    'status' => $validatedData['status'] ?? 'Active', // Default status
                    'createdby' => auth()->id(), // Assuming you have authentication setup
                    'modifiedby' => auth()->id(),
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Equipment and Crmentity updated successfully',
                'equipment' => $equipment,
                //'crmentity' => $crmentity,
            ], 200);
    
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Equipment not found'], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update equipment and Crmentity: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update equipment and Crmentity'], 500);
        }
    }
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
                'tags.*' => 'exists:jo_tags,id',
                'per_page' => 'nullable|integer|min:1',
            ]);
            $query = Equipments::query();
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
            $perPage = $validatedData['per_page'] ?? 10; 
            $equipments = $query->paginate($perPage);
            foreach ($equipments as $equipment) {
                $equipment->location = json_decode($equipment->location, true);
                $equipment->tags = json_decode($equipment->tags, true);
            }
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

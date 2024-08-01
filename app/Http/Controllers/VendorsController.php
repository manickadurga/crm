<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Vendors;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Models\Crmentity;

class VendorsController extends Controller
{

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Set default per_page to 10

            // Retrieve paginated vendors
            $vendors = Vendors::select('id', 'vendor_name', 'phone', 'email', 'website')
                              ->paginate($perPage);

            // Return JSON response with paginated vendors and pagination information
            return response()->json([
                'status' => 200,
                'vendors' => $vendors->items(),
                'pagination' => [
                    'total' => $vendors->total(),
                    'title' => 'Vendors',
                    'per_page' => $vendors->perPage(),
                    'current_page' => $vendors->currentPage(),
                    'last_page' => $vendors->lastPage(),
                    'from' => $vendors->firstItem(),
                    'to' => $vendors->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions and return a JSON error response
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }



    public function store(Request $request)
    {
        DB::beginTransaction(); // Start a transaction to ensure atomic operations
    
        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'vendor_name' => 'required|string',
                'phone' => 'nullable|string',
                'email' => 'required|string|email',
                'website' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
            ]);
    
            // Create Crmentity record
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Vendors', $validatedData['vendor_name']);
    
            if (!$crmid) {
                throw new \Exception('Failed to create Crmentity');
            }
    
            // Add crmid to validated data
            $validatedData['id'] = $crmid;
    
            // Create the Vendor record with crmid
            $vendor = Vendors::create($validatedData);
    
            DB::commit(); // Commit the transaction
    
            return response()->json($vendor, 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Rollback the transaction on validation error
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction on general error
            Log::error('Failed to create vendor: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Failed to create vendor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    


    public function show($id)
    {
        try {
            $vendor = Vendors::findOrFail($id);
            return response()->json($vendor);
        } catch (Exception $e) {
            Log::error('Failed to fetch vendor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch vendor'], 500);
        }
    }


    public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // Validate request data
        $validatedData = $request->validate([
            'vendor_name' => 'nullable|string',
            'phone' => 'nullable|integer',
            'email' => 'nullable|string|email',
            'website' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
        ]);

        // Find and update the vendor
        $vendor = Vendors::findOrFail($id);
        $vendor->update($validatedData);

        // Prepare Crmentity update data
        $crmentityData = [
            'label' => $validatedData['vendor_name'], // Use vendor name for the label
            // Add other fields from vendor if necessary
        ];

        // Update or create Crmentity record
        $crmentity = Crmentity::where('crmid', $id)->first();

        if ($crmentity) {
            // Update existing Crmentity record
            $crmentity->update($crmentityData);
        } else {
            // Create a new Crmentity record if it does not exist
            $crmentity = new Crmentity();
            $crmentity->crmid = $id; // Use a unique identifier
            $crmentity->label = $validatedData['vendor_name'];
            $crmentity->save();
        }

        // Commit transaction
        DB::commit();

        return response()->json([
            'message' => 'Vendor and Crmentity updated successfully',
            'vendor' => $vendor,
            'crmentity' => $crmentity,
        ], 200);

    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json(['error' => 'Validation error', 'details' => $e->errors()], 422);
    } catch (ModelNotFoundException $e) {
        DB::rollBack();
        return response()->json(['error' => 'Vendor not found'], 404);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update vendor and Crmentity: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
        return response()->json(['error' => 'Failed to update vendor and Crmentity'], 500);
    }
}


        public function destroy($id)
    {
        try {
            $vendor = Vendors::findOrFail($id);
            $vendor->delete();
            return response()->json(['message' => 'Vendor deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete vendor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete vendor'], 500);
        }
    }
    public function search(Request $request)
{
    try {
        // Validate the search input
        $validatedData = $request->validate([
            'vendor_name' => 'nullable|string',
            'email' => 'nullable|string|email',
            'per_page' => 'nullable|integer|min:1', // Add validation for per_page
        ]);

        // Initialize the query builder
        $query = Vendors::query();

        // Apply search filters
        if (!empty($validatedData['name'])) {
            $query->where('name', 'like', '%' . $validatedData['name'] . '%');
        }
        if (!empty($validatedData['email'])) {
            $query->where('email', $validatedData['email']);
        }

        // Paginate the search results
        $perPage = $validatedData['per_page'] ?? 10; // default per_page value
        $vendors = $query->paginate($perPage);

        // Check if any vendors found
        if ($vendors->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No matching records found',
            ], 404);
        }

        // Return JSON response with search results and pagination information
        return response()->json([
            'status' => 200,
            'vendors' => $vendors->items(),
            'pagination' => [
                'total' => $vendors->total(),
                'per_page' => $vendors->perPage(),
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'from' => $vendors->firstItem(),
                'to' => $vendors->lastItem(),
            ],
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Failed to search vendors: ' . $e->getMessage());
        // Return a generic server error response
        return response()->json(['message' => 'Server Error'], 500);
    }
}

}

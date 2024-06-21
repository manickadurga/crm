<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Customers;
use App\Models\Projects;
use App\Models\Tags;
use Illuminate\Support\Facades\File;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Set the number of items per page, default is 10
            $perPage = $request->input('per_page', 10);
    
            // Get paginated customers with specific fields including 'id'
            $customers = Customers::select('id', 'name', 'primary_phone', 'primary_email', 'projects', 'location')
                ->paginate($perPage);
    
            // Prepare array to hold formatted customers
            $formattedCustomers = [];
    
            // Iterate through each customer to format data
            foreach ($customers as $customer) {
                // Ensure projects and location are valid JSON strings before decoding
                $projectsArray = is_string($customer->projects) ? json_decode($customer->projects, true) : [];
                $locationArray = is_string($customer->location) ? json_decode($customer->location, true) : [];
    
                // Build formatted customer array and embed 'id'
                $formattedCustomers[] = [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'primary_phone' => $customer->primary_phone,
                        'primary_email' => $customer->primary_email,
                        'projects' => $projectsArray,
                        'country' => $locationArray['country'] ?? null,
                        'city' => $locationArray['city'] ?? null,
                ];
            }
    
            // Return JSON response with formatted data and pagination information
            return response()->json([
                'status' => 200,
                'customers' => $formattedCustomers,
                'pagination' => [
                    'total' => $customers->total(),
                    'per_page' => $customers->perPage(),
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'from' => $customers->firstItem(),
                    'to' => $customers->lastItem(),
                ],
            ], 200);
    
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve customers: ' . $e->getMessage());
    
            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve customers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'name' => 'required|string',
                'primary_email' => 'nullable|email',
                'primary_phone' => 'nullable|string',
                'website' => 'nullable|url',
                'fax' => 'nullable|string',
                'fiscal_information' => 'nullable|string',
                'projects' => 'nullable|array|max:5000',
                'projects.*' => 'exists:jo_projects,id',
                'contact_type' => 'nullable|string|max:5000',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'location' => 'nullable|array|max:5000',
                'location.country' => 'nullable|string',
                'location.city' => 'nullable|string',
                'location.address' => 'nullable|string',
                'location.postal_code' => 'nullable|string',
                'location.longitude' => 'nullable|numeric',
                'location.latitude' => 'nullable|numeric',
                'type' => 'nullable|integer',
                'type_suffix' => 'nullable|in:cost,hours',
            ]);
    
            // Process image if provided
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
                $validatedData['image'] = $imageName; // Save $imageName to database
            }
    
            // Ensure 'location' is stored as JSON
            if (isset($validatedData['location'])) {
                $validatedData['location'] = json_encode($validatedData['location']);
            }
    
            // Ensure 'projects' is stored as JSON
            if (isset($validatedData['projects'])) {
                $projects = [];
                foreach ($validatedData['projects'] as $id) {
                    $project = Projects::find($id);
                    if ($project) {
                        $projects[] = $project->project_name;
                    } else {
                        throw ValidationException::withMessages(['projects' => "Project with ID '$id' not found"]);
                    }
                }
                $validatedData['projects'] = json_encode($projects);
            }
    
            // Ensure 'tags' is stored as JSON
            if (isset($validatedData['tags'])) {
                $tags = [];
                foreach ($validatedData['tags'] as $id) {
                    $tag = Tags::find($id);
                    if ($tag) {
                        $tags[] = [
                            'tags_name' => $tag->tags_name,
                            'tag_color' => $tag->tag_color,
                        ];
                    } else {
                        throw ValidationException::withMessages(['tags' => "Tag with ID '$id' not found"]);
                    }
                }
                $validatedData['tags'] = json_encode($tags);
            }
    
            // Create a new customer record in the database
            Customers::create($validatedData);
    
            // Return a success response
            return response()->json(['message' => 'Customer created successfully']);
    
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to create customer: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create customer: ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id)
{
    try {
        $customer = Customers::findOrFail($id);

        // Decode JSON fields if they are stored as JSON strings
        $customer->location = is_string($customer->location) ? json_decode($customer->location, true) : [];
        $customer->tags = is_string($customer->tags) ? json_decode($customer->tags, true) : [];
        $customer->projects = is_string($customer->projects) ? json_decode($customer->projects, true) : [];

        return response()->json(['status' => 200, 'customer' => $customer], 200);
    } catch (ModelNotFoundException $e) {
        Log::warning('Customer not found: ' . $id);
        return response()->json(['status' => 404, 'message' => 'Customer not found'], 404);
    } catch (Exception $e) {
        Log::error('Failed to retrieve customer details: ' . $e->getMessage());
        return response()->json(['status' => 500, 'message' => 'Failed to retrieve customer details'], 500);
    }
}
public function update(Request $request, string $id)
{
    try {
        $customer = Customers::findOrFail($id);

        // Log incoming request data
        Log::info('Request data:', $request->all());

        // Validate the incoming request data
        $validatedData = $request->validate([
            'image' => 'nullable|string', // Expecting a base64 string
            'name' => 'nullable|string',
            'primary_email' => 'nullable|email',
            'primary_phone' => 'nullable|string',
            'website' => 'nullable|url',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'projects' => 'nullable|array|max:5000',
            'projects.*' => 'exists:jo_projects,id',
            'contact_type' => 'nullable|string|max:5000',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'location' => 'nullable|array|max:5000',
            'location.country' => 'nullable|string',
            'location.city' => 'nullable|string',
            'location.address' => 'nullable|string',
            'location.postal_code' => 'nullable|string',
            'location.longitude' => 'nullable|numeric',
            'location.latitude' => 'nullable|numeric',
            'type' => 'nullable|integer',
            'type_suffix' => 'nullable|in:cost,hours',
        ]);

        // Process image if provided
        if ($request->has('image')) {
            $imageData = $request->get('image');

            // Delete old image if it exists
            if ($customer->image) {
                $oldImagePath = public_path('images/' . $customer->image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }

            // Decode the base64 string and save the image
            $imageName = time() . '.jpg'; // You can change the extension based on the actual file type
            $imagePath = public_path('images/' . $imageName);

            // Extract the base64 data
            $imageData = explode(',', $imageData)[1];
            File::put($imagePath, base64_decode($imageData));

            $validatedData['image'] = $imageName; // Save $imageName to database
        }

        if (isset($validatedData['location'])) {
            $validatedData['location'] = json_encode($validatedData['location']);
        }

        // Log validated data
        Log::info('Validated data before update:', $validatedData);

        // Update customer data
        $customer->update($validatedData);

        // Log after update
        Log::info('Customer updated successfully:', $customer->toArray());

        // Return success response
        return response()->json(['message' => 'Customer updated successfully']);
    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Customer not found'], 404);
    } catch (ValidationException $e) {
        // Log validation errors
        Log::error('Validation errors:', $e->validator->errors()->toArray());
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        Log::error('Failed to update customer: ' . $e->getMessage());
        return response()->json(['message' => 'An unexpected error occurred while processing your request. Please try again later.'], 500);
    }
}
    public function destroy(string $id)
    {
        try {
            $customer = Customers::findOrFail($id);
            $customer->delete();
            return response()->json(['message' => 'Customer deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete customer: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred while processing your request. Please try again later.'], 500);
        }
    }

    public function search(Request $request)
{
    try {
        // Validate the search input
        $validatedData = $request->validate([
            'name' => 'nullable|string',
            'primary_email' => 'nullable|email',
            'primary_phone' => 'nullable|string',
            'website' => 'nullable|url',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'projects' => 'nullable|array|max:5000',
            'projects.*' => 'exists:projects,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id', // Validate each tag ID exists in the tags table
            'location' => 'nullable|array|max:5000',
            'location.country' => 'nullable|string',
            'location.city' => 'nullable|string',
            'location.address' => 'nullable|string',
            'location.postal_code' => 'nullable|string',
            'location.longitude' => 'nullable|numeric',
            'location.latitude' => 'nullable|numeric',
            'type' => 'nullable|integer',
            'type_suffix' => 'nullable|in:cost,hours',
            'per_page' => 'nullable|integer|min:1', // Add validation for per_page
        ]);

        // Initialize the query builder
        $query = Customers::query();

        // Apply search filters
        foreach ($validatedData as $key => $value) {
            if ($value !== null && in_array($key, ['name', 'primary_email', 'primary_phone', 'website', 'fax', 'fiscal_information', 'type'])) {
                $query->where($key, 'like', '%' . $value . '%');
            }

            if ($key === 'projects' && $value !== null) {
                $query->whereHas('projects', function ($q) use ($value) {
                    $q->whereIn('project_name', $value);
                });
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
        $customers = $query->paginate($perPage);

        // Decode location for each customer
        foreach ($customers as $customer) {
            $customer->location = json_decode($customer->location, true);
            $customer->tags=json_decode($customer->tags,true);
            $customer->projects=json_decode($customer->projects,true);
        }

        // Check if any customers found
        if ($customers->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No matching records found',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'customers' => $customers->items(),
            'pagination' => [
                'total' => $customers->total(),
                'per_page' => $customers->perPage(),
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
            ],
        ], 200);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        Log::error('Failed to search customers: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to search customers: ' . $e->getMessage()], 500);
    }
}
}

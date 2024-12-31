<?php

namespace App\Http\Controllers;

use App\Events\ContactCreated;
use App\Events\ContactTag;
use App\Events\ContactUpdated;
use App\Events\TagUpdated;
use App\Mail\SendEmailAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\Customers;
use App\Models\EmailAction;
use App\Models\Projects;
use App\Models\SmsAction;
use App\Models\Tags;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

        // Get paginated customers with specific fields including 'id', 'name', 'primary_phone', 'primary_email', 'projects', 'location'
        $customers = Customers::select('id', 'name', 'primary_phone', 'primary_email', 'projects', 'country','city')
            ->paginate($perPage);

        // Prepare array to hold formatted customers
        $formattedCustomers = [];

        // Iterate through each customer to format data
        foreach ($customers as $customer) {
            // Initialize arrays
            $projects = [];
            //$location = [];

            // Handle projects field
            if (!empty($customer->projects)) {
                // Decode projects field if it's a string
                $projectIds = is_string($customer->projects) ? json_decode($customer->projects) : $customer->projects;

                // Fetch project names using project IDs
                $projectNames = Projects::whereIn('id', $projectIds)
                    ->pluck('project_name')
                    ->toArray();

                // Combine project names into a comma-separated string
                $projects = implode(',', $projectNames);
            }

            // Decode location field if it's a string
            // if (!empty($customer->location)) {
            //     $location = json_decode($customer->location, true);
            //     if (!is_array($location)) {
            //         throw new \RuntimeException('Invalid JSON format for location');
            //     }
            // }

            // Build formatted customer array and embed 'id'
            $formattedCustomers[] = [
                'id' => $customer->id,
                'name' => $customer->name,
                'primary_phone' => $customer->primary_phone,
                'primary_email' => $customer->primary_email,
                'projects' => $projects,
                'country' => $customer->country,
                'city' => $customer->city,
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
        $validatedData = Validator::make($request->all(), [
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'primary_email' => 'nullable|string|email|max:255',
            'primary_phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:20',
            'fiscal_information' => 'nullable|string',
            'projects' => 'nullable|array',
            'projects.*'=>'exists:jo_projects,id',
            'contact_type' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'post_code' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'type' => 'nullable|integer',
            'type_suffix' => 'nullable|in:cost,hours',
        ])->validate();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = $imageName;
        }

        // Create Crmentity record via CrmentityController
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Customers', $validatedData['name']);

        // Create the customer with the crmid
        $validatedData['id'] = $crmid; // Add crmid to customer data
        $customer = Customers::create($validatedData);
        $customer->update($validatedData);
        event(new ContactCreated($customer));
        return response()->json([
            'message' => 'Customer created successfully',
            'customer' => $customer,
        ], 201);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to create customer',
            'message' => $e->getMessage(),
        ], 500);
    }
}
public function show(string $id)
{
    try {
        $customer = Customers::findOrFail($id);

        // Decode JSON fields if they are stored as JSON strings
        //$customer->location = is_string($customer->location) ? json_decode($customer->location, true) : [];
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


public function update(Request $request, $id)
{
    try {
        // Validate the incoming request data
        $validatedData = Validator::make($request->all(), [
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'nullable|string|max:255',
            'primary_email' => 'nullable|string|email|max:255',
            'primary_phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:20',
            'fiscal_information' => 'nullable|string',
            'projects' => 'nullable|array',
            'projects.*' => 'exists:jo_projects,id',
            'contact_type' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'post_code' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'type' => 'nullable|integer',
            'type_suffix' => 'nullable|in:cost,hours',
        ])->validate();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $validatedData['image'] = $imageName;
        }

        // Find the customer by ID
        $customer = Customers::findOrFail($id);
        // Capture the current tags before updating
        $tagsBeforeUpdate = $customer->tags ?? [];

        // Update customer data
        $customer->update($validatedData);
        // Check if tags have changed
        if (isset($request->tags)) {
        $updatedTags = $request->tags;
        
        // Determine which tags were added and which were removed
        $addedTags = array_diff($updatedTags, $tagsBeforeUpdate);
        $removedTags = array_diff($tagsBeforeUpdate, $updatedTags);
        
        // Fire the event for tags added
        foreach ($addedTags as $tagId) {
            $tag = Tags::find($tagId);
            event(new TagUpdated($customer, $tag, 'tag_added'));
        }

        // Fire the event for tags removed
        foreach ($removedTags as $tagId) {
            $tag = Tags::find($tagId);
            event(new TagUpdated($customer, $tag, 'tag_removed'));
        }
    }
        event(new ContactUpdated($customer));
        // Dispatch the event
        event(new ContactTag($customer, $tagsBeforeUpdate));
       

        // Optional: Update Crmentity record if name is provided
        if (isset($validatedData['name'])) {
            $crmentityController = new CrmentityController();
            $updatedCrmentity = $crmentityController->updateCrmentity($customer->id, [
                'label' => $validatedData['name'],
            ]);

            if (!$updatedCrmentity) {
                throw new Exception('Failed to update Crmentity');
            }
        }

        return response()->json([
            'message' => 'Customer updated successfully',
            'customer' => $customer,
        ], 200);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        Log::error("Failed to update customer: " . $e->getMessage());
        return response()->json([
            'error' => 'Failed to update customer',
            'message' => $e->getMessage(),
        ], 500);
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
public function showImported()
{
    $customers = Customers::all();  // Assuming you want to display all customers including the imported ones
    return view('customers.show_imported', compact('customers'));
}
public function addTag(Request $request, $contactId, $tagId)
{
    // Find the contact
    $contact = Customers::findOrFail($contactId);

    // Get the existing tags or initialize as an empty array if null
    $tags = is_array($contact->tags) ? $contact->tags : json_decode($contact->tags, true) ?? [];

    // Check if the tag already exists to avoid duplicates
    if (!in_array($tagId, $tags)) {
        // Add the tagId to the tags array
        $tags[] = $tagId;

        // Save the updated tags array back to the database
        $contact->tags = json_encode($tags); // Save as JSON string
        $contact->save();

        // Dispatch the 'TagUpdated' event (for tag addition)
        event(new TagUpdated($contact, $tagId, 'tag_added'));

        return response()->json(['message' => 'Tag added successfully!', 'tags' => $tags]);
    }

    return response()->json(['message' => 'Tag already exists!', 'tags' => $tags], 400);
}
public function removeTag(Request $request, $contactId, $tagId)
{
    // Find the contact
    $contact = Customers::findOrFail($contactId);

    // Get the existing tags as an array
    $tags = is_array($contact->tags) ? $contact->tags : json_decode($contact->tags, true) ?? [];

    // Check if the tag exists in the array
    if (($key = array_search($tagId, $tags)) !== false) {
        // Remove the tag from the array
        unset($tags[$key]);

        // Re-index the array to ensure proper structure and save it back as a JSON string
        $contact->tags = json_encode(array_values($tags));
        $contact->save();

        // Dispatch the 'TagUpdated' event (for tag removal)
        event(new TagUpdated($contact, $tagId, 'tag_removed'));

        return response()->json(['message' => 'Tag removed successfully!', 'tags' => $tags]);
    }

    return response()->json(['message' => 'Tag not found!', 'tags' => $tags], 404);
}
}


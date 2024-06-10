<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Customers;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $customers = Customers::all();
            if ($customers->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'customers' => $customers,
            ], 200);
        } catch (Exception $e) {
            
            // Log the error
            Log::error('Failed to retrieve customers: ' . $e->getMessage());
    
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
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'name' => 'required|string',
                'primary_email' => 'nullable|email',
                'primary_phone' => 'nullable|string',
                'website' => 'nullable|url',
                'fax' => 'nullable|string',
                'fiscal_information' => 'nullable|string',
                'projects' => 'nullable|array|max:5000',
                'contact_type' => 'nullable|string|max:5000',
                'tags' => 'nullable|array|max:5000',
                'location' => 'nullable|array|max:5000',
                'type' => 'nullable|string',
                'type_suffix' => 'nullable|in:cost,hours',
                'orgid'=>'nullable|numeric'
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('images', 'public');
                $validatedData['image'] = $imagePath;
            }

            // Ensure 'location' is stored as JSON
            if (isset($validatedData['location'])) {
                $validatedData['location'] = json_encode($validatedData['location']);
            }

            // Create a new customer record in the database
            Customers::create($validatedData);

            // Return a success response
            return response()->json(['message' => 'Customer created successfully']);
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to create customer: ' . $e->getMessage());

            // Return an error response with the actual error message
            return response()->json(['error' => 'Failed to create customer: ' . $e->getMessage()], 500);
        }
    }
   public function show(string $id)
   {
    try {
        $customer = Customers::findOrFail($id);
        return response()->json(['status' => 200, 'customer' => $customer], 200);
    } catch (ModelNotFoundException $e) {
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
    
            // Validate the incoming request data
            $validatedData = $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'name' => 'required|string',
                'primary_email' => 'nullable|email',
                'primary_phone' => 'nullable|string',
                'website' => 'nullable|url',
                'fax' => 'nullable|string',
                'fiscal_information' => 'nullable|string',
                'projects' => 'nullable|array|max:5000',
                'contact_type' => 'nullable|string|max:5000',
                'tags' => 'nullable|array|max:5000',
                'location' => 'nullable|array|max:5000',
                'type' => 'nullable|string',
                'type_suffix' => 'nullable|in:cost,hours',
                'orgid'=>'nullable|numeric'
            ]);
    
            // Update customer data
            $customer->update($validatedData);
    
            // Return success response
            return response()->json(['message' => 'Customer updated successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer not found'], 404);
        } catch (ValidationException $e) {
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
}

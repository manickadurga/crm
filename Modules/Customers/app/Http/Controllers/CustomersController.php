<?php

namespace Modules\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Customers\Models\Customers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;


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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $customers = $request->validate([
                'image'=>'nullable|binary',
                'name' => 'required|string',
                'primary_email' => 'nullable|email',
                'primary_phone' => 'nullable|string',
                'website' => 'nullable|url',
                'fax'=>'nullable|string',
                'fiscal_information' => 'nullable|string',
                'projects' => 'nullable|array|max:5000',
                'contact_type' => 'nullable|string|max:5000',
                'tags' => 'nullable|array|max:5000',
                'find_address' => 'nullable|string',
                'country' => 'nullable|string',
                'city' => 'nullable|string',
                'postal_code' => 'nullable|string',
                'address' => 'nullable|string',
                'address_2' => 'nullable|string',
                'coordinates' => 'nullable|boolean',
                //'type' => 'nullable|string|max:5000',
                'dynamic_fields' => 'nullable|array|max:5000',
                'dynamic_fields.*.type' => 'required|string',
                'dynamic_fields.*.value' => 'required|numeric',
                //'dynamic_value' => 'nullable|numeric',
            ]);
            

            // Create a new customer record in the database
            Customers::create($customers);

            // Return a success response
            return response()->json(['message' => 'Customer created successfully']);
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to create customer: ' . $e->getMessage());

            // Return an error response with the actual error message
            
            return response()->json(['error' => 'Failed to create customer: ' . $e->getMessage()], 500);
        }
    
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $customers = Customers::findOrFail($id);
            return response()->json($customers);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Invoice not found'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
   /**
 * Show the form for editing the specified resource.
 */
public function edit($id)
{
    try {
        // Retrieve the customer record by ID
        $customer = Customers::findOrFail($id);

        // Return the customer data as JSON response
        return response()->json(['customer' => $customer], 200);
    } catch (ModelNotFoundException $e) {
        // Return a not found error response if the customer is not found
        return response()->json(['message' => 'Customer not found', 'error' => $e->getMessage()], 404);
    } catch (Exception $e) {
        // Return an error response if a general exception occurs
        return response()->json(['message' => 'Failed to retrieve customer for editing', 'error' => $e->getMessage()], 500);
    }
}
public function update(Request $request, $id)
    {
        try {
            $customer = Customers::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string',
                'primary_email' => 'nullable|email',
                'primary_phone' => 'nullable|string',
                'website' => 'nullable|url',
                'fax' => 'nullable|string',
                'fiscal_information' => 'nullable|string',
                'projects' => 'nullable|array|max:5000',
                'contact_type' => 'nullable|string|max:5000',
                'tags' => 'nullable|array|max:5000',
                'find_address' => 'nullable|string',
                'country' => 'nullable|string',
                'city' => 'nullable|string',
                'postal_code' => 'nullable|string',
                'address' => 'nullable|string',
                'address_2' => 'nullable|string',
                'coordinates' => 'nullable|boolean',
                'dynamic_fields' => 'nullable|array|max:5000',
                'dynamic_fields.*.type' => 'required|string',
                'dynamic_fields.*.value' => 'required|numeric',
            ]);

            $customer->update($validatedData);

            return response()->json(['message' => 'Customer updated successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update customer: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred while processing your request. Please try again later.'], 500);
        }
    }
    public function destroy($id)
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


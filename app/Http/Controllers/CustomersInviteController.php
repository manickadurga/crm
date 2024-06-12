<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Mail\InviteMail;
use Illuminate\Support\Facades\Mail;
use App\Models\CustomersInvite;


class CustomersInviteController extends Controller
{
    public function index()
    {
        try {
            $customers = CustomersInvite::all();
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
            $validated = $request->validate([
                'contact_name' => 'required|string|max:255',
                'primary_phone' => 'required|string',
                'email' => 'required|email',
                'orgid'=>'nullable|numeric'
            ]);

            // Create a new customer invite entry
            $customersInvite = CustomersInvite::create($validated);

            // Send email to the provided email address
            Mail::to($validated['email'])->send(new InviteMail($validated));

            return response()->json($customersInvite, 201);
        } catch (Exception $e) {
            Log::error('Failed to store customer invite: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to store customer invite'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'contact_name' => 'required|string|max:255',
                'primary_phone' => 'required|string',
                'email' => 'required|email',
                'orgid'=>'nullable|numeric'
            ]);

            // Find the customer invite by ID
            $customersInvite = CustomersInvite::findOrFail($id);

            // Update the customer invite with new data
            $customersInvite->update($validated);

            return response()->json(['message' => 'Customer invite updated successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer invite not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update customer invite: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update customer invite'], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $customersinvite = CustomersInvite::findOrFail($id);
            $customersinvite->delete();
            return response()->json(['message' => 'Customer deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Customer not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete customer: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred while processing your request. Please try again later.'], 500);
        }
    }
}

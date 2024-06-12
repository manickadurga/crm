<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Payments;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $payments = Payments::all();
            if ($payments->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'payments' => $payments,
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve payments: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve payments',
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
            $validator = Validator::make($request->all(), [
                'invoice_number' => 'nullable|integer',
                'contacts' => 'required|string',
                'projects' => 'required|string',
                'payment_date' => 'nullable|date',
                'payment_method' => 'required|string',
                'currency' => 'nullable|string',
                'tags' => 'nullable|array',
                'amount' => 'required|numeric',
                'note' => 'nullable|string',
                'orgid' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            Payments::create($request->all());

            return response()->json(['message' => 'Payment created successfully'], 201);
        } catch (Exception $e) {
            Log::error('Failed to create payment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Find the payment by ID
            $payment = Payments::findOrFail($id);
            
            // Return the payment as JSON response
            return response()->json(['payment' => $payment], 200);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the payment is not found
            return response()->json(['message' => 'Payment not found'], 404);
        } catch (Exception $e) {
            // Handle other exceptions
            Log::error('Failed to retrieve payment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'invoice_number' => 'nullable|integer',
                'contacts' => 'required|string',
                'projects' => 'required|string',
                'payment_date' => 'nullable|date',
                'payment_method' => 'required|string',
                'currency' => 'nullable|string',
                'tags' => 'nullable|array',
                'amount' => 'required|numeric',
                'note' => 'nullable|string',
                'orgid' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Find the payment by ID
            $payment = Payments::findOrFail($id);

            // Update the payment with new data
            $payment->update($request->all());

            return response()->json(['message' => 'Payment updated successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Payment not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update payment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $payments = Payments::findOrFail($id);
            $payments->delete();
            return response()->json(['message' => 'Payment deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Payment not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete payment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete payment: ' . $e->getMessage()], 500);
        }
    }
}

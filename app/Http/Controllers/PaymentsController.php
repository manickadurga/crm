<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
//use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Payments;
//use App\Models\Tags;

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
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'nullable|integer|exists:jo_invoices,invoicenumber',
            'contacts' => 'required|string',
            'projects' => 'required|string|exists:jo_projects,name',
            'payment_date' => 'nullable|date',
            'payment_method' => 'required|string',
            'currency' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        if (isset($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        try {
            $payment = Payments::create($data);
            return response()->json(['message' => 'Payment created successfully', 'payment' => $payment], 201);
        } catch (Exception $e) {
            Log::error('Failed to create payment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create payment'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $payment = Payments::findOrFail($id);
            return response()->json(['payment' => $payment], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Payment not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve payment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve payment'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'nullable|integer|exists:jo_invoices,invoicenumber',
            'contacts' => 'required|string',
            'projects' => 'required|string|exists:jo_projects,name',
            'payment_date' => 'nullable|date',
            'payment_method' => 'required|string',
            'currency' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        if (isset($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        try {
            $payment = Payments::findOrFail($id);
            $payment->update($data);
            return response()->json(['message' => 'Payment updated successfully', 'payment' => $payment], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Payment not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update payment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update payment'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $payment = Payments::findOrFail($id);
            $payment->delete();
            return response()->json(['message' => 'Payment deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Payment not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete payment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete payment'], 500);
        }
    }
}

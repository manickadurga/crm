<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Vendor;
use App\Models\Vendors;
use Exception;
use Illuminate\Validation\ValidationException;

class VendorsController extends Controller
{
    
    public function index()
    {
        try {
            $vendors = Vendors::all();
            if ($vendors->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'vendors' => $vendors,
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve vendors: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve vendors',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'phone' => 'nullable|integer',
                'email' => 'required|string|email',
                'website' => 'nullable|string',
                'tags' => 'nullable|array',
                'orgid' => 'nullable|integer',
            ]);

            $vendor = Vendors::create($validatedData);
            return response()->json($vendor, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Failed to create vendor: ' . $e->getMessage()], 422);
        } catch (Exception $e) {
            Log::error('Failed to create vendor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create vendor'], 500);
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
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'phone' => 'nullable|integer',
                'email' => 'required|string|email',
                'website' => 'nullable|string',
                'tags' => 'nullable|array',
                'orgid' => 'nullable|integer',
            ]);

            $vendor = Vendors::findOrFail($id);
            $vendor->update($validatedData);
            return response()->json($vendor);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Failed to update vendor: ' . $e->getMessage()], 422);
        } catch (Exception $e) {
            Log::error('Failed to update vendor: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update vendor'], 500);
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
}

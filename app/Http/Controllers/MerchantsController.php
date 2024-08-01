<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Merchants;
use App\Models\Tags;

class MerchantsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve paginated merchants
            $merchants = Merchants::paginate(10); // Adjust 10 to the number of merchants per page you want

            // Check if any merchants found
            if ($merchants->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'merchants' => $merchants->items(),
                'pagination' => [
                    'total' => $merchants->total(),
                    'per_page' => $merchants->perPage(),
                    'current_page' => $merchants->currentPage(),
                    'last_page' => $merchants->lastPage(),
                    'from' => $merchants->firstItem(),
                    'to' => $merchants->lastItem(),
                ],
            ], 200);

        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve merchants: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve merchants',
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
            'image' => 'nullable|string',
            'name' => 'nullable|string',
            'code' => 'nullable|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'currency' => 'nullable|string',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'website' => 'nullable|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'is_active' => 'boolean',
            'location' => 'nullable|array',
            'warehouses' => 'nullable|exists:jo_warehouses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        if (isset($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        try {
            $merchant = Merchants::create($data);
            return response()->json(['message' => 'Merchant created successfully', 'merchant' => $merchant], 201);
        } catch (Exception $e) {
            Log::error('Failed to create merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create merchant'], 500);
        }
    }
    public function show($id)
    {
        try {
            $merchant = Merchants::findOrFail($id);
            return response()->json(['merchant' => $merchant], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Merchant not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to fetch merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch merchant: ' . $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|string',
            'name' => 'nullable|string',
            'code' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'currency' => 'nullable|string',
            'fax' => 'nullable|string',
            'fiscal_information' => 'nullable|string',
            'website' => 'nullable|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'is_active' => 'boolean',
            'location' => 'nullable|array',
            'warehouses' => 'nullable|exists:jo_warehouses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        if (isset($data['tags'])) {
            $data['tags'] = json_encode($data['tags']);
        }

        try {
            $merchant = Merchants::findOrFail($id);
            $merchant->update($data);
            return response()->json(['message' => 'Merchant updated successfully', 'merchant' => $merchant], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Merchant not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update merchant'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $merchant = Merchants::findOrFail($id);
            $merchant->delete();
            return response()->json(['message' => 'Merchant deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Merchant not found'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete merchant: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete merchant'], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'image' => 'nullable|string',
                'name' => 'nullable|string',
                'code' => 'nullable|string',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'currency' => 'nullable|string',
                'fax' => 'nullable|string',
                'fiscal_information' => 'nullable|string',
                'website' => 'nullable|string',
                'description' => 'nullable|string',
               'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
                'is_active' => 'boolean',
                'location' => 'nullable|array',
                'warehouses' => 'nullable|string|exists:jo_warehouses,id',
                'per_page' => 'nullable|integer|min:1', // Add validation for per_page
            ]);
            $query = Merchants::query();
            foreach ($validatedData as $key => $value) {
                if ($value !== null && in_array($key, [
                    'image', 'name', 'code', 'email', 'phone', 'currency',
                    'fax', 'fiscal_information', 'website', 'description',
                    'is_active', 'location', 'warehouses'
                ])) {
                    if (is_array($value)) {
                        foreach ($value as $item) {
                            $query->where($key, 'like', '%' . $item . '%');
                        }
                    } else {
                        $query->where($key, 'like', '%' . $value . '%');
                    }
                }
            }
            $perPage = $validatedData['per_page'] ?? 10;
            $merchants = $query->paginate($perPage);
            if ($merchants->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'merchants' => $merchants->items(),
                'pagination' => [
                    'total' => $merchants->total(),
                    'per_page' => $merchants->perPage(),
                    'current_page' => $merchants->currentPage(),
                    'last_page' => $merchants->lastPage(),
                    'from' => $merchants->firstItem(),
                    'to' => $merchants->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search merchants: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search merchants: ' . $e->getMessage()], 500);
        }
    }
}

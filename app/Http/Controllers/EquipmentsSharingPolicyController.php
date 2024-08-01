<?php

namespace App\Http\Controllers;

use App\Models\EquipmentsSharingPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class EquipmentsSharingPolicyController extends Controller
{
    public function index()
    {
        try {
            $policies = EquipmentsSharingPolicy::paginate(10);
            if ($policies->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'policies' => $policies->items(),
                'pagination' => [
                    'total' => $policies->total(),
                    'per_page' => $policies->perPage(),
                    'current_page' => $policies->currentPage(),
                    'last_page' => $policies->lastPage(),
                    'from' => $policies->firstItem(),
                    'to' => $policies->lastItem(),
                ],
            ], 200);

        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve policies: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve policies',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'required_fields' => ['name', 'description']], 400);
        }

        try {
            $policy = EquipmentsSharingPolicy::create($validator->validated());
            return response()->json($policy, 201);
        } catch (Exception $e) {
            Log::error('Failed to create equipment sharing policy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create equipment sharing policy'], 500);
        }
    }
    public function show($id)
    {
        try {
            $policy = EquipmentsSharingPolicy::findOrFail($id);
            return response()->json($policy);
        } catch (Exception $e) {
            Log::error('Failed to fetch equipment sharing policy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch equipment sharing policy'], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'required_fields' => ['name', 'description', 'orgid']], 400);
        }

        try {
            $policy = EquipmentsSharingPolicy::findOrFail($id);
            $policy->update($validator->validated());
            return response()->json($policy);
        } catch (Exception $e) {
            Log::error('Failed to update equipment sharing policy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update equipment sharing policy'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $policy = EquipmentsSharingPolicy::findOrFail($id);
            $policy->delete();
            return response()->json(['message' => 'Equipment sharing policy deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete equipment sharing policy: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete equipment sharing policy'], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            // Validate the search input
            $validatedData = $request->validate([
                'name' => 'nullable|string',
                'description' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1',
            ]);
            $query = EquipmentsSharingPolicy::query();
            foreach ($validatedData as $key => $value) {
                if ($value !== null && in_array($key, ['name', 'description'])) {
                    $query->where($key, 'like', '%' . $value . '%');
                }
            }
            $perPage = $validatedData['per_page'] ?? 10;
            $policies = $query->paginate($perPage);
            if ($policies->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }
            return response()->json([
                'status' => 200,
                'policies' => $policies->items(),
                'pagination' => [
                    'total' => $policies->total(),
                    'per_page' => $policies->perPage(),
                    'current_page' => $policies->currentPage(),
                    'last_page' => $policies->lastPage(),
                    'from' => $policies->firstItem(),
                    'to' => $policies->lastItem(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to search equipment sharing policies: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search equipment sharing policies: ' . $e->getMessage()], 500);
        }
    }
}

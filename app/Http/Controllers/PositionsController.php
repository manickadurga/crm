<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PositionsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);

            $positions = Position::paginate($perPage);

            return response()->json([
                'status' => 200,
                'positions' => $positions->items(),
                'pagination' => [
                    'total' => $positions->total(),
                    'per_page' => $positions->perPage(),
                    'current_page' => $positions->currentPage(),
                    'last_page' => $positions->lastPage(),
                    'from' => $positions->firstItem(),
                    'to' => $positions->lastItem(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching positions: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching positions', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'position_name' => 'required|string|max:255',
                'tags' => 'nullable|array|max:5000',
                'tags.*' => 'exists:jo_tags,id',
            ]);

            $position = Position::create($validatedData);

            DB::commit();

            return response()->json(['message' => 'Position created successfully', 'position' => $position], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create position: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create position: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $position = Position::findOrFail($id);
            return response()->json($position);
        } catch (\Exception $e) {
            Log::error('Error fetching position: ' . $e->getMessage());
            return response()->json(['message' => 'Position not found', 'error' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $position = Position::findOrFail($id);

            $validatedData = $request->validate([
                'position_name' => 'nullable|string|max:255',
                'tags' => 'nullable|array|max:5000',
                'tags.*' => 'exists:jo_tags,id',
            ]);

            $position->update($validatedData);

            DB::commit();

            return response()->json(['message' => 'Position updated successfully', 'position' => $position], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update position: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update position: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $position = Position::findOrFail($id);
            $position->delete();
            return response()->json(['message' => 'Position deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Error deleting position: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting position', 'error' => $e->getMessage()], 500);
        }
    }
}

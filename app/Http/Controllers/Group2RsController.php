<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Group2Rs;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class Group2RsController extends Controller
{
    public function index()
    {
        try {
            $relations = Group2Rs::all();
            return response()->json(['relations' => $relations], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve relations: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve relations.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'groupid' => 'required|exists:jo_groups,id',
                'roleandsubid' => 'required|exists:jo_roles,roleid',
            ]);

            $relation = Group2Rs::create($validatedData);

            return response()->json(['relation' => $relation], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to create relation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create relation.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $relation = Group2Rs::findOrFail($id);
            return response()->json(['relation' => $relation], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Relation not found.'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve relation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve relation.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'groupid' => 'required|exists:jo_groups,id',
                'roleandsubid' => 'required|exists:jo_roles,roleid',
            ]);

            $relation = Group2Rs::findOrFail($id);
            $relation->update($validatedData);

            return response()->json(['relation' => $relation], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Relation not found.'], 404);
        } catch (Exception $e) {
            Log::error('Failed to update relation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update relation.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $relation = Group2Rs::findOrFail($id);
            $relation->delete();

            return response()->json(['message' => 'Relation deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Relation not found.'], 404);
        } catch (Exception $e) {
            Log::error('Failed to delete relation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete relation.'], 500);
        }
    }
}

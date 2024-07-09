<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\GrouptoGroupRel; // Replace with your actual model
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class GrouptoGroupRelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $relations = GrouptoGroupRel::all();
            return response()->json(['relations' => $relations], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve relations: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve relations.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'groupid' => 'required|exists:jo_groups,id',
                'containsgroupid' => 'required|exists:jo_groups,id',
            ]);

            $relation = GrouptoGroupRel::create($validatedData);

            return response()->json(['relation' => $relation], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to create relation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create relation.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $relation = GrouptoGroupRel::findOrFail($id);
            return response()->json(['relation' => $relation], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Relation not found.'], 404);
        } catch (Exception $e) {
            Log::error('Failed to retrieve relation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve relation.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'groupid' => 'required|exists:jo_groups,id',
                'containsgroupid' => 'required|exists:jo_groups,id',
            ]);

            $relation = GrouptoGroupRel::findOrFail($id);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $relation = GrouptoGroupRel::findOrFail($id);
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

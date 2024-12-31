<?php

namespace App\Http\Controllers;

use App\Models\RelCriteriaGrouping;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RelCriteriaGroupingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $relCriteriaGrouping = RelCriteriaGrouping::all();
            return response()->json($relCriteriaGrouping, \Illuminate\Http\Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve records', 'message' => $e->getMessage()], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'groupid' => 'nullable|integer',
                'queryid' => 'nullable|integer',
                'group_condition' => 'nullable|string|max:255',
                'condition_expression' => 'nullable|string|max:255',
            ]);

            $relCriteriaGrouping = RelCriteriaGrouping::create($validatedData);
            return response()->json($relCriteriaGrouping, \Illuminate\Http\Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'message' => $e->errors()], \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create record', 'message' => $e->getMessage()], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $relCriteriaGrouping = RelCriteriaGrouping::findOrFail($id);
            return response()->json($relCriteriaGrouping, \Illuminate\Http\Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Record not found', 'message' => $e->getMessage()], \Illuminate\Http\Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve record', 'message' => $e->getMessage()], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'groupid' => 'nullable|integer',
                'queryid' => 'nullable|integer',
                'group_condition' => 'nullable|string|max:255',
                'condition_expression' => 'nullable|string|max:255',
            ]);

            $relCriteriaGrouping = RelCriteriaGrouping::findOrFail($id);
            $relCriteriaGrouping->update($validatedData);

            return response()->json($relCriteriaGrouping, \Illuminate\Http\Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Record not found', 'message' => $e->getMessage()], \Illuminate\Http\Response::HTTP_NOT_FOUND);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'message' => $e->errors()], \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update record', 'message' => $e->getMessage()], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $relCriteriaGrouping = RelCriteriaGrouping::findOrFail($id);
            $relCriteriaGrouping->delete();

            return response()->json(null, \Illuminate\Http\Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Record not found', 'message' => $e->getMessage()], \Illuminate\Http\Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete record', 'message' => $e->getMessage()], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

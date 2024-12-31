<?php

namespace App\Http\Controllers;

use App\Models\RelCriteria;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RelCriteriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $relcriteria = RelCriteria::all();
            return response()->json($relcriteria, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve records', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'queryid' => 'nullable|integer',
                'columnindex' => 'nullable|integer',
                'columnname' => 'required|string|max:255',
                'comparator' => 'nullable|string|max:255',
                'value' => 'nullable|string|max:255',
                'groupid' => 'required|integer',
                'column_condition' => 'required|string|max:255',
            ]);

            $relcriterial = RelCriteria::create($validatedData);
            return response()->json($relcriterial, Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'message' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create record', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $relcriterial = RelCriteria::findOrFail($id);
            return response()->json($relcriterial, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Record not found', 'message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve record', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'queryid' => 'nullable|integer',
                'columnindex' => 'nullable|integer',
                'columnname' => 'required|string|max:255',
                'comparator' => 'nullable|string|max:255',
                'value' => 'nullable|string|max:255',
                'groupid' => 'required|integer',
                'column_condition' => 'required|string|max:255',
            ]);

            $relcriterial = RelCriteria::findOrFail($id);
            $relcriterial->update($validatedData);

            return response()->json($relcriterial, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Record not found', 'message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'message' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update record', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $relcriterial = RelCriteria::findOrFail($id);
            $relcriterial->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Record not found', 'message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete record', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

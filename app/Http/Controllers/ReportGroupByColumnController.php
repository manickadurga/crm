<?php

namespace App\Http\Controllers;

use App\Models\ReportGroupByColumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReportGroupByColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $columns = ReportGroupByColumn::all();
            return response()->json($columns);
        } catch (\Exception $e) {
            Log::error('Error fetching ReportGroupByColumns: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching resources'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Incoming request data:', $request->all());

        try {
            $validatedData = $request->validate([
                'reportid' => 'required|integer',
                'sortid' => 'required|integer',
                'sortcolumn' => 'required|string|max:255',
                'dategroupbycriteria' => 'required|string|max:255',
            ]);

            $column = ReportGroupByColumn::create($validatedData);
            return response()->json($column, 201);
        } catch (ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage());
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating ReportGroupByColumn: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating resource'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $column = ReportGroupByColumn::findOrFail($id);
            return response()->json($column);
        } catch (\Exception $e) {
            Log::error('Error fetching ReportGroupByColumn: ' . $e->getMessage());
            return response()->json(['message' => 'Resource not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $column = ReportGroupByColumn::findOrFail($id);

            $validatedData = $request->validate([
                'reportid' => 'required|integer',
                'sortid' => 'required|integer',
                'sortcolumn' => 'required|string|max:255',
                'dategroupbycriteria' => 'required|string|max:255',
            ]);

            $column->update($validatedData);
            return response()->json($column);
        } catch (ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage());
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating ReportGroupByColumn: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating resource'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $column = ReportGroupByColumn::findOrFail($id);
            $column->delete();
            return response()->json(['message' => 'Resource deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting ReportGroupByColumn: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting resource'], 500);
        }
    }
}

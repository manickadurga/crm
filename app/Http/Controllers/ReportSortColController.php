<?php

namespace App\Http\Controllers;

use App\Models\ReportSortCol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportSortColController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sortCols = ReportSortCol::all();
        return response()->json($sortCols);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Incoming request data:', $request->all());

        $request->validate([
            'sortcolid' => 'required|integer',
            'reportid' => 'required|integer',
            'columnname' => 'required|string|max:255',
            'sortorder' => 'required|string|max:255',
        ]);

        try {
            $sortCol = ReportSortCol::create($request->all());
            return response()->json($sortCol, 201);
        } catch (\Exception $e) {
            Log::error('Error creating ReportSortCol: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating resource'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sortCol = ReportSortCol::find($id);

        if (!$sortCol) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        return response()->json($sortCol);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sortCol = ReportSortCol::find($id);

        if (!$sortCol) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $request->validate([
            'sortcolid' => 'required|integer',
            'reportid' => 'required|integer',
            'columnname' => 'required|string|max:255',
            'sortorder' => 'required|string|max:255',
        ]);

        try {
            $sortCol->update($request->all());
            return response()->json($sortCol);
        } catch (\Exception $e) {
            Log::error('Error updating ReportSortCol: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating resource'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $sortCol = ReportSortCol::find($id);

        if (!$sortCol) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        try {
            $sortCol->delete();
            return response()->json(['message' => 'Resource deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting ReportSortCol: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting resource'], 500);
        }
    }
}

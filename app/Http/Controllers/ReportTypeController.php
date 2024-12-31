<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reportTypes = ReportType::all();
        return response()->json($reportTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Incoming request data:', $request->all());

        $request->validate([
            'reportid' => 'required|integer',
            'data' => 'required|string',
        ]);

        try {
            $reportType = ReportType::create($request->all());
            return response()->json($reportType, 201);
        } catch (\Exception $e) {
            Log::error('Error creating ReportType: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating resource'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $reportType = ReportType::find($id);

        if (!$reportType) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        return response()->json($reportType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $reportType = ReportType::find($id);

        if (!$reportType) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $request->validate([
            'reportid' => 'required|integer',
            'data' => 'required|string',
        ]);

        try {
            $reportType->update($request->all());
            return response()->json($reportType);
        } catch (\Exception $e) {
            Log::error('Error updating ReportType: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating resource'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $reportType = ReportType::find($id);

        if (!$reportType) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        try {
            $reportType->delete();
            return response()->json(['message' => 'Resource deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting ReportType: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting resource'], 500);
        }
    }
}

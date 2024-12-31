<?php

namespace App\Http\Controllers;

use App\Models\ReportSharing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportSharingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reportSharings = ReportSharing::all();
        return response()->json($reportSharings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Incoming request data:', $request->all());

        $request->validate([
            'reportid' => 'required|integer',
            'shareid' => 'required|integer',
            'setype' => 'required|string|max:255',
        ]);

        try {
            $reportSharing = ReportSharing::create($request->all());
            return response()->json($reportSharing, 201);
        } catch (\Exception $e) {
            Log::error('Error creating ReportSharing: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating resource'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $reportSharing = ReportSharing::find($id);

        if (!$reportSharing) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        return response()->json($reportSharing);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $reportSharing = ReportSharing::find($id);

        if (!$reportSharing) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $request->validate([
            'reportid' => 'required|integer',
            'shareid' => 'required|integer',
            'setype' => 'required|string|max:255',
        ]);

        try {
            $reportSharing->update($request->all());
            return response()->json($reportSharing);
        } catch (\Exception $e) {
            Log::error('Error updating ReportSharing: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating resource'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $reportSharing = ReportSharing::find($id);

        if (!$reportSharing) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        try {
            $reportSharing->delete();
            return response()->json(['message' => 'Resource deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting ReportSharing: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting resource'], 500);
        }
    }
}

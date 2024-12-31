<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportSummary;

class ReportSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $summaries = ReportSummary::all();
        return response()->json($summaries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'reportsummaryid' => 'required|integer',
            'summarytype' => 'required|integer',
            'columnname' => 'required|string|max:255',
        ]);

        $summary = ReportSummary::create($validatedData);

        return response()->json($summary, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $summary = ReportSummary::findOrFail($id);
        return response()->json($summary);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'reportsummaryid' => 'required|integer',
            'summarytype' => 'required|integer',
            'columnname' => 'required|string|max:255',
        ]);

        $summary = ReportSummary::findOrFail($id);
        $summary->update($validatedData);

        return response()->json($summary);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $summary = ReportSummary::findOrFail($id);
        $summary->delete();

        return response()->json(null, 204);
    }
}

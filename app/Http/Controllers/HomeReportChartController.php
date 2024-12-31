<?php

namespace App\Http\Controllers;

use App\Models\HomeReportChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeReportChartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $homeReportCharts = HomeReportChart::all();
        return response()->json($homeReportCharts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stuffid' => 'required|integer',
            'reportid' => 'required|integer',
            'reportcharttype' => 'required|string|max:255',
        ]);

        try {
            $homeReportChart = HomeReportChart::create($request->all());
            return response()->json($homeReportChart, 201);
        } catch (\Exception $e) {
            Log::error('Error creating HomeReportChart: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating resource'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $homeReportChart = HomeReportChart::find($id);

        if (!$homeReportChart) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        return response()->json($homeReportChart);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $homeReportChart = HomeReportChart::find($id);

        if (!$homeReportChart) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $request->validate([
            'stuffid' => 'required|integer',
            'reportid' => 'required|integer',
            'reportcharttype' => 'required|string|max:255',
        ]);

        try {
            $homeReportChart->update($request->all());
            return response()->json($homeReportChart);
        } catch (\Exception $e) {
            Log::error('Error updating HomeReportChart: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating resource'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $homeReportChart = HomeReportChart::find($id);

        if (!$homeReportChart) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        try {
            $homeReportChart->delete();
            return response()->json(['message' => 'Resource deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting HomeReportChart: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting resource'], 500);
        }
    }
}

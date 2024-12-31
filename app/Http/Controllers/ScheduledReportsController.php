<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledReports;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ScheduledReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $scheduledReports = ScheduledReports::all();
            return response()->json($scheduledReports);
        } catch (\Exception $e) {
            Log::error('Error fetching scheduled reports: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch scheduled reports'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'reportid' => 'required|integer',
                'recipients' => 'required|array',
                'schedule' => 'array', // Validate as array, adjust as per your data requirements
                'format' => 'required|string|max:255',
                'next_trigger_time' => 'required|date',
            ]);

            // Ensure schedule field is not null before storing
            $validatedData['schedule'] = $validatedData['schedule'] ?? [];

            $scheduledReport = ScheduledReports::create($validatedData);

            return response()->json($scheduledReport, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->all()], 400);
        } catch (QueryException $e) {
            Log::error('Error storing scheduled report: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to store scheduled report'], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error storing scheduled report: ' . $e->getMessage());
            return response()->json(['error' => 'Unexpected error occurred'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $scheduledReport = ScheduledReports::findOrFail($id);
            return response()->json($scheduledReport);
        } catch (\Exception $e) {
            Log::error('Error fetching scheduled report: ' . $e->getMessage());
            return response()->json(['error' => 'Scheduled report not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'reportid' => 'required|integer',
                'recipients' => 'required|array',
                'schedule' => 'required|array',
                'format' => 'required|string|max:255',
                'next_trigger_time' => 'required|date',
            ]);

            $scheduledReport = ScheduledReports::findOrFail($id);
            $scheduledReport->update($validatedData);

            return response()->json($scheduledReport);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->all()], 400);
        } catch (QueryException $e) {
            Log::error('Error updating scheduled report: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update scheduled report'], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error updating scheduled report: ' . $e->getMessage());
            return response()->json(['error' => 'Unexpected error occurred'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $scheduledReport = ScheduledReports::findOrFail($id);
            $scheduledReport->delete();

            return response()->json(['message' => 'Scheduled report deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting scheduled report: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete scheduled report'], 500);
        }
    }

    
}


<?php

namespace App\Http\Controllers;

use App\Models\ReportFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = ReportFilter::all();
        return response()->json($filters);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Normally for web, you might return a view here.
        // For API, you might not need this method.
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Incoming request data:', $request->all());

        $request->validate([
            'filterid' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        try {
            $filter = ReportFilter::create($request->all());
            return response()->json($filter, 201);
        } catch (\Exception $e) {
            Log::error('Error creating ReportFilter: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating resource'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $filter = ReportFilter::find($id);

        if (!$filter) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        return response()->json($filter);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Normally for web, you might return a view here.
        // For API, you might not need this method.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $filter = ReportFilter::find($id);

        if (!$filter) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $request->validate([
            'filterid' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        try {
            $filter->update($request->all());
            return response()->json($filter);
        } catch (\Exception $e) {
            Log::error('Error updating ReportFilter: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating resource'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $filter = ReportFilter::find($id);

        if (!$filter) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        try {
            $filter->delete();
            return response()->json(['message' => 'Resource deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting ReportFilter: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting resource'], 500);
        }
    }
}

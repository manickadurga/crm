<?php

namespace App\Http\Controllers;

use App\Models\ReportDateFilter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportDateFilterController extends Controller
{
    public function index()
    {
        $filters = ReportDateFilter::all();
        return response()->json($filters);
    }

    public function store(Request $request)
    {
        $request->validate([
            'datefilderid' => 'required|integer',
            'datecolumnname' => 'required|string|max:255',
            'datefilder' => 'required|string|max:255',
            'startdate' => 'required|date',
            'enddate' => 'required|date',
        ]);

        $filter = ReportDateFilter::create($request->all());
        return response()->json($filter, 201);
    }

    // Display the specified resource.
    public function show($id)
    {
        $filter = ReportDateFilter::findOrFail($id);
        return response()->json($filter);
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $request->validate([
            'datefilderid' => 'integer',
            'datecolumnname' => 'string|max:255',
            'datefilder' => 'string|max:255',
            'startdate' => 'date',
            'enddate' => 'date',
        ]);

        $filter = ReportDateFilter::findOrFail($id);
        $filter->update($request->all());
        return response()->json($filter);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $filter = ReportDateFilter::findOrFail($id);
        $filter->delete();
        return response()->json(null, 204);
    }
}

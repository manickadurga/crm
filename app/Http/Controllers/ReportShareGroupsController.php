<?php

namespace App\Http\Controllers;

use App\Models\ReportShareGroups;
use Illuminate\Http\Request;

class ReportShareGroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shareGroups = ReportShareGroups::all();
        return response()->json($shareGroups);
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
        $request->validate([
            'reportid' => 'required|integer',
            'groupid' => 'required|integer',
        ]);

        $shareGroup = ReportShareGroups::create($request->all());
        return response()->json($shareGroup, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $shareGroup = ReportShareGroups::find($id);

        if (!$shareGroup) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        return response()->json($shareGroup);
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
        $shareGroup = ReportShareGroups::find($id);

        if (!$shareGroup) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $request->validate([
            'reportid' => 'required|integer',
            'groupid' => 'required|integer',
        ]);

        $shareGroup->update($request->all());
        return response()->json($shareGroup);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $shareGroup = ReportShareGroups::find($id);

        if (!$shareGroup) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $shareGroup->delete();
        return response()->json(['message' => 'Resource deleted']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ReportModules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportModulesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modules = ReportModules::all();
        return response()->json($modules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Incoming request data:', $request->all());

        $request->validate([
            'reportmodulesid' => 'nullable|integer',
            'primarymodule' => 'required|string|max:255',
            'secondarymodules' => 'nullable|string|max:255',
        ]);

        try {
            $module = ReportModules::create($request->all());
            return response()->json($module, 201);
        } catch (\Exception $e) {
            Log::error('Error creating ReportModules: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating resource'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $module = ReportModules::find($id);

        if (!$module) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        return response()->json($module);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $module = ReportModules::find($id);

        if (!$module) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $request->validate([
            'reportmodulesid' => 'required|integer',
            'primarymodule' => 'required|string|max:255',
            'secondarymodules' => 'nullable|string|max:255',
        ]);

        try {
            $module->update($request->all());
            return response()->json($module);
        } catch (\Exception $e) {
            Log::error('Error updating ReportModules: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating resource'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $module = ReportModules::find($id);

        if (!$module) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        try {
            $module->delete();
            return response()->json(['message' => 'Resource deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting ReportModules: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting resource'], 500);
        }
    }
}

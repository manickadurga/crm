<?php

namespace App\Http\Controllers;

use App\Models\ReportShareUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportShareUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shareUsers = ReportShareUsers::all();
        return response()->json($shareUsers);
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
            'reportid' => 'required|integer',
            'userid' => 'required|integer',
        ]);

        try {
            $shareUser = ReportShareUsers::create($request->all());
            return response()->json($shareUser, 201);
        } catch (\Exception $e) {
            Log::error('Error creating ReportShareUser: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating resource'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $shareUser = ReportShareUsers::find($id);

        if (!$shareUser) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        return response()->json($shareUser);
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
        $shareUser = ReportShareUsers::find($id);

        if (!$shareUser) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $request->validate([
            'reportid' => 'required|integer',
            'userid' => 'required|integer',
        ]);

        $shareUser->update($request->all());
        return response()->json($shareUser);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $shareUser = ReportShareUsers::find($id);

        if (!$shareUser) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $shareUser->delete();
        return response()->json(['message' => 'Resource deleted']);
    }
}

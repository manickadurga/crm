<?php

namespace App\Http\Controllers;

use App\Models\ReportShareRoles;
use Illuminate\Http\Request;

class ReportShareRolesController extends Controller
{
    public function index()
    {
        $shareRoles = ReportShareRoles::all();
        return response()->json($shareRoles);
    }
    public function store(Request $request)
    {
        $request->validate([
            'reportid' => 'required|integer',
            'roleid' => 'required|string',
        ]);

        $shareRoles = ReportShareRoles::create($request->all());
        return response()->json($shareRoles, 201);
    }
    public function show($id)
    {
        $shareRoles = ReportShareRoles::find($id);

        if (!$shareRoles) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        return response()->json($shareRoles);
    }
    public function update(Request $request, $id)
    {
        $shareRoles = ReportShareRoles::find($id);

        if (!$shareRoles) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $request->validate([
            'reportid' => 'required|integer',
            'roleid' => 'required|string',
        ]);

        $shareRoles->update($request->all());
        return response()->json($shareRoles);
    }
    public function destroy($id)
    {
        $shareRoles = ReportShareRoles::find($id);

        if (!$shareRoles) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $shareRoles->delete();
        return response()->json(['message' => 'Resource deleted']);
    }

}

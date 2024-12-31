<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        $calendarEntries = Calendar::with(['employee', 'client', 'project', 'team', 'task'])->get();
        return response()->json($calendarEntries);
    }

    // Create a new calendar entry
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:jo_manage_employees,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:h:i:s A',
            'end_time' => 'required|after:start_time|date_format:h:i:s A',
            'is_billable' => 'nullable|boolean',
            'client_id' => 'nullable|exists:jo_clients,id',
            'project_id' => 'nullable|exists:jo_projects,id',
            'team_id' => 'nullable|exists:jo_teams,id',
            'task_id' => 'nullable|exists:jo_tasks,id',
            'description' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $calendar = Calendar::create($validated);
        return response()->json(['message' => 'Calendar entry created successfully', 'data' => $calendar]);
    }

    // Show a single calendar entry
    public function show($id)
    {
        $calendar = Calendar::with(['employee', 'client', 'project', 'team', 'task'])->findOrFail($id);
        return response()->json($calendar);
    }

    // Update a calendar entry
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:jo_manage_employees,id',
            'date' => 'nullable|date',
            'start_time' => 'nullable|date_format:h:i:s A',
            'end_time' => 'nullable|after:start_time|date_format:h:i:s A',
            'is_billable' => 'nullable|boolean',
            'client_id' => 'nullable|exists:jo_clients,id',
            'project_id' => 'nullable|exists:jo_projects,id',
            'team_id' => 'nullable|exists:jo_teams,id',
            'task_id' => 'nullable|exists:jo_tasks,id',
            'description' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $calendar = Calendar::findOrFail($id);
        $calendar->update($validated);
        return response()->json(['message' => 'Calendar entry updated successfully', 'data' => $calendar]);
    }

    // Delete a calendar entry
    public function destroy($id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->delete();
        return response()->json(['message' => 'Calendar entry deleted successfully']);
    }
}

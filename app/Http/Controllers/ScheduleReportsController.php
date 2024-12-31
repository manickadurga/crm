<?php

namespace App\Http\Controllers;
use App\Models\Groups;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ScheduleReports;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


class ScheduleReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = ScheduleReports::all();
        return response()->json($reports);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'reportid' => 'required|integer',
                'scheduleid' => 'required|integer',
                'recipients' => 'required|array',
                'recipients.*.Users' => 'exists:users,username',
                'recipients.*.Roles' => 'exists:jo_roles,rolename',
                'recipients.*.Groups' => 'exists:jo_groups,group_name',
                'schdate' => 'required|string|max:255',
                'schtime' => 'required|string|max:255',
                'schdayoftheweek' => 'nullable|string|max:255',
                'schdayofthemonth' => 'nullable|string|max:255',
                'schannualdates' => 'nullable|string|max:255',
                'specificemails' => 'nullable|string|max:255',
                'next_trigger_time' => 'required|string|max:255',
                'fileformat' => 'required|string|max:255',
            ]);

            // Process recipients if provided
            $recipients = [];
            foreach ($validatedData['recipients'] as $member) {
                $memberInfo = [];

                // Retrieve and store user information
                if (isset($member['Users'])) {
                    $user = User::where('username', $member['Users'])->first();
                    if ($user) {
                        $memberInfo['id'] = $user->id;
                    } else {
                        throw ValidationException::withMessages(['recipients' => "User with username '{$member['Users']}' not found"]);
                    }
                }

                

                // Retrieve and store role information
                if (isset($member['Roles'])) {
                    $role = Role::where('rolename', $member['Roles'])->first();
                    if ($role) {
                        $memberInfo['roleid'] = $role->roleid;
                    } else {
                        throw ValidationException::withMessages(['recipients' => "Role with rolename '{$member['Roles']}' not found"]);
                    }
                }

                // Retrieve and store group information
                if (isset($member['Groups'])) {
                    $group = Groups::where('group_name', $member['Groups'])->first();
                    if ($group) {
                        $memberInfo['id'] = $group->id;
                    } else {
                        throw ValidationException::withMessages(['recipients' => "Group with group_name '{$member['Groups']}' not found"]);
                    }
                }

                $recipients[] = $memberInfo;
            }

            // Prepare data for schedule report creation
            $reportData = [
                'reportid' => $validatedData['reportid'],
                'scheduleid' => $validatedData['scheduleid'],
                'recipients' => json_encode($recipients),
                'schdate' => $validatedData['schdate'],
                'schtime' => $validatedData['schtime'],
                'schdayoftheweek' => $validatedData['schdayoftheweek'],
                'schdayofthemonth' => $validatedData['schdayofthemonth'],
                'schannualdates' => $validatedData['schannualdates'],
                'specificemails' => $validatedData['specificemails'],
                'next_trigger_time' => $validatedData['next_trigger_time'],
                'fileformat' => $validatedData['fileformat']
            ];

            // Create a new schedule report record in the database
            ScheduleReports::create($reportData);

            // Return a success response
            return response()->json(['message' => 'Report scheduled successfully']);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to schedule report: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to schedule report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $report = ScheduleReports::findOrFail($id);
        return response()->json($report);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'reportid' => 'required|integer',
            'scheduleid' => 'required|integer',
            'recipients' => 'required|string|max:255',
            'schdate' => 'required|string|max:255',
            'schtime' => 'required|string|max:255',
            'schdayoftheweek' => 'nullable|string|max:255',
            'schdayofthemonth' => 'nullable|string|max:255',
            'schannualdates' => 'nullable|string|max:255',
            'specificemails' => 'nullable|string|max:255',
            'next_trigger_time' => 'required|string|max:255',
            'fileformat' => 'required|string|max:255',
        ]);

        $scheduleReport = ScheduleReports::findOrFail($id);
        $scheduleReport->update($validatedData);

        return response()->json($scheduleReport);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $scheduleReport = ScheduleReports::findOrFail($id);
        $scheduleReport->delete();

        return response()->json(null, 204);
    }
}

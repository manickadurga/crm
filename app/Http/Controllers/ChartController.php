<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Field;
use App\Models\Groups;
use App\Models\Report;
use App\Models\ReportModules;
use App\Models\Role;
use App\Models\ScheduleReports;
use App\Models\Tab;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Customers;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page',10);

            $query = Report::query();

            // Optional: add filters based on request parameters
            if ($request->has('reportname')) {
                $query->where('reportname', 'like', '%' . $request->input('reportname') . '%');
            }

            if ($request->has('folderid')) {
                $query->where('folderid', $request->input('folderid'));
            }

            $reports = $query->paginate($perPage);

            return response()->json([
                'status' => 200,
                'data' => $reports,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }





    public function store(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'reportname' => 'required|string',
                'folderid' => 'required|array|min:1',
                'folderid.*.folderid' => 'required|integer|exists:jo_reportfolder,folderid',
                'primarymodule' => 'required|integer|exists:jo_tabs,tabid',
                'recipients' => 'required|array',
                'recipients.*.Users' => 'exists:users,username',
                'recipients.*.Roles' => 'exists:jo_roles,rolename',
                'recipients.*.Groups' => 'exists:jo_groups,group_name',
                'scheduleid' => 'required|integer',
                'schdate' => 'required|string|max:255',
                'schtime' => 'required|string|max:255',
                'schdayoftheweek' => 'string|max:255|nullable',
                'schdayofthemonth' => 'string|max:255|nullable',
                'schannualdates' => 'string|max:255|nullable',
                'specificemails' => 'string|max:255|nullable',
                'next_trigger_time' => 'required|string|max:255',
                'fileformat' => 'required|string|max:255',
                'requests' => 'required|array',
                'requests.*.field_name' => 'required|string',
                'requests.*.field_value' => 'required|string',
                'requests.*.condition' => 'required|string|in:starts_with,ends_with,equals,not_equals,contains,not_contains',
                'requests.*.chart_type' => 'required|string|in:pie,bar,line',
                'group_by_field' => 'required|string', // Dynamic validation based on primary module
            ]);

            // Fetch the tab name using the primarymodule tabid
            $tabName = DB::table('jo_tabs')->where('tabid', $validatedData['primarymodule'])->value('name');
            if (!$tabName) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Invalid primarymodule tabid provided',
                ], 400);
            }

            // Fetch blocks and fields for the given tab id (primarymodule)
            $blocks = Block::where('tabid', $validatedData['primarymodule'])->with('fields')->get();
            $response = [
                'tabid' => $validatedData['primarymodule'],
                'blocks' => $blocks->map(function ($block) {
                    return [
                        'blocklabel' => $block->blocklabel,
                        'fields' => $block->fields ? $block->fields->map(function ($field) {
                            return [
                                'fieldid' => $field->fieldid,
                                'columnname' => $field->columnname,
                            ];
                        }) : [],
                    ];
                }),
            ];

            // Initialize query based on primary module table
            $primaryModuleTableName = '';
            switch ($validatedData['primarymodule']) {
                case 1:
                    $primaryModuleTableName = 'jo_customers'; // Example table name for tabid 1
                    break;
                case 2:
                    $primaryModuleTableName = 'jo_teamtasks'; // Example table name for tabid 2
                    break;
                default:
                    return response()->json([
                        'status' => 400,
                        'message' => 'Invalid primarymodule tabid provided',
                    ], 400);
            }

            // Build query with primary module table
            $query = DB::table($primaryModuleTableName);

            // Apply search filters
            foreach ($validatedData['requests'] as $requestItem) {
                $fieldName = $requestItem['field_name'];
                $fieldValue = $requestItem['field_value'];
                $condition = $requestItem['condition'];

                switch ($condition) {
                    case 'starts_with':
                        $query->where($fieldName, 'like', $fieldValue . '%');
                        break;
                    case 'ends_with':
                        $query->where($fieldName, 'like', '%' . $fieldValue);
                        break;
                    case 'equals':
                        $query->where($fieldName, '=', $fieldValue);
                        break;
                    case 'not_equals':
                        $query->where($fieldName, '!=', $fieldValue);
                        break;
                    case 'contains':
                        $query->where($fieldName, 'like', '%' . $fieldValue . '%');
                        break;
                    case 'not_contains':
                        $query->where($fieldName, 'not like', '%' . $fieldValue . '%');
                        break;
                    default:
                        // Handle invalid condition if needed
                        break;
                }
            }

            // Group by the selected field
            $groupByField = $validatedData['group_by_field'];
            $query->groupBy($groupByField);

            // Prepare chart data based on the chart type
            $chartType = $validatedData['requests'][0]['chart_type']; // Assuming the chart type is the same for all requests
            $chartData = $this->prepareChartData($query, $groupByField, $chartType);

            // Fetch all results
            $results = $query->get();

            $count = count($chartData);

            // Check if any results found
            if ($results->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No matching records found',
                ], 404);
            }

            // Prepare recipients information
            $recipients = [];
            foreach ($validatedData['recipients'] as $member) {
                $memberInfo = [];
                if (isset($member['Users'])) {
                    $user = User::where('username', $member['Users'])->first();
                    if ($user) {
                        $memberInfo['id'] = $user->id;
                    } else {
                        throw ValidationException::withMessages(['recipients' => "User with username '{$member['Users']}' not found"]);
                    }
                }
                if (isset($member['Roles'])) {
                    $role = Role::where('rolename', $member['Roles'])->first();
                    if ($role) {
                        $memberInfo['roleid'] = $role->roleid;
                    } else {
                        throw ValidationException::withMessages(['recipients' => "Role with rolename '{$member['Roles']}' not found"]);
                    }
                }
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

            // Extract the first folderid from the array
            $firstFolderId = $validatedData['folderid'][0]['folderid'];

            // Create a new report
            $report = new Report();
            $report->reportname = $validatedData['reportname'];
            $report->folderid = $firstFolderId;
            $report->save();

            // Create a new report module
            $reportModule = new ReportModules();
            $reportModule->reportmodulesid = $report->id;
            $reportModule->primarymodule = $tabName; // Store tab name instead of tabid
            $reportModule->secondarymodules = ''; // Assuming secondarymodules can be empty
            $reportModule->save();

            // Create a new schedule report
            $scheduleReport = new ScheduleReports();
            $scheduleReport->reportid = $report->id;
            $scheduleReport->scheduleid = $validatedData['scheduleid'];
            $scheduleReport->recipients = json_encode($recipients);
            $scheduleReport->schdate = $validatedData['schdate'];
            $scheduleReport->schtime = $validatedData['schtime'];
            $scheduleReport->schdayoftheweek = $validatedData['schdayoftheweek'];
            $scheduleReport->schdayofthemonth = $validatedData['schdayofthemonth'];
            $scheduleReport->schannualdates = $validatedData['schannualdates'];
            $scheduleReport->specificemails = $validatedData['specificemails'];
            $scheduleReport->next_trigger_time = $validatedData['next_trigger_time'];
            $scheduleReport->fileformat = $validatedData['fileformat'];
            $scheduleReport->save();

            $totalCount = 0;
foreach ($chartData as $item) {
    $totalCount += $item['value'];
}
            // Adjust your return statement to include the count field
            return response()->json([
                'status' => 200,
                'message' => 'Report created successfully',
                'report' => $report,
                'reportModule' => $reportModule,
                'scheduleReport' => $scheduleReport,
                'count' => $totalCount, // Store the aggregated count from chartData
                'chart_type' => $chartType,
                'chartData' => $chartData,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation Error',
                'errors' => $e->validator->getMessageBag(),
            ], 400);
        } catch (\Exception $e) {
            // Log the detailed error message
            Log::error('Error storing report: ' . $e->getMessage() . ' in file ' . $e->getFile() . ' on line ' . $e->getLine());

            return response()->json([
                'status' => 500,
                'message' => 'Failed to store report',
                'error' => $e->getMessage(), // Include the error message in the response
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $report = Report::findOrFail($id);

            return response()->json([
                'status' => 200,
                'data' => $report,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function update(Request $request, $id)
    {
        try {
            $report = Report::findOrFail($id);

            $validatedData = $request->validate([
                'reportname' => 'required|string',
                'folderid' => 'required|integer|exists:jo_reportfolder,folderid',
                'primarymodule' => 'required|integer|exists:jo_tabs,tabid',
                'recipients' => 'required|array',
                'recipients.*.Users' => 'exists:users,username',
                'recipients.*.Roles' => 'exists:jo_roles,rolename',
                'recipients.*.Groups' => 'exists:jo_groups,group_name',
                'scheduleid' => 'required|integer',
                'schdate' => 'required|string|max:255',
                'schtime' => 'required|string|max:255',
                'schdayoftheweek' => 'string|max:255|nullable',
                'schdayofthemonth' => 'string|max:255|nullable',
                'schannualdates' => 'string|max:255|nullable',
                'specificemails' => 'string|max:255|nullable',
                'next_trigger_time' => 'required|string|max:255',
                'fileformat' => 'required|string|max:255',
                'requests' => 'required|array',
                'requests.*.field_name' => 'required|string',
                'requests.*.field_value' => 'required|string',
                'requests.*.condition' => 'required|string|in:starts_with,ends_with,equals,not_equals,contains,not_contains',
                'requests.*.chart_type' => 'required|string|in:pie,bar,line',
                'group_by_field' => 'required|string',
            ]);

            $report->reportname = $validatedData['reportname'];
            $report->folderid = $validatedData['folderid'];
            $report->save();

            // Optional: update related models (ReportModules, ScheduleReports) as needed

            return response()->json([
                'status' => 200,
                'message' => 'Report updated successfully',
                'data' => $report,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Destroy function to delete a specific report
    public function destroy($id)
    {
        try {
            $report = Report::findOrFail($id);
            $report->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Report deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function prepareChartData($query, $groupByField, $chartType)
    {
        $data = $query->select($groupByField, DB::raw('count(*) as total'))->groupBy($groupByField)->get();

        $chartData = [];
        switch ($chartType) {
            case 'pie':
                foreach ($data as $row) {
                    $chartData[] = [
                        'label' => $row->$groupByField,
                        'value' => $row->total,
                    ];
                }
                break;
            case 'bar':
            case 'line':
                // Assuming bar and line charts have similar data structure
                foreach ($data as $row) {
                    $chartData['labels'][] = $row->$groupByField;
                    $chartData['data'][] = $row->total;
                }
                break;
            default:
                // Handle unsupported chart type if needed
                break;
        }

        return $chartData;
    }

}

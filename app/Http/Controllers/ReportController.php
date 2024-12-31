<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Field;
use App\Models\Groups;
use App\Models\Report;
use App\Models\ReportModules;
use App\Models\Role;
use App\Models\ScheduleReports;
use Maatwebsite\Excel\Concerns\FromArray;
use App\Models\Tab;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Exports\CustomersExport;
use App\Models\Customers;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;





class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $reports = Report::all();
            return response()->json($reports);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch reports.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'folderid' => 'required|integer',
                'reportname' => 'required|string|max:255',
                'description' => 'nullable|string',
                'reporttype' => 'nullable|string|max:255',
                'queryid' => 'nullable|integer',
                'state' => 'nullable|string|max:255',
                'customizable' => 'nullable|integer',
                'category' => 'nullable|integer',
                'owner' => 'nullable|integer',
                'sharingtype' => 'nullable|string|max:255',
            ]);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Create the report using validated data
            $report = Report::create($validator->validated());

            // Return success response with the created report data
            return response()->json($report, 201);
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to store report: ' . $e->getMessage());

            // Return error response
            return response()->json(['error' => 'Failed to store report.'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $report = Report::findOrFail($id);
            return response()->json($report);
        } catch (Exception $e) {
            return response()->json(['error' => 'Report not found.'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'folderid' => 'required|integer',
                'reportname' => 'required|string|max:255',
                'description' => 'nullable|string',
                'reporttype' => 'nullable|string|max:255',
                'queryid' => 'nullable|integer',
                'state' => 'nullable|string|max:255',
                'customizable' => 'nullable|integer',
                'category' => 'nullable|integer',
                'owner' => 'nullable|integer',
                'sharingtype' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $report = Report::findOrFail($id);
            $report->update($validator->validated());

            return response()->json($report);
        } catch (Exception $e) {
            Log::error('Failed to update report: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update report.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $report = Report::findOrFail($id);
            $report->delete();

            return response()->json(null, 204);
        } catch (Exception $e) {
            Log::error('Failed to delete report: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete report.'], 500);
        }
    }






























//     public function indexs(Request $request)
//     {
//         try {
//             // Define the number of items per page
//             $perPage = $request->input('per_page', 10);

//             // Fetch the reports with optional filters
//             $query = Report::query();

//             // Optional: add filters based on request parameters
//             if ($request->has('reportname')) {
//                 $query->where('reportname', 'like', '%' . $request->input('reportname') . '%');
//             }

//             if ($request->has('folderid')) {
//                 $query->where('folderid', $request->input('folderid'));
//             }

//             // Execute the query and paginate the results
//             $reports = $query->paginate($perPage);

//             // Return the paginated results
//             return response()->json([
//                 'status' => 200,
//                 'data' => $reports,
//             ], 200);

//         } catch (Exception $e) {
//             return response()->json([
//                 'status' => 500,
//                 'message' => $e->getMessage(),
//             ], 500);
//         }
//     }






//     public function stores(Request $request)
//     {
//         try {
//             // Validate the request
//             $validatedData = $request->validate([
//                 'reportname' => 'required|string',
//                 'folderid' => 'required|array|min:1',
//                 'folderid.*.folderid' => 'required|integer|exists:jo_reportfolder,folderid',
//                 'primarymodule' => 'required|integer|exists:jo_tabs,tabid',
//                 'recipients' => 'required|array',
//                 'recipients.*.Users' => 'exists:users,username',
//                 'recipients.*.Roles' => 'exists:jo_roles,rolename',
//                 'recipients.*.Groups' => 'exists:jo_groups,group_name',
//                 'scheduleid' => 'required|integer',
//                 'schdate' => 'required|string|max:255',
//                 'schtime' => 'required|string|max:255',
//                 'schdayoftheweek' => 'string|max:255|nullable',
//                 'schdayofthemonth' => 'string|max:255|nullable',
//                 'schannualdates' => 'string|max:255|nullable',
//                 'specificemails' => 'string|max:255|nullable',
//                 'next_trigger_time' => 'required|string|max:255',
//                 'fileformat' => 'required|string|max:255',
//                 'select_column' => 'required|array',
//                 'select_column.*' => 'required|string',
//                 'group_by' => 'nullable|array',
//                 'group_by.*.field_name' => 'required|string',
//                 'group_by.*.sort_order' => 'required|string|in:Ascending,Descending',
//                 'requests' => 'required|array',
//                 'requests.*.field_name' => 'required|string',
//                 'requests.*.field_value' => 'required|string',
//                 'requests.*.condition' => 'required|string|in:starts_with,ends_with,equals,not_equals,contains,not_contains',
//                 'requests.*.per_page' => 'nullable|integer|min:1',
//                 'requests.*.export_excel' => 'nullable|boolean',
//             ]);

//             // Fetch the tab name using the primarymodule tabid
//             $tabName = DB::table('jo_tabs')->where('tabid', $validatedData['primarymodule'])->value('name');
//             if (!$tabName) {
//                 return response()->json([
//                     'status' => 400,
//                     'message' => 'Invalid primarymodule tabid provided',
//                 ], 400);
//             }

//             // Fetch blocks and fields for the given tab id (primarymodule)
//             $blocks = Block::where('tabid', $validatedData['primarymodule'])->with('fields')->get();
//             $response = [
//                 'tabid' => $validatedData['primarymodule'],
//                 'blocks' => $blocks->map(function ($block) {
//                     return [
//                         'blocklabel' => $block->blocklabel,
//                         'fields' => $block->fields->map(function ($field) {
//                             return [
//                                 'fieldid' => $field->fieldid,
//                                 'columnname' => $field->columnname,
//                             ];
//                         }),
//                     ];
//                 }),
//             ];

//             // Initialize query based on primary module table
//             $primaryModuleTableName = '';
//             switch ($validatedData['primarymodule']) {
//                 case 1:
//                     $primaryModuleTableName = 'jo_customers'; // Example table name for tabid 1
//                     break;
//                 case 2:
//                     $primaryModuleTableName = 'jo_teamtasks'; // Example table name for tabid 2
//                     break;
//                 default:
//                     return response()->json([
//                         'status' => 400,
//                         'message' => 'Invalid primarymodule tabid provided',
//                     ], 400);
//             }

//             // Build query with primary module table
//             $query = DB::table($primaryModuleTableName);

//             // Select all columns specified in select_column
//             $query->select($validatedData['select_column']);

//             // Apply search filters
//             foreach ($validatedData['requests'] as $requestItem) {
//                 $fieldName = $requestItem['field_name'];
//                 $fieldValue = $requestItem['field_value'];
//                 $condition = $requestItem['condition'];

//                 switch ($condition) {
//                     case 'starts_with':
//                         $query->where($fieldName, 'like', $fieldValue . '%');
//                         break;
//                     case 'ends_with':
//                         $query->where($fieldName, 'like', '%' . $fieldValue);
//                         break;
//                     case 'equals':
//                         $query->where($fieldName, '=', $fieldValue);
//                         break;
//                     case 'not_equals':
//                         $query->where($fieldName, '!=', $fieldValue);
//                         break;
//                     case 'contains':
//                         $query->where($fieldName, 'like', '%' . $fieldValue . '%');
//                         break;
//                     case 'not_contains':
//                         $query->where($fieldName, 'not like', '%' . $fieldValue . '%');
//                         break;
//                     default:
//                         // Handle invalid condition if needed
//                         break;
//                 }
//             }

//             // Apply group by and sort order
//             if (!empty($validatedData['group_by'])) {
//                 foreach ($validatedData['group_by'] as $groupBy) {
//                     $fieldName = $groupBy['field_name'];
//                     $sortOrder = strtolower($groupBy['sort_order']) === 'ascending' ? 'asc' : 'desc';
//                     $query->groupBy($fieldName)->orderBy($fieldName, $sortOrder);
//                 }
//             }

//             // Ensure all selected columns are part of the group by clause
//             $selectColumns = $validatedData['select_column'];
//             $groupByColumns = array_column($validatedData['group_by'] ?? [], 'field_name');
//             $allGroupByColumns = array_unique(array_merge($selectColumns, $groupByColumns));
//             $query->groupBy($allGroupByColumns);

//             // Paginate the search results
//             $perPage = $validatedData['requests'][0]['per_page'] ?? 10;
//             $results = $query->paginate($perPage);

//             // Check if any results found
//             if ($results->isEmpty()) {
//                 return response()->json([
//                     'status' => 404,
//                     'message' => 'No matching records found',
//                 ], 404);
//             }

//             // Prepare the response data
//             $responseData = [];
//             foreach ($results as $result) {
//                 $data = [];
//                 foreach ($validatedData['select_column'] as $column) {
//                     $data[] = $result->$column;
//                 }
//                 $responseData[] = $data;
//             }

//             // Export to Excel if the flag is set
//             if (isset($validatedData['requests'][0]['export_excel']) && $validatedData['requests'][0]['export_excel']) {
//                 $filename = 'export_' . time() . '.xlsx';
//                 return Excel::download(new CustomersExport([$validatedData['select_column'], ...$responseData]), $filename);
//             }

//             // Prepare recipients information
//             $recipients = [];
//             foreach ($validatedData['recipients'] as $member) {
//                 $memberInfo = [];
//                 if (isset($member['Users'])) {
//                     $user = User::where('username', $member['Users'])->first();
//                     if ($user) {
//                         $memberInfo['id'] = $user->id;
//                     } else {
//                         throw ValidationException::withMessages(['recipients' => "User with username '{$member['Users']}' not found"]);
//                     }
//                 }
//                 if (isset($member['Roles'])) {
//                     $role = Role::where('rolename', $member['Roles'])->first();
//                     if ($role) {
//                         $memberInfo['roleid'] = $role->roleid;
//                     } else {
//                         throw ValidationException::withMessages(['recipients' => "Role with rolename '{$member['Roles']}' not found"]);
//                     }
//                 }
//                 if (isset($member['Groups'])) {
//                     $group = Groups::where('group_name', $member['Groups'])->first();
//                     if ($group) {
//                         $memberInfo['id'] = $group->id;
//                     } else {
//                         throw ValidationException::withMessages(['recipients' => "Group with group_name '{$member['Groups']}' not found"]);
//                     }
//                 }
//                 $recipients[] = $memberInfo;
//             }

//             // Extract the first folderid from the array
//             $firstFolderId = $validatedData['folderid'][0]['folderid'];

//             // Create a new report
//             $report = new Report();
//             $report->reportname = $validatedData['reportname'];
//             $report->folderid = $firstFolderId;
//             $report->save();

//             // Create a new report module
//             $reportModule = new ReportModules();
//             $reportModule->reportmodulesid = $report->id;
//             $reportModule->primarymodule = $tabName; // Store tab name instead of tabid
//             $reportModule->secondarymodules = ''; // Assuming secondarymodules can be empty
//             $reportModule->save();

//             // Create a new schedule report
//             $scheduleReport = new ScheduleReports();
//             $scheduleReport->reportid = $report->id;
//             $scheduleReport->scheduleid = $validatedData['scheduleid'];
//             $scheduleReport->recipients = json_encode($recipients);
//             $scheduleReport->schdate = $validatedData['schdate'];
//             $scheduleReport->schtime = $validatedData['schtime'];
//             $scheduleReport->schdayoftheweek = $validatedData['schdayoftheweek'];
//             $scheduleReport->schdayofthemonth = $validatedData['schdayofthemonth'];
//             $scheduleReport->schannualdates = $validatedData['schannualdates'];
//             $scheduleReport->specificemails = $validatedData['specificemails'];
//             $scheduleReport->next_trigger_time = $validatedData['next_trigger_time'];
//             $scheduleReport->fileformat = $validatedData['fileformat'];
//             $scheduleReport->save();

//             return response()->json([
//                 'status' => 200,
//                 'message' => 'Report created successfully',
//                 'report' => $report,
//                 'reportModule' => $reportModule,
//                 'scheduleReport' => $scheduleReport,
//                 'data' => $responseData,
//                 'pagination' => [
//                     'total' => $results->total(),
//                     'current_page' => $results->currentPage(),
//                     'last_page' => $results->lastPage(),
//                     'per_page' => $results->perPage(),
//                 ],
//             ], 200);

//         } catch (ValidationException $e) {
//             return response()->json([
//                 'status' => 422,
//                 'message' => $e->errors(),
//             ], 422);
//         } catch (Exception $e) {
//             return response()->json([
//                 'status' => 500,
//                 'message' => $e->getMessage(),
//             ], 500);
//         }
//     }






// // public function exportData(Request $request)
// // {
// //     try {
// //         // Retrieve data to export
// //         $dataToExport = $request->data; // Assuming 'data' contains $responseData

// //         // Validate the request (optional if you already have validated data)

// //         // Example: Create and download Excel file
// //         return Excel::download(new CustomersExport($dataToExport), 'report.xlsx');

// //         // Example: Create and download CSV file
// //         // return Excel::download(new ReportExport($dataToExport), 'report.csv', \Maatwebsite\Excel\Excel::CSV);

// //     } catch (Exception $e) {
// //         return response()->json([
// //             'status' => 500,
// //             'message' => 'Failed to export data',
// //             'error' => $e->getMessage(),
// //         ], 500);
// //     }
// // }





// // public function exportToExcel(Request $request)
// // {
// //     try {
// //         // Validate the request
// //         $validatedData = $request->validate([
// //             'reportname' => 'required|string',
// //             'folderid' => 'required|array|min:1',
// //             'folderid.*.folderid' => 'required|integer|exists:jo_reportfolder,folderid',
// //             'primarymodule' => 'required|integer|exists:jo_tabs,tabid',
// //             'select_column' => 'required|array',
// //             'select_column.*' => 'required|string',
// //             'group_by' => 'nullable|array',
// //             'group_by.*.field_name' => 'required|string',
// //             'group_by.*.sort_order' => 'required|string|in:Ascending,Descending',
// //             'requests' => 'required|array',
// //             'requests.*.field_name' => 'required|string',
// //             'requests.*.field_value' => 'required|string',
// //             'requests.*.condition' => 'required|string|in:starts_with,ends_with,equals,not_equals,contains,not_contains',
// //             'requests.*.per_page' => 'nullable|integer|min:1',
// //         ]);

// //         // Fetch the tab name using the primarymodule tabid
// //         $tabName = DB::table('jo_tabs')->where('tabid', $validatedData['primarymodule'])->value('name');
// //         if (!$tabName) {
// //             return response()->json([
// //                 'status' => 400,
// //                 'message' => 'Invalid primarymodule tabid provided',
// //             ], 400);
// //         }

// //         // Fetch blocks and fields for the given tab id (primarymodule)
// //         $blocks = Block::where('tabid', $validatedData['primarymodule'])->with('fields')->get();

// //         // Initialize query based on primary module table
// //         $primaryModuleTableName = '';
// //         switch ($validatedData['primarymodule']) {
// //             case 1:
// //                 $primaryModuleTableName = 'jo_customers'; // Example table name for tabid 1
// //                 break;
// //             case 2:
// //                 $primaryModuleTableName = 'jo_teamtasks'; // Example table name for tabid 2
// //                 break;
// //             default:
// //                 return response()->json([
// //                     'status' => 400,
// //                     'message' => 'Invalid primarymodule tabid provided',
// //                 ], 400);
// //         }

// //         // Build query with primary module table
// //         $query = DB::table($primaryModuleTableName);

// //         // Select all columns specified in select_column
// //         $query->select($validatedData['select_column']);

// //         // Apply search filters
// //         foreach ($validatedData['requests'] as $requestItem) {
// //             $fieldName = $requestItem['field_name'];
// //             $fieldValue = $requestItem['field_value'];
// //             $condition = $requestItem['condition'];

// //             switch ($condition) {
// //                 case 'starts_with':
// //                     $query->where($fieldName, 'like', $fieldValue . '%');
// //                     break;
// //                 case 'ends_with':
// //                     $query->where($fieldName, 'like', '%' . $fieldValue);
// //                     break;
// //                 case 'equals':
// //                     $query->where($fieldName, '=', $fieldValue);
// //                     break;
// //                 case 'not_equals':
// //                     $query->where($fieldName, '!=', $fieldValue);
// //                     break;
// //                 case 'contains':
// //                     $query->where($fieldName, 'like', '%' . $fieldValue . '%');
// //                     break;
// //                 case 'not_contains':
// //                     $query->where($fieldName, 'not like', '%' . $fieldValue . '%');
// //                     break;
// //                 default:
// //                     // Handle invalid condition if needed
// //                     break;
// //             }
// //         }

// //         // Apply group by and sort order
// //         if (!empty($validatedData['group_by'])) {
// //             foreach ($validatedData['group_by'] as $groupBy) {
// //                 $fieldName = $groupBy['field_name'];
// //                 $sortOrder = strtolower($groupBy['sort_order']) === 'ascending' ? 'asc' : 'desc';
// //                 $query->groupBy($fieldName)->orderBy($fieldName, $sortOrder);
// //             }
// //         }

// //         // Ensure all selected columns are part of the group by clause
// //         $selectColumns = $validatedData['select_column'];
// //         $groupByColumns = array_column($validatedData['group_by'] ?? [], 'field_name');
// //         $allGroupByColumns = array_unique(array_merge($selectColumns, $groupByColumns));
// //         $query->groupBy($allGroupByColumns);

// //         // Get the results
// //         $results = $query->get();

// //         // Check if any results found
// //         if ($results->isEmpty()) {
// //             return response()->json([
// //                 'status' => 404,
// //                 'message' => 'No matching records found',
// //             ], 404);
// //         }

// //         // Prepare the response data
// //         $responseData = [];
// //         foreach ($results as $result) {
// //             $data = [];
// //             foreach ($validatedData['select_column'] as $column) {
// //                 $data[] = $result->$column;
// //             }
// //             $responseData[] = $data;
// //         }

// //         // Export to Excel
// //         $filename = 'export_' . time() . '.xlsx';
// //         return Excel::download(new CustomersExport([$validatedData['select_column'], ...$responseData]), $filename);

// //     } catch (ValidationException $e) {
// //         return response()->json([
// //             'status' => 422,
// //             'message' => $e->errors(),
// //         ], 422);
// //     } catch (Exception $e) {
// //         return response()->json([
// //             'status' => 500,
// //             'message' => $e->getMessage(),
// //         ], 500);
// //     }
// // }





// //SHOW


//     public function shows($id)
//     {
//         try {
//             // Find the report by ID
//             $report = Report::findOrFail($id);

//             // Fetch associated report module and schedule report
//             $reportModule = ReportModules::where('reportmodulesid', $id)->first();
//             $scheduleReport = ScheduleReports::where('reportid', $id)->first();

//             return response()->json([
//                 'status' => 200,
//                 'message' => 'Report retrieved successfully',
//                 'report' => $report,
//                 'reportModule' => $reportModule,
//                 'scheduleReport' => $scheduleReport,
//             ], 200);

//         } catch (ModelNotFoundException $e) {
//             return response()->json([
//                 'status' => 404,
//                 'message' => 'Report not found',
//             ], 404);
//         } catch (Exception $e) {
//             return response()->json([
//                 'status' => 500,
//                 'message' => $e->getMessage(),
//             ], 500);
//         }
//     }


// //UPDATE


//     public function updates(Request $request, $id)
// {
//     try {
//         // Validate the request
//         $validatedData = $request->validate([
//             'reportname' => 'required|string',
//             'folderid' => 'required|integer|exists:jo_reportfolder,folderid',
//             'primarymodule' => 'required|integer|exists:jo_tabs,tabid',
//             'recipients' => 'required|array',
//             'recipients.*.Users' => 'exists:users,username',
//             'recipients.*.Roles' => 'exists:jo_roles,rolename',
//             'recipients.*.Groups' => 'exists:jo_groups,group_name',
//             'scheduleid' => 'required|integer',
//             'schdate' => 'required|string|max:255',
//             'schtime' => 'required|string|max:255',
//             'schdayoftheweek' => 'string|max:255|nullable',
//             'schdayofthemonth' => 'string|max:255|nullable',
//             'schannualdates' => 'string|max:255|nullable',
//             'specificemails' => 'string|max:255|nullable',
//             'next_trigger_time' => 'required|string|max:255',
//             'fileformat' => 'required|string|max:255',
//             'select_column' => 'required|array',
//             'select_column.*' => 'required|string',
//             'group_by' => 'nullable|array',
//             'group_by.*.field_name' => 'required|string',
//             'group_by.*.sort_order' => 'required|string|in:Ascending,Descending',
//             'requests' => 'required|array',
//             'requests.*.field_name' => 'required|string',
//             'requests.*.field_value' => 'required|string',
//             'requests.*.condition' => 'required|string|in:starts_with,ends_with,equals,not_equals,contains,not_contains',
//             'requests.*.per_page' => 'nullable|integer|min:1',
//             'requests.*.export_excel' => 'nullable|boolean',
//         ]);

//         // Find the report by ID
//         $report = Report::findOrFail($id);
//         $reportModule = ReportModules::where('reportmodulesid', $id)->first();
//         $scheduleReport = ScheduleReports::where('reportid', $id)->first();

//         // Update the report
//         $report->reportname = $validatedData['reportname'];
//         $report->folderid = $validatedData['folderid'];
//         $report->save();

//         // Update the report module
//         $reportModule->primarymodule = $validatedData['primarymodule'];
//         $reportModule->save();

//         // Update the schedule report
//         $scheduleReport->scheduleid = $validatedData['scheduleid'];
//         $scheduleReport->recipients = json_encode($validatedData['recipients']);
//         $scheduleReport->schdate = $validatedData['schdate'];
//         $scheduleReport->schtime = $validatedData['schtime'];
//         $scheduleReport->schdayoftheweek = $validatedData['schdayoftheweek'];
//         $scheduleReport->schdayofthemonth = $validatedData['schdayofthemonth'];
//         $scheduleReport->schannualdates = $validatedData['schannualdates'];
//         $scheduleReport->specificemails = $validatedData['specificemails'];
//         $scheduleReport->next_trigger_time = $validatedData['next_trigger_time'];
//         $scheduleReport->fileformat = $validatedData['fileformat'];
//         $scheduleReport->save();

//         return response()->json([
//             'status' => 200,
//             'message' => 'Report updated successfully',
//             'report' => $report,
//             'reportModule' => $reportModule,
//             'scheduleReport' => $scheduleReport,
//         ], 200);

//     } catch (ModelNotFoundException $e) {
//         return response()->json([
//             'status' => 404,
//             'message' => 'Report not found',
//         ], 404);
//     } catch (ValidationException $e) {
//         return response()->json([
//             'status' => 422,
//             'message' => $e->errors(),
//         ], 422);
//     } catch (Exception $e) {
//         return response()->json([
//             'status' => 500,
//             'message' => $e->getMessage(),
//         ], 500);
//     }
// }


// //DELETE

// public function destroys($id)
// {
//     try {
//         // Find the report by ID
//         $report = Report::findOrFail($id);

//         // Delete associated report module and schedule report
//         ReportModules::where('reportmodulesid', $id)->delete();
//         ScheduleReports::where('reportid', $id)->delete();

//         // Delete the report
//         $report->delete();

//         return response()->json([
//             'status' => 200,
//             'message' => 'Report deleted successfully',
//         ], 200);

//     } catch (ModelNotFoundException $e) {
//         return response()->json([
//             'status' => 404,
//             'message' => 'Report not found',
//         ], 404);
//     } catch (Exception $e) {
//         return response()->json([
//             'status' => 500,
//             'message' => $e->getMessage(),
//         ], 500);
//     }
// }


//     public function testRoute()
// {
//     return response()->json(['message' => 'Route is working'], 200);
// }


}

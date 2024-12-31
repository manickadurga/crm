<?php

namespace App\Http\Controllers;

use App\Models\RelatedModule;
use App\Models\Report;
use App\Models\ReportModules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ChartReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Retrieve all reports with related information
            $reports = DB::table('jo_reports')
                ->join('jo_reportmodules', 'jo_reports.reportid', '=', 'jo_reportmodules.reportmodulesid')
                ->join('jo_reportfolder', 'jo_reports.folderid', '=', 'jo_reportfolder.folderid')
                ->join('users', 'jo_reports.owner', '=', 'users.id') // Assuming 'owner' in 'jo_reports' is a user ID
                ->select(
                    'jo_reports.reportname',
                    'jo_reports.owner',
                    'jo_reports.reporttype',
                    'jo_reportmodules.primarymodule',
                    'jo_reportfolder.foldername' // Adjust this if the column name is different
                )
                ->get();
    
            // Prepare the response data
            $reportData = $reports->map(function($report) {
                return [
                    'reportname' => $report->reportname,
                    'owner' => $report->owner,
                    'reporttype' => $report->reporttype,
                    'primarymodule' => $report->primarymodule,
                    'foldername' => $report->foldername,
                ];
            });
    
            return response()->json([
                'message' => 'Reports retrieved successfully',
                'data' => $reportData
            ], 200);
    
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error retrieving reports: ' . $e->getMessage(), [
                'exception' => $e
            ]);
    
            // Return a JSON response with the error details
            return response()->json([
                'message' => 'An error occurred while retrieving the reports.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'reportname' => 'required|string|max:255',
                'folderid' => 'required|integer|exists:jo_reportfolder,folderid',
                'primarymodule' => 'required|integer|exists:jo_tabs,tabid',
                'secondarymodules' => 'nullable|array|max:2',
                'secondarymodules.*' => 'nullable|integer|exists:jo_tabs,tabid',
                'description' => 'nullable|string',
                'share_report' => 'nullable|array',
                'share_report.Users' => 'nullable|array',
                'share_report.Users.*' => 'nullable|integer|exists:users,id',
                'share_report.Roles' => 'nullable|array',
                'share_report.Roles.*' => 'nullable|string|exists:jo_roles,roleid',
                'share_report.Groups' => 'nullable|array',
                'share_report.Groups.*' => 'nullable|integer|exists:jo_groups,id',
                'schedule_reports' => 'nullable|array',
                'schedule_reports.scheduleid' => 'nullable|integer',
                'schedule_reports.recipients' => 'nullable|array',
                'schedule_reports.recipients.*' => 'nullable|string',
                'schedule_reports.schdate' => 'nullable|date',
                'schedule_reports.schtime' => 'nullable|date_format:H:i:s',
                'schedule_reports.schdayoftheweek' => 'nullable|integer|between:1,7',
                'schedule_reports.schdayofthemonth' => 'nullable|integer|between:1,31',
                'schedule_reports.schannualdates' => 'nullable|array',
                'schedule_reports.schannualdates.*' => 'nullable|date',
                'schedule_reports.specificemails' => 'nullable|string',
                'schedule_reports.next_trigger_time' => 'nullable|date',
                'schedule_reports.fileformat' => 'nullable|string',
                'all_conditions' => 'nullable|array',
                'all_conditions.*.columnname' => 'required|string',
                'all_conditions.*.module' => 'required|string', // Added validation for module
                'all_conditions.*.comparator' => 'required|string',
                'all_conditions.*.value' => 'required|string',
                'all_conditions.*.column_condition' => 'nullable|string|in:AND,OR',
                'any_conditions' => 'nullable|array',
                'any_conditions.*.columnname' => 'required|string',
                'any_conditions.*.module' => 'required|string', // Added validation for module
                'any_conditions.*.comparator' => 'required|string',
                'any_conditions.*.value' => 'required|string',
                'any_conditions.*.column_condition' => 'nullable|string|in:AND,OR',
                'chart_type' => 'required|string|in:pie,vertical_bar,horizontal_bar,line',
                'groupbyfield' => 'required|string',
                'datafields' => 'required|string'
            ]);
    
            $primaryModuleId = $validatedData['primarymodule'];
            $secondaryModuleIds = $validatedData['secondarymodules'] ?? [];
            $relatedSecondaryModules = DB::table('jo_relatedlist')
                ->where('tabid', $primaryModuleId)
                ->whereIn('related_tabid', $secondaryModuleIds)
                ->pluck('related_tabid')
                ->toArray();
    
            if (count($relatedSecondaryModules) !== count($secondaryModuleIds)) {
                return response()->json(['status' => 400, 'message' => 'Secondary module is not related to the primary module'], 400);
            }
    
            $primaryModuleName = DB::table('jo_tabs')->where('tabid', $primaryModuleId)->value('name');
    
            // Retrieve the tab names for the two related modules (if provided)
            $secondaryModuleNames = [];
            if (!empty($validatedData['secondarymodules'])) {
                $secondaryModuleNames = DB::table('jo_tabs')
                    ->whereIn('tabid', $validatedData['secondarymodules'])
                    ->pluck('name')
                    ->take(2) // Ensure only the first two values are taken
                    ->toArray();
            }
    
            $queryId = DB::table('jo_selectquery')->insertGetId([
                'startindex' => 0,
                'numofobjects' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'queryid');
    
            // Convert the related module names into a single string
            $secondaryModulesString = implode(':', $secondaryModuleNames);
            $report = Report::create([
                'reportname' => $validatedData['reportname'],
                'folderid' => $validatedData['folderid'],
                'description' => $validatedData['description'],
                'reporttype' => 'chart',
                'queryid' => $queryId,
                'state' => 'CUSTOM',
                'customizable' => 1,
                'category' => 1,
                'owner' => 1,
                'sharingtype' => 'Private'
                // Other fields for the report
            ]);
    
            $reportId = $report->reportid;
            DB::table('jo_reportmodules')->updateOrInsert([
                'reportmodulesid' => $reportId,
                'primarymodule' => $primaryModuleName,
                'secondarymodules' => $secondaryModulesString
            ]);
    
            if (isset($validatedData['share_report'])) {
                $this->storeShareData($reportId, $validatedData['share_report']);
            }
    
            if (isset($validatedData['schedule_reports'])) {
                $this->storeScheduleData($reportId, $validatedData['schedule_reports']);
            }
    
            // Store all conditions (groupid = 1)
            if (isset($validatedData['all_conditions'])) {
                foreach ($validatedData['all_conditions'] as $index => $condition) {
                    if (!isset($condition['module'])) {
                        throw new \InvalidArgumentException("Module key is missing in 'all_conditions'.");
                    }
                    $this->storeCondition($queryId, $condition, $index, 1, $primaryModuleId, $secondaryModuleIds);
                }
            }
    
            // Store any conditions (groupid = 2)
            if (isset($validatedData['any_conditions'])) {
                foreach ($validatedData['any_conditions'] as $index => $condition) {
                    if (!isset($condition['module'])) {
                        throw new \InvalidArgumentException("Module key is missing in 'any_conditions'.");
                    }
                    $this->storeCondition($queryId, $condition, $index, 2, $primaryModuleId, $secondaryModuleIds);
                }
            }
            DB::table('jo_reporttype')->updateOrInsert(
                ['reportid' => $reportId],
                [
                    'data' => json_encode([
                        'type' => $validatedData['chart_type'],
                        'groupbyfield' => $validatedData['groupbyfield'],
                        'datafields' => $validatedData['datafields'],
                    ]),
                ]
            );
    
    
    
            $storedData = [
                'report' => $report,
                'modules' => [
                    'primarymodule' => $primaryModuleName,
                    'secondarymodules' => $secondaryModulesString,
                ],
                'share_report' => $validatedData['share_report'] ?? null,
                'schedule_reports' => $validatedData['schedule_reports'] ?? null,
            ];
            
            $groupbyfield = $validatedData['groupbyfield'];
            $datafields = $validatedData['datafields'];
            $results = $this->buildQuery(
                $primaryModuleId,
                $secondaryModuleIds,
                $validatedData['all_conditions'] ?? [],
                $validatedData['any_conditions'] ?? [],
                $groupbyfield
            )
            ->selectRaw("$groupbyfield, COUNT(*) as record_count")
            ->groupBy($groupbyfield)
            ->get();
            
            $chartData = [
                'labels' => $results->pluck($groupbyfield)->toArray(),
                'data' => $results->pluck('record_count')->toArray(),
            ];
            return response()->json([
                'message' => 'Report created successfully',
                'data' => $storedData,
                'data1' => $results,
                'chartdata'=>$chartData
            ], 201);
    
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error creating report: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
    
            // Return a JSON response with the error details
            return response()->json([
                'message' => 'An error occurred while creating the report.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    protected function storeCondition($queryId, $condition, $columnIndex, $groupId, $primaryModuleId, $secondaryModuleIds)
{
    try {
        // Determine if column exists in any module
        $moduleId = $this->findModuleWithColumn($condition['columnname'], $primaryModuleId, $secondaryModuleIds);

        if (!$moduleId) {
            throw new \Exception('Column name not found in any module: ' . $condition['columnname']);
        }

        // Construct the column name dynamically
        $columnname = $this->constructColumnName($moduleId, $condition['columnname']);
        if (!$columnname) {
            throw new \Exception('Invalid column name constructed: ' . $condition['columnname']);
        }

        // Log the column name and module information for debugging
        Log::info('Storing condition', [
            'queryId' => $queryId,
            'condition' => $condition,
            'moduleId' => $moduleId,
            'columnname' => $columnname
        ]);

        // Store or update the condition in jo_relcriteria
        DB::table('jo_relcriteria')->updateOrInsert(
            ['queryid' => $queryId, 'columnindex' => $columnIndex],
            [
                'columnname' => $columnname,
                'comparator' => $condition['comparator'],
                'value' => $condition['value'],
                'groupid' => $groupId,
                'column_condition' => $condition['column_condition'] ?? ($groupId == 1 ? 'AND' : 'OR'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Log the grouping data
        Log::info('Storing grouping data', [
            'queryId' => $queryId,
            'groupId' => $groupId,
            'conditionExpression' => $condition['condition_expression'] ?? ''
        ]);

        // Determine group_condition and condition_expression based on groupId and conditions
        $groupingData = [
            'queryid' => $queryId,
            'groupid' => $groupId,
            'condition_expression' => $condition['condition_expression'] ?? '',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Set group_condition based on your logic
        if ($groupId == 1) {
            $groupingData['group_condition'] = 'AND'; // Set default condition for group 1
        } elseif ($groupId == 2) {
            $groupingData['group_condition'] = 'OR'; // Set default condition for group 2
        }

        // Store or update the grouping criteria in jo_relcriteria_grouping
        DB::table('jo_relcriteria_grouping')->updateOrInsert(
            ['queryid' => $queryId, 'groupid' => $groupId],
            $groupingData
        );

    } catch (\Exception $e) {
        // Enhanced error logging
        Log::error('Error storing condition: ' . $e->getMessage(), [
            'exception' => $e,
            'queryId' => $queryId,
            'condition' => $condition,
            'moduleId' => $moduleId ?? 'unknown'
        ]);
        throw $e;
    }
}

    
    private function findModuleWithColumn($columnname, $primaryModuleId, $secondaryModuleIds)
    {
        // Check column in primary module
        $existsInPrimary = DB::table('jo_fields')
            ->where('fieldname', $columnname)
            ->where('tabid', $primaryModuleId)
            ->exists();
    
        if ($existsInPrimary) {
            return $primaryModuleId;
        }
    
        // Check column in secondary modules
        foreach ($secondaryModuleIds as $secondaryModuleId) {
            $existsInSecondary = DB::table('jo_fields')
                ->where('fieldname', $columnname)
                ->where('tabid', $secondaryModuleId)
                ->exists();
    
            if ($existsInSecondary) {
                return $secondaryModuleId;
            }
        }
    
        // Column not found in any module
        return null;
    }
    
    protected function constructColumnName($moduleId, $columnName)
    {
        // Fetch the field details from the database
        $field = DB::table('jo_fields')
            ->select('tablename', 'columnname', 'fieldlabel', 'fieldname', 'typeofdata')
            ->where('fieldname', $columnName)
            ->where('tabid', $moduleId)
            ->first();
        
        // Check if the field exists
        if (!$field) {
            Log::warning('Field not found', [
                'moduleId' => $moduleId,
                'columnName' => $columnName
            ]);
            return null; // Optionally handle this case with a specific error message or default value
        }
    
        // Extract the necessary information from the field
        $tableName = $field->tablename;
        $typeOfData = isset($field->typeofdata) ? explode('~', $field->typeofdata)[0] : '';
    
        // Format and return the column name
        return sprintf(
            "%s.%s:%s:%s:%s",
            $tableName,
            $field->columnname,
            $field->fieldlabel,
            $field->fieldname,
            strtoupper($typeOfData)
        );
    }
    
    private function storeShareData($reportId, $shareReportData)
    {
        Log::info('Share Report Data:', $shareReportData);
    
        // Handle Users
        if (!empty($shareReportData['Users'])) {
            foreach ($shareReportData['Users'] as $userId) {
                // Check if the record already exists
                $exists = DB::table('jo_report_shareusers')
                    ->where('reportid', $reportId)
                    ->where('userid', $userId)
                    ->exists();
                
                if (!$exists) {
                    // Insert new record if it does not exist
                    DB::table('jo_report_shareusers')->insert([
                        'reportid' => $reportId,
                        'userid' => $userId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    
        // Handle Roles
        if (!empty($shareReportData['Roles'])) {
            foreach ($shareReportData['Roles'] as $roleId) {
                // Check if the record already exists
                $exists = DB::table('jo_report_shareroles')
                    ->where('reportid', $reportId)
                    ->where('roleid', $roleId)
                    ->exists();
                
                if (!$exists) {
                    // Insert new record if it does not exist
                    DB::table('jo_report_shareroles')->insert([
                        'reportid' => $reportId,
                        'roleid' => $roleId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    
        // Handle Groups
        if (!empty($shareReportData['Groups'])) {
            foreach ($shareReportData['Groups'] as $groupId) {
                // Check if the record already exists
                $exists = DB::table('jo_report_sharegroups')
                    ->where('reportid', $reportId)
                    ->where('groupid', $groupId)
                    ->exists();
                
                if (!$exists) {
                    // Insert new record if it does not exist
                    DB::table('jo_report_sharegroups')->insert([
                        'reportid' => $reportId,
                        'groupid' => $groupId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
    
private function storeScheduleData($reportId, $scheduleData)
{
    // Assuming you have a table called 'jo_report_schedules' for storing scheduling info
    DB::table('jo_schedulereports')->updateOrInsert(
        ['reportid' => $reportId],
        [
            'scheduleid' => $scheduleData['scheduleid'] ?? null,
            'recipients' => json_encode($scheduleData['recipients'] ?? []),
            'schdate' => $scheduleData['schdate'] ?? null,
            'schtime' => $scheduleData['schtime'] ?? null,
            'schdayoftheweek' => $scheduleData['schdayoftheweek'] ?? null,
            'schdayofthemonth' => $scheduleData['schdayofthemonth'] ?? null,
            'schannualdates' => json_encode($scheduleData['schannualdates'] ?? []),
            'specificemails' => $scheduleData['specificemails'] ?? '',
            'next_trigger_time' => $scheduleData['next_trigger_time'] ?? null,
           'fileformat' => $scheduleData['fileformat'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]
    );
}
private function applyCondition($query, $table, $columnname, $comparator, $value)
{
    switch ($comparator) {
        case 'starts_with':
            $query->where($table . '.' . $columnname, 'LIKE', $value . '%');
            break;
        case 'contains':
            $query->where($table . '.' . $columnname, 'LIKE', '%' . $value . '%');
            break;
        case 'ends_with':
            $query->where($table . '.' . $columnname, 'LIKE', '%' . $value);
            break;
        case 'is_empty':
            $query->whereNull($table . '.' . $columnname);
            break;
        case 'equals':
            $query->where($table . '.' . $columnname, '=', $value);
            break;
        case 'not_equal':
            $query->where($table . '.' . $columnname, '<>', $value);
            break;
        case 'does_not_contain':
            $query->where($table . '.' . $columnname, 'NOT LIKE', '%' . $value . '%');
            break;
        default:
            throw new \InvalidArgumentException("Unsupported comparator: $comparator");
    }
}
private function buildQuery($primaryModuleId, $secondaryModuleIds, $allConditions, $anyConditions, $groupbyfield)
{
    $moduleTableMap = [
        1 => 'jo_customers',
        2 => 'jo_teamtasks',
        3 => 'jo_tasks',
        7 => 'jo_invoices',
        8 => 'jo_payments',
        9 => 'jo_pipelines',
        10 => 'jo_estimates',
        11 => 'jo_incomes',
        12 => 'jo_proposals',
        13 => 'jo_equipments',
        14 => 'jo_products',
        15 => 'jo_expenses',
        16 => 'jo_documents',
        17 => 'jo_teams',
        18 => 'jo_recuring_expenses',
        19 => 'jo_proposal_templates',
        20 => 'jo_organizations',
        21 => 'jo_clients',
        22 => 'jo_departments',
        23 => 'jo_employment_types',
        24 => 'jo_tags',
        25 => 'jo_vendors',
        26 => 'jo_leads',
        27 => 'jo_projects',
        28 => 'jo_manage_employees'
        // Add other mappings as needed
    ];

    // Validate primary module
    if (!isset($moduleTableMap[$primaryModuleId])) {
        throw new \Exception('Primary module table does not exist.');
    }

    // Get primary table
    $primaryTable = $moduleTableMap[$primaryModuleId];
    $query = DB::table($primaryTable . ' as pm');

    $columnsToSelect = [];
    $groupByColumns = [];
    $conditionsApplied = false;

    // Handle all conditions with AND logic
    foreach ($allConditions as $condition) {
        if (isset($condition['columnname'], $condition['module'], $condition['comparator'], $condition['value'])) {
            $table = $moduleTableMap[$condition['module']] ?? $primaryTable;
            $alias = $table === $primaryTable ? 'pm' : $table;

            if (Schema::hasColumn($table, $condition['columnname'])) {
                $this->applyCondition($query, $alias, $condition['columnname'], $condition['comparator'], $condition['value']);
                $columnsToSelect[] = $alias . '.' . $condition['columnname'];
                $groupByColumns[] = $alias . '.' . $condition['columnname'];
                $conditionsApplied = true;
            } else {
                Log::warning("Column {$condition['columnname']} does not exist in table {$table}.");
            }
        }
    }

    // Handle any conditions with OR logic
    if ($anyConditions) {
        $query->where(function ($q) use ($anyConditions, $moduleTableMap, $primaryTable) {
            foreach ($anyConditions as $condition) {
                if (isset($condition['columnname'], $condition['module'], $condition['comparator'], $condition['value'])) {
                    $table = $moduleTableMap[$condition['module']] ?? $primaryTable;
                    $alias = $table === $primaryTable ? 'pm' : $table;

                    $q->orWhere(function ($q) use ($alias, $condition) {
                        if (Schema::hasColumn($alias, $condition['columnname'])) {
                            $this->applyCondition($q, $alias, $condition['columnname'], $condition['comparator'], $condition['value']);
                        } else {
                            Log::warning("Column {$condition['columnname']} does not exist in table {$alias}.");
                        }
                    });
                }
            }
        });
    }

    // Select the columns for aggregation
    if ($conditionsApplied) {
        $columnsToSelect = array_unique($columnsToSelect);
        $query->select($columnsToSelect)
              ->selectRaw('COUNT(*) as record_count');
    } else {
        // No conditions applied, just select the grouping field
        $query->select('pm.' . $groupbyfield)
              ->selectRaw('COUNT(*) as record_count');
        $groupByColumns[] = 'pm.' . $groupbyfield;
    }

    // Apply the GROUP BY clause
    if (!empty($groupByColumns)) {
        $query->groupBy($groupByColumns);
    }

    return $query;
}



public function generateReport($queryId, $primaryModuleId, $secondaryModuleIds, $allConditions, $anyConditions, $groupbyfield = null)
{
    // Ensure the groupbyfield is provided, defaulting to the first condition's column if none is provided
    if (is_null($groupbyfield) && !empty($allConditions)) {
        $groupbyfield = $allConditions[0]['columnname']; // Use the first condition's columnname as the default groupbyfield
    }

    // Build the query
    $query = $this->buildQuery($primaryModuleId, $secondaryModuleIds, $allConditions, $anyConditions, $groupbyfield);

    // Fetch results
    $results = $query->get();

    return $results;
}




    /**
     * Display the specified resource.
     */
    public function show($reportId)
    {
        try {
            // Fetch the report with the given ID
            $report = Report::findOrFail($reportId);
    
            // Retrieve primary and secondary module data from the jo_reportmodules table
            $reportModules = DB::table('jo_reportmodules')->where('reportmodulesid', $reportId)->first();
    
            if (!$reportModules) {
                throw new \Exception('No module data found for the report.');
            }
    
            // Extract primary and secondary module names
            $primaryModuleName = $reportModules->primarymodule;
            $secondaryModuleNames = explode(':', $reportModules->secondarymodules);
    
            // Ensure secondaryModuleNames is an array with no empty values
            $secondaryModuleNames = array_filter($secondaryModuleNames, 'strlen');
    
            // Log for debugging
            Log::info('Primary Module Name:', ['primaryModuleName' => $primaryModuleName]);
            Log::info('Secondary Module Names:', ['secondaryModuleNames' => $secondaryModuleNames]);
    
            // Retrieve the primary module ID (assuming `name` is used to retrieve `tabid`)
            $primaryModuleId = DB::table('jo_tabs')->where('name', $primaryModuleName)->value('tabid');
    
            if (!$primaryModuleId) {
                throw new \Exception('Primary module ID not found.');
            }
    
            // Retrieve the tab IDs for the secondary modules if there are any
            $secondaryModuleIds = [];
            if (!empty($secondaryModuleNames)) {
                $secondaryModuleIds = DB::table('jo_tabs')
                    ->whereIn('name', $secondaryModuleNames)
                    ->pluck('tabid')
                    ->toArray();
            }
    
            // Convert IDs back to names for the response
            $secondaryModuleNames = DB::table('jo_tabs')
                ->whereIn('tabid', $secondaryModuleIds)
                ->pluck('name')
                ->toArray();
    
            $secondaryModulesString = implode(':', $secondaryModuleNames);
    
            // Retrieve share data
            $shareReportData = [
                'Users' => DB::table('jo_report_shareusers')->where('reportid', $reportId)->pluck('userid')->toArray(),
                'Roles' => DB::table('jo_report_shareroles')->where('reportid', $reportId)->pluck('roleid')->toArray(),
                'Groups' => DB::table('jo_report_sharegroups')->where('reportid', $reportId)->pluck('groupid')->toArray(),
            ];
    
            // Retrieve schedule data
            $scheduleReportData = DB::table('jo_schedulereports')->where('reportid', $reportId)->first();
    
            // Retrieve conditions
            $allConditions = DB::table('jo_relcriteria')
                ->where('queryid', $report->queryid)
                ->where('groupid', 1)
                ->get()
                ->toArray();
    
            $anyConditions = DB::table('jo_relcriteria')
                ->where('queryid', $report->queryid)
                ->where('groupid', 2)
                ->get()
                ->toArray();
    
            // Prepare the response data
            $reportData = [
                'report' => $report,
                'modules' => [
                    'primarymodule' => $primaryModuleName,
                    'secondarymodules' => $secondaryModulesString,
                ],
                'share_report' => $shareReportData,
                'schedule_reports' => $scheduleReportData,
                'all_conditions' => $allConditions,
                'any_conditions' => $anyConditions,
            ];
    
            return response()->json([
                'message' => 'Report retrieved successfully',
                'data' => $reportData
            ], 200);
    
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error retrieving report: ' . $e->getMessage(), [
                'exception' => $e
            ]);
    
            // Return a JSON response with the error details
            return response()->json([
                'message' => 'An error occurred while retrieving the report.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $reportId)
{
    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'reportname' => 'nullable|string|max:255',
            'folderid' => 'nullable|integer|exists:jo_reportfolder,folderid',
            'primarymodule' => 'nullable|integer|exists:jo_tabs,tabid',
            'secondarymodules' => 'nullable|array|max:2',
            'secondarymodules.*' => 'nullable|integer|exists:jo_tabs,tabid',
            'description' => 'nullable|string',
            'share_report' => 'nullable|array',
            'share_report.Users' => 'nullable|array',
            'share_report.Users.*' => 'nullable|integer|exists:users,id',
            'share_report.Roles' => 'nullable|array',
            'share_report.Roles.*' => 'nullable|string|exists:jo_roles,roleid',
            'share_report.Groups' => 'nullable|array',
            'share_report.Groups.*' => 'nullable|integer|exists:jo_groups,id',
            'schedule_reports' => 'nullable|array',
            'schedule_reports.scheduleid' => 'nullable|integer',
            'schedule_reports.recipients' => 'nullable|array',
            'schedule_reports.recipients.*' => 'nullable|string',
            'schedule_reports.schdate' => 'nullable|date',
            'schedule_reports.schtime' => 'nullable|date_format:H:i:s',
            'schedule_reports.schdayoftheweek' => 'nullable|integer|between:1,7',
            'schedule_reports.schdayofthemonth' => 'nullable|integer|between:1,31',
            'schedule_reports.schannualdates' => 'nullable|array',
            'schedule_reports.schannualdates.*' => 'nullable|date',
            'schedule_reports.specificemails' => 'nullable|string',
            'schedule_reports.next_trigger_time' => 'nullable|date',
            'schedule_reports.fileformat' => 'nullable|string',
            'all_conditions' => 'nullable|array',
            'all_conditions.*.columnname' => 'nullable|string',
            'all_conditions.*.module' => 'nullable|string',
            'all_conditions.*.comparator' => 'nullable|string',
            'all_conditions.*.value' => 'nullable|string',
            'all_conditions.*.column_condition' => 'nullable|string|in:AND,OR',
            'any_conditions' => 'nullable|array',
            'any_conditions.*.columnname' => 'nullable|string',
            'any_conditions.*.module' => 'nullable|string',
            'any_conditions.*.comparator' => 'nullable|string',
            'any_conditions.*.value' => 'nullable|string',
            'any_conditions.*.column_condition' => 'nullable|string|in:AND,OR',
            'chart_type' => 'nullable|string|in:pie,vertical_bar,horizontal_bar,line',
            'groupbyfield' => 'nullable|string',
            'datafields' => 'nullable|string'
        ]);
    
        // Check if the report exists
        $report = Report::findOrFail($reportId);

        $primaryModuleId = $validatedData['primarymodule'];
        $secondaryModuleIds = $validatedData['secondarymodules'] ?? [];
        $relatedSecondaryModules = DB::table('jo_relatedlist')
            ->where('tabid', $primaryModuleId)
            ->whereIn('related_tabid', $secondaryModuleIds)
            ->pluck('related_tabid')
            ->toArray();
    
        if (count($relatedSecondaryModules) !== count($secondaryModuleIds)) {
            return response()->json(['status' => 400, 'message' => 'Secondary module is not related to the primary module'], 400);
        }
    
        $primaryModuleName = DB::table('jo_tabs')->where('tabid', $primaryModuleId)->value('name');
    
        // Retrieve the tab names for the secondary modules
        $secondaryModuleNames = [];
        if (!empty($validatedData['secondarymodules'])) {
            $secondaryModuleNames = DB::table('jo_tabs')
                ->whereIn('tabid', $validatedData['secondarymodules'])
                ->pluck('name')
                ->take(2)
                ->toArray();
        }
    
        $secondaryModulesString = implode(':', $secondaryModuleNames);

        // Update the report
        $report->update([
            'reportname' => $validatedData['reportname'],
            'folderid' => $validatedData['folderid'],
            'description' => $validatedData['description'],
            'reporttype' => 'chart',
            'state' => 'CUSTOM',
            'customizable' => 1,
            'category' => 1,
            'owner' => 1,
            'sharingtype' => 'Private'
            // Other fields for the report if needed
        ]);

        // Update or insert the report modules
        DB::table('jo_reportmodules')->updateOrInsert([
            'reportmodulesid' => $reportId,
        ], [
            'primarymodule' => $primaryModuleName,
            'secondarymodules' => $secondaryModulesString
        ]);

        if (isset($validatedData['share_report'])) {
            $this->storeShareData($reportId, $validatedData['share_report']);
        }

        if (isset($validatedData['schedule_reports'])) {
            $this->storeScheduleData($reportId, $validatedData['schedule_reports']);
        }

        // Update all conditions (groupid = 1)
        if (isset($validatedData['all_conditions'])) {
            // Delete existing conditions
            DB::table('jo_relcriteria')->where('queryid', $report->queryid)->where('groupid', 1)->delete();
            
            foreach ($validatedData['all_conditions'] as $index => $condition) {
                if (!isset($condition['module'])) {
                    throw new \InvalidArgumentException("Module key is missing in 'all_conditions'.");
                }
                $this->storeCondition($report->queryid, $condition, $index, 1, $primaryModuleId, $secondaryModuleIds);
            }
        }

        // Update any conditions (groupid = 2)
        if (isset($validatedData['any_conditions'])) {
            // Delete existing conditions
            DB::table('jo_relcriteria')->where('queryid', $report->queryid)->where('groupid', 2)->delete();
            
            foreach ($validatedData['any_conditions'] as $index => $condition) {
                if (!isset($condition['module'])) {
                    throw new \InvalidArgumentException("Module key is missing in 'any_conditions'.");
                }
                $this->storeCondition($report->queryid, $condition, $index, 2, $primaryModuleId, $secondaryModuleIds);
            }
        }

        $storedData = [
            'report' => $report,
            'modules' => [
                'primarymodule' => $primaryModuleName,
                'secondarymodules' => $secondaryModulesString,
            ],
            'share_report' => $validatedData['share_report'] ?? null,
            'schedule_reports' => $validatedData['schedule_reports'] ?? null,
        ];

        $groupbyfield = $validatedData['groupbyfield'];
        $datafields = $validatedData['datafields'];

        $results = $this->buildQuery(
            $primaryModuleId,
            $secondaryModuleIds,
            $validatedData['all_conditions'] ?? [],
            $validatedData['any_conditions'] ?? [],
            $groupbyfield
        )
        ->selectRaw("$groupbyfield, COUNT(*) as record_count")
        ->groupBy($groupbyfield)
        ->get();

        $chartData = [
            'labels' => $results->pluck($groupbyfield)->toArray(),
            'data' => $results->pluck('record_count')->toArray(),
        ];

        return response()->json([
            'message' => 'Report updated successfully',
            'data' => $storedData,
            'data1' => $results,
            'chartdata' => $chartData
        ], 200);

    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Error updating report: ' . $e->getMessage(), [
            'exception' => $e,
            'request' => $request->all()
        ]);

        // Return a JSON response with the error details
        return response()->json([
            'message' => 'An error occurred while updating the report.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($reportId)
{
    try {
        // Fetch the report with the given ID
        $report = Report::findOrFail($reportId);

        // Delete related data
        DB::table('jo_reportmodules')->where('reportmodulesid',$reportId)->delete();
        DB::table('jo_report_shareusers')->where('reportid', $reportId)->delete();
        DB::table('jo_report_shareroles')->where('reportid', $reportId)->delete();
        DB::table('jo_report_sharegroups')->where('reportid', $reportId)->delete();
        DB::table('jo_schedulereports')->where('reportid', $reportId)->delete();
        DB::table('jo_relcriteria')->where('queryid', $report->queryid)->delete();
        DB::table('jo_relcriteria_grouping')->where('queryid', $report->queryid)->delete();

        // Delete the report
        $report->delete();

        return response()->json([
            'message' => 'Report deleted successfully'
        ], 200);

    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Error deleting report: ' . $e->getMessage(), [
            'exception' => $e
        ]);

        // Return a JSON response with the error details
        return response()->json([
            'message' => 'An error occurred while deleting the report.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}

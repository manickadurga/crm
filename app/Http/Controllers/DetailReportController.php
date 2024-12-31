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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Exports\CustomersExport;
use App\Exports\PdfReportExport;
use App\Models\Customers;
use App\Models\JoSelectcolumn;
use App\Models\RelCriteria;
use App\Models\ReportSortCol;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use App\Exports\ReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use App\Mail\ReportMail;
use Illuminate\Support\Facades\Mail;


class DetailReportController extends Controller
{

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


    public function exportToExcel($id)
    {
        try {
            // Fetch the data related to the id
            $data = $this->fetchDataForReport($id);

            // Create an instance of the ReportExport class
            $export = new CustomersExport($data);

            // Download the file
            return Excel::download($export, 'report_' . $id . '.xlsx');
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function fetchDataForReport($id)
    {
        $criteria = DB::table('jo_relcriteria')
                      ->where('queryid', $id)
                      ->get()
                      ->toArray();

        $grouping = DB::table('jo_reportsortcol')
                      ->where('sortcolid', $id)
                      ->get()
                      ->toArray();

        $selectColumns = DB::table('jo_selectcolumn')
                           ->where('queryid', $id)
                           ->get()
                           ->toArray();

        return [
            'criteria' => $criteria,
            'grouping' => $grouping,
            'select_columns' => $selectColumns,
        ];
    }
    public function stores(Request $request){
     try{
        $validatedData = $request->validate([
            'reportname' => 'required|string',
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
            'select_column' => 'required|array|max:25',
            'select_column.*' => 'required|string',
            'group_by' => 'nullable|array|max:3',
            'group_by.*.field_name' => 'required|string',
            'group_by.*.sort_order' => 'required|string|in:asc,desc',
            'calculations' => 'nullable|array',
            'calculations.*.column' => 'required|string',
            'calculations.*.type' => 'required|in:sum,avg,min,max',
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
            'any_conditions.*.column_condition' => 'nullable|string|in:AND,OR'
           
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
            'reporttype' => 'tabular',
            'queryid' => $queryId,
            'state' => 'CUSTOM',
            'customizable' => 1,
            'category' => 1,
            'owner' => 1,
            'sharingtype' => 'Public'
            // Other fields for the report
        ]);
        $reportId = $report->reportid;
        DB::table('jo_reportmodules')->updateOrInsert([
            'reportmodulesid' => $reportId,
            'primarymodule' => $primaryModuleName,
            'secondarymodules' => $secondaryModulesString
        ]);
        $columnIndex = 0; // Keep track of column index
        foreach ($validatedData['select_column'] as $column) {
            // Determine if column belongs to primary or secondary module
            $isPrimaryModuleField = DB::table('jo_fields')
                ->where('tabid', $primaryModuleId)
                ->where('fieldname', $column)
                ->exists();

            if ($isPrimaryModuleField) {
                // Primary module field format
                $columnName = "{$primaryModuleName}:{$column}:{$primaryModuleName}:{$column}";
            } else {
                // Handle secondary module field
                foreach ($relatedSecondaryModules as $secondaryModuleId) {
                    $secondaryModuleName = DB::table('jo_tabs')->where('tabid', $secondaryModuleId)->value('name');
                    $isSecondaryModuleField = DB::table('jo_fields')
                        ->where('tabid', $secondaryModuleId)
                        ->where('fieldname', $column)
                        ->exists();

                    if ($isSecondaryModuleField) {
                        $columnName = "{$secondaryModuleName}:{$column}:{$secondaryModuleName}:{$column}";
                        break;
                    }
                }
            }

            // Insert into jo_selectcolumn with the correct format
            DB::table('jo_selectcolumn')->insert([
                'queryid' => $queryId,
                'columnindex' => $columnIndex,
                'columnname' => $columnName,
            ]);

            $columnIndex++;
        }
        if (isset($validatedData['group_by'])) {
            foreach ($validatedData['group_by'] as $index => $group) {
                $fieldName = $group['field_name'];
                $sortOrder = $group['sort_order']; // Either 'Ascending' or 'Descending'

                // Determine if the field belongs to primary or secondary module
                $tableName = DB::table('jo_fields')
                    ->join('jo_tabs', 'jo_fields.tabid', '=', 'jo_tabs.tabid')
                    ->where('jo_fields.fieldname', $fieldName)
                    ->where(function ($query) use ($primaryModuleId, $relatedSecondaryModules) {
                        $query->where('jo_fields.tabid', $primaryModuleId)
                              ->orWhereIn('jo_fields.tabid', $relatedSecondaryModules);
                    })
                    ->value('jo_fields.tablename'); // Retrieve the correct table name

                if (!$tableName) {
                    // Skip if tableName is not found
                    continue;
                }

                // Prepare the columnname for storage
                $columnName = "{$tableName}:{$fieldName}";

                // Store the sort column information
                DB::table('jo_reportsortcol')->insert([
                    'reportid' => $reportId,
                    'columnname' => "{$columnName}:{$sortOrder}",
                    'sortorder' => $sortOrder
                ]);
            }
        }
              // Perform calculations and store results
              $calculationResults = [];
              if (isset($validatedData['calculations'])) {
                  foreach ($validatedData['calculations'] as $index => $calc) {
                      $columnName = $calc['column'];
                      $calcType = $calc['type']; // Expected to be sum, average, min, max
                      $summaryType = $index; // Using index as summaryType
              
                      // Determine the table name associated with the column
                      $tableName = DB::table('jo_fields')
                          ->join('jo_tabs', 'jo_fields.tabid', '=', 'jo_tabs.tabid')
                          ->where('jo_fields.fieldname', $columnName)
                          ->where(function ($query) use ($primaryModuleId, $relatedSecondaryModules) {
                              $query->where('jo_fields.tabid', $primaryModuleId)
                                    ->orWhereIn('jo_fields.tabid', $relatedSecondaryModules);
                          })
                          ->value('jo_fields.tablename'); // Retrieve the correct table name
              
                      if (!$tableName) {
                          // Skip if tableName is not found
                          continue;
                      }
              
                      // Correct SQL query
                      $result = DB::table($tableName)
                          ->select(DB::raw("{$calcType}({$columnName}) as result"))
                          ->pluck('result')
                          ->first();
              
                      // Insert into reportsummary table
                      DB::table('jo_reportsummary')->insert([
                          'reportsummaryid' => $reportId,
                          'summarytype' => $summaryType,
                          'columnname' => "{$tableName}:{$columnName}:{$calcType}" // Use table name here
                      ]);
              
                      // Add calculation result to the array
                      $calculationResults[] = [
                          'column' => $columnName,
                          'type' => $calcType,
                          'result' => $result,
                      ];
                  }
              }
              

        if (isset($validatedData['share_report'])) {
            $this->storeShareData($reportId, $validatedData['share_report']);
        }

        if (isset($validatedData['schedule_reports'])) {
            $this->storeScheduleData($reportId, $validatedData['schedule_reports']);
        }
       
         // Store all conditions (groupid = 1)
         // Store all conditions (groupid = 1)
         if (isset($validatedData['all_conditions'])) {
            foreach ($validatedData['all_conditions'] as $index => $condition) {
                $this->storeCondition($queryId, $condition, $index, 1, $primaryModuleId, $secondaryModuleIds);
            }
        }
        

        // Store any conditions (groupid = 2)
        if (isset($validatedData['any_conditions'])) {
            foreach ($validatedData['any_conditions'] as $index => $condition) {
                $this->storeCondition($queryId, $condition, $index, 2, $primaryModuleId, $secondaryModuleIds);
            }
        }
        
        $reportRequest = $request->all();
        $results = $this->getResultsBasedOnConditions($reportRequest);

          
          //dd($results);


       

        $storedData = [
            'report' => $report,
            'modules' => [
                'primarymodule' => $primaryModuleName,
                'secondarymodules' => $secondaryModulesString,
            ],
            'share_report' => $validatedData['share_report'] ?? null,
            'schedule_reports' => $validatedData['schedule_reports'] ?? null,
           'select_column'=>$validatedData['select_column'],
           'calculations' => $calculationResults ?? null,
           'group_by'=>$validatedData['group_by'] ?? null,
           'results' => $results 
        ];
        
       // Extract file format safely
    $fileFormat = $validatedData['schedule_reports']['fileformat'] ?? 'csv'; // Default to 'csv' if not provided

    // Ensure `file_format` is provided
    if (!$fileFormat) {
        return response()->json([
            'message' => 'File format is required.',
            'error' => 'The file_format key is missing.',
        ], 400);
    }

    // Generate report file
    try {
        $reportRequest = $request->all();
        $results = $this->getResultsBasedOnConditions($reportRequest); // Example method to get report data
        $fileContent = $this->generateReportFile($results, $fileFormat);
    } catch (Exception $e) {
        Log::error('Error generating report file: ' . $e->getMessage());
        return response()->json([
            'message' => 'An error occurred while creating the report.',
            'error' => $e->getMessage(),
        ], 500);
    }

    $reportName = $request->input('reportname'); // Adjust if necessary

    // Send email with the report
    if (!empty($validatedData['schedule_reports']['specificemails'])) {
        $recipientEmails = explode(',', $validatedData['schedule_reports']['specificemails']); // Assuming comma-separated emails

        foreach ($recipientEmails as $email) {
            $email = trim($email); // Remove any surrounding whitespace
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($email)->send(new ReportMail($fileContent, $fileFormat, $reportName));
            } else {
                Log::warning("Invalid email address: $email");
            }
        }
    } else {
        Log::warning('No recipient emails provided.');
    }

        return response()->json([
            'message' => 'Report created successfully',
            'data' => $storedData,
        ], 201);

    } catch (Exception $e) {
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
    protected function generateReportFile($results, $fileFormat)
{
    if ($fileFormat === 'csv') {
        return $this->generateCsv($results);
    } elseif ($fileFormat === 'xlsx') {
        return $this->generateXlsx($results);
    } else {
        //throw new InvalidArgumentException('Unsupported file format.');
    }
}

protected function generateCsv($results)
{
    $handle = fopen('php://temp', 'r+');
    if ($handle === false) {
       // throw new RuntimeException('Unable to open temporary file for CSV generation.');
    }

    // Add headers if needed
    // fputcsv($handle, ['Header1', 'Header2', 'Header3']);

    foreach ($results as $result) {
        fputcsv($handle, (array)$result);
    }

    rewind($handle);
    $csvContent = stream_get_contents($handle);
    fclose($handle);

    return $csvContent;
}

protected function generateXlsx($results)
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Add headers if needed
    // $sheet->setCellValue('A1', 'Header1');
    // $sheet->setCellValue('B1', 'Header2');
    // ...

    $rowIndex = 1;
    foreach ($results as $result) {
        $sheet->fromArray((array)$result, null, 'A' . $rowIndex++);
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
    if ($tempFile === false) {
       // throw new RuntimeException('Unable to create temporary file for XLSX generation.');
    }

    $writer->save($tempFile);
    $xlsxContent = file_get_contents($tempFile);
    unlink($tempFile);

    return $xlsxContent;
}

    private function getTableNameFromTabId($tabId)
    {
        // Define your module-to-table mappings
        $moduleTableMapping = [
            7 => 'jo_invoices', // Invoices module
            1 => 'jo_customers', // Customers module
            // Add other module mappings here...
        ];
    
        // Ensure a valid table name is returned
        $tableName = $moduleTableMapping[$tabId] ?? null;
        Log::info('Retrieved Table Name:', ['tabId' => $tabId, 'tableName' => $tableName]);
        return $tableName;
    }
    private function tableExists($tableName)
{
    return Schema::hasTable($tableName);
}
   
    protected function getFormattedColumn($primaryModuleId, $primaryModuleName, $relatedSecondaryModules, $column)
    {
        // Check if the column belongs to the primary module
        $isPrimaryModuleField = DB::table('jo_fields')
            ->where('tabid', $primaryModuleId)
            ->where('fieldname', $column)
            ->exists();
    
        if ($isPrimaryModuleField) {
            return "{$primaryModuleName}:{$column}:{$primaryModuleName}:{$column}";
        }
    
        // Check secondary modules
        foreach ($relatedSecondaryModules as $secondaryModuleId) {
            $secondaryModuleName = DB::table('jo_tabs')->where('tabid', $secondaryModuleId)->value('name');
            $isSecondaryModuleField = DB::table('jo_fields')
                ->where('tabid', $secondaryModuleId)
                ->where('fieldname', $column)
                ->exists();
    
            if ($isSecondaryModuleField) {
                return "{$secondaryModuleName}:{$column}:{$secondaryModuleName}:{$column}";
            }
        }
    
        return null;
    }
    
    // Helper method to get the summary type index
    protected function getSummaryType($type)
    {
        switch ($type) {
            case 'sum':
                return 0;
            case 'avg':
                return 1;
            case 'min':
                return 2;
            case 'max':
                return 3;
            default:
                return -1; // Invalid type
        }
    }    
    protected function storeCondition($queryId, $condition, $columnIndex, $groupId, $primaryModuleId, $secondaryModuleIds)
{
    try {
        // Determine if column exists in any module
        $moduleId = $this->findModuleWithColumn($condition['columnname'], $primaryModuleId, $secondaryModuleIds);

        if (!$moduleId) {
            throw new Exception('Column name not found in any module: ' . $condition['columnname']);
        }

        // Construct the column name dynamically
        $columnname = $this->constructColumnName($moduleId, $condition['columnname']);
        if (!$columnname) {
            throw new Exception('Invalid column name constructed: ' . $condition['columnname']);
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

    } catch (Exception $e) {
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
private function applyComparator($query, $column, $comparator, $value, $type = 'where')
{
    switch ($comparator) {
        case 'contains':
            $query->{$type}($column, 'LIKE', '%' . $value . '%');
            break;
        case 'equals_to':
            $query->{$type}($column, '=', $value);
            break;
        case 'greater_than':
            $query->{$type}($column, '>', $value);
            break;
        case 'less_than':
            $query->{$type}($column, '<', $value);
            break;
        // Add more comparators as needed
        default:
            throw new Exception('Unsupported comparator: ' . $comparator);
    }
}
private function getResultsBasedOnConditions($reportRequest)
{
    // Log the entire report request data
    Log::info('Report Request Data:', ['request' => $reportRequest]);

    $primaryModuleId = $reportRequest['primarymodule'] ?? null;
    Log::info('Primary Module ID:', ['primaryModuleId' => $primaryModuleId]);

    if (is_null($primaryModuleId)) {
        Log::error('Primary module ID is missing in the report request.');
        throw new Exception('Primary module ID is not found.');
    }

    $primaryTable = $this->getTableNameFromTabId($primaryModuleId);
    $secondaryModuleIds = $reportRequest['secondarymodules'] ?? [];
    $secondaryTables = array_map([$this, 'getTableNameFromTabId'], $secondaryModuleIds);

    Log::info('Retrieved Table Names:', ['primaryTable' => $primaryTable, 'secondaryTables' => $secondaryTables]);

    // Check if the primary table exists
    if (empty($primaryTable) || !$this->tableExists($primaryTable)) {
        Log::error('Primary module table does not exist.', ['primaryTable' => $primaryTable]);
        throw new Exception('Primary module table does not exist.');
    }

    // Initialize the query builder
    $query = DB::table($primaryTable);

    // Select only the columns specified in the report request
    $selectColumns = $reportRequest['select_column'] ?? [];
    $query->select($selectColumns);

    // Apply all conditions
    foreach ($reportRequest['all_conditions'] as $condition) {
        $this->applyComparator($query, $condition['columnname'], $condition['comparator'], $condition['value']);
    }

    // Apply any conditions with OR logic
    foreach ($reportRequest['any_conditions'] as $condition) {
        $this->applyComparator($query, $condition['columnname'], $condition['comparator'], $condition['value'], 'orWhere');
    }

    Log::info('Built Query:', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);

    // Fetch the results
    $results = $query->get();

    Log::info('Query Results Count:', ['count' => $results->count()]);

    return $results;
}


private function applyCondition($query, $column, $comparator, $value, $type = 'where')
{
    switch ($comparator) {
        case 'contains':
            $query->{$type}($column, 'LIKE', '%' . $value . '%');
            break;
        case 'equals_to':
            $query->{$type}($column, '=', $value);
            break;
        case 'greater_than':
            $query->{$type}($column, '>', $value);
            break;
        case 'less_than':
            $query->{$type}($column, '<', $value);
            break;
        // Add more comparators as needed
        default:
            throw new Exception('Unsupported comparator: ' . $comparator);
    }
}

private function buildQuery($primaryModuleId, $secondaryModuleIds, $allConditions, $anyConditions)
{
    $moduleTableMap = [
        1 => 'jo_customers',
        2 => 'jo_clients',
        3 => 'jo_payments',
        4 => 'jo_products',
        5 => 'jo_expenses',
        7 => 'jo_invoices',
        14 => 'jo_products',
        16 => 'jo_documents',
        // Add other mappings as needed
    ];

    // Validate primary module
    if (!isset($moduleTableMap[$primaryModuleId])) {
        throw new Exception('Primary module table does not exist.');
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
   

    return $query;
}



public function generateReport($queryId, $primaryModuleId, $secondaryModuleIds, $allConditions, $anyConditions, $groupbyfield = null)
{
    // Ensure the groupbyfield is provided, defaulting to the first condition's column if none is provided
    if (is_null($groupbyfield) && !empty($allConditions)) {
        $groupbyfield = $allConditions[0]['columnname']; // Use the first condition's columnname as the default groupbyfield
    }

    // Build the query
    $query = $this->buildQuery($primaryModuleId, $secondaryModuleIds, $allConditions, $anyConditions);

    // Fetch results
    $results = $query->get();

    return $results;
}

//     public function stores(Request $request)
//     {
//         DB::beginTransaction();
//         try {
//             $tableMappings = [
//                 1 => 'jo_customers',
//                 2 => 'jo_teamtasks',
//                 3 => 'jo_tasks',
//                 7 => 'jo_invoices',
//                 8 => 'jo_payments',
//                 9 => 'jo_pipelines',
//                 10 => 'jo_estimates',
//                 11 => 'jo_incomes',
//                 12=> 'jo_proposals',
//                 13 => 'jo_equipments',
//                 14 => 'jo_products',
//                 15 => 'jo_expenses',
//                 16=> 'jo_documents',
//                 17 => 'jo_teams',
//                 18 => 'jo_recuring_expenses',
//                 19 => 'jo_proposal_templates',
//                 20 => 'jo_organizations',
//                 21 => 'jo_clients',
//                 22 => 'jo_departments',
//                 23 => 'jo_employment_types',
//                 24 => 'jo_tags',
//                 25 => 'jo_vendors',
//                 26 => 'jo_leads',
//                 27 => 'jo_projects',
//                 28 => 'jo_manage_employees',
//             ];
    
//             // Validate the request
            // $validatedData = $request->validate([
            //     'reportname' => 'required|string',
            //     'folderid' => 'required|array|min:1',
            //     'folderid.*.folderid' => 'required|integer|exists:jo_reportfolder,folderid',
            //     'primarymodule' => 'required|integer|exists:jo_tabs,tabid',
            //     'secondarymodules' => 'nullable|integer|exists:jo_tabs,tabid',
            //     'description' => 'nullable|string',
            //     'recipients' => 'required|array',
            //     'recipients.*.Users' => 'exists:users,role',
            //     'recipients.*.Roles' => 'exists:jo_roles,rolename',
            //     'recipients.*.Groups' => 'exists:jo_groups,group_name',
            //     'scheduleid' => 'required|integer',
            //     'schdate' => 'required|string|max:255',
            //     'schtime' => 'required|string|max:255',
            //     'schdayoftheweek' => 'string|max:255|nullable',
            //     'schdayofthemonth' => 'string|max:255|nullable',
            //     'schannualdates' => 'string|max:255|nullable',
            //     'specificemails' => 'string|max:255|nullable',
            //     'next_trigger_time' => 'required|string|max:255',
            //     'fileformat' => 'required|string|max:255',
            //     'select_column' => 'required|array',
            //     'select_column.*' => 'required|string',
            //     'secondary_select_column' => 'nullable|array',
            //     'secondary_select_column.*' => 'nullable|string',
            //     'group_by' => 'nullable|array',
            //     'group_by.*.field_name' => 'required|string',
            //     'group_by.*.sort_order' => 'required|string|in:Ascending,Descending',
            //     'requests' => 'required|array',
            //     'requests.*.field_name' => 'required|string',
            //     'requests.*.field_value' => 'required|string',
            //     'requests.*.condition' => 'required|string|in:starts_with,ends_with,equals,not_equals,contains,not_contains',
            //     'secondary_requests' => 'nullable|array',
            //     'secondary_requests.*.field_name' => 'required_with:secondary_requests|string',
            //     'secondary_requests.*.field_value' => 'required_with:secondary_requests|string',
            //     'secondary_requests.*.condition' => 'required_with:secondary_requests|string|in:starts_with,ends_with,equals,not_equals,contains,not_contains',
            //     'calculations' => 'nullable|array',
            // 'calculations.*.column' => 'required|string',
            // 'calculations.*.type' => 'required|in:sum,average,min,max',
            // ]);
            
//             $primaryTabId = $validatedData['primarymodule'];
//             $secondaryTabId = $validatedData['secondarymodules'] ?? null;

//             $primaryTabName = DB::table('jo_tabs')->where('tabid', $primaryTabId)->value('name');
//             if (!$primaryTabName) {
//                 return response()->json(['status' => 400, 'message' => 'Invalid primarymodule tabid provided'], 400);
//             }
//             $primaryModuleTableName = $tableMappings[$primaryTabId] ?? '';
//             if (empty($primaryModuleTableName)) {
//                 return response()->json(['status' => 400, 'message' => 'Invalid primarymodule tabid provided'], 400);
//             }
//             $relatedTabIds = DB::table('jo_relatedlist')
//             ->where('tabid',$primaryTabId)
//             ->pluck('related_tabid')
//             ->toArray();
//             $secondaryModuleTableName = $secondaryTabId ? $tableMappings[$secondaryTabId] ?? null : null;

//             if (!empty($validatedData['secondarymodules']) && !in_array($validatedData['secondarymodules'], $relatedTabIds)) {
//                 return response()->json([
//                     'status' => 400,
//                     'message' => 'Secondary module is not related to the primary module',
//                 ], 400);
//             }
    
//             // Fetch secondary module table name
//             $secondaryTabName = null;
//             $secondaryModuleTableName = $secondaryTabId ? $tableMappings[$secondaryTabId] ?? null : null;
//             if (!empty($validatedData['secondarymodules'])) {
//                 $secondaryTabName = DB::table('jo_tabs')->where('tabid', $validatedData['secondarymodules'])->value('name') ?? null;
//                 if (!$secondaryTabName) {
//                     return response()->json([
//                         'status' => 400,
//                         'message' => 'Invalid secondarymodules tabid provided',
//                     ], 400);
//                 }
//                 $secondaryModuleTableName = $tableMappings[$validatedData['secondarymodules']] ?? null;
//             }
          
//             // Log columns from secondary module table
           
    
        
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
//             $firstFolderId = $validatedData['folderid'][0]['folderid'];
//             $report = new Report();
//             $report->reportname = $validatedData['reportname'];
//             $report->folderid = $firstFolderId;
//             $report->description = $validatedData['description'] ?? null; 
            
//             $queryid = DB::table('jo_selectquery')->insertGetId([
//                 'startindex' => 0,
//                 'numofobjects' => 0
//             ], 'queryid');
//             Log::info('Query Id', ['queryid' => $queryid]);
//         if (!is_int($queryid)) {
//             throw new Exception('Failed to get Query ID');
//         }
//             $report->queryid = $queryid;
//             $report->save();
//             $reportId = $report->reportid;
//             Log::info('Report ID:', ['reportId' => $report->reportid]);
//             $primaryColumns = Schema::getColumnListing($primaryModuleTableName);
//             Log::info('Primary Module Columns:', ['columns' => $primaryColumns]);
//             if ($secondaryModuleTableName) {
//                 $secondaryColumns = Schema::getColumnListing($secondaryModuleTableName);
//                 Log::info('Secondary Module Columns:', ['columns' => $secondaryColumns]);
//             }
//             $query = DB::table($primaryModuleTableName);
//             $fieldMappings = DB::table('jo_fields')
//                         ->whereIn('tabid', [$primaryTabId, $secondaryTabId])
//                         ->get()
//                         ->groupBy('tabid')
//                         ->mapWithKeys(function ($group, $tabid) {
//                             return [$tabid => $group->pluck('columnname')->toArray()];
//                         });
//                     Log::info('Field Mappings:', ['fieldMappings' => $fieldMappings]);
//                     $primarySelectColumns = $validatedData['select_column'];
//                     $primaryModuleColumns = $fieldMappings->get($validatedData['primarymodule'], []);
//                     $secondarySelectColumns = $validatedData['secondary_select_column'] ?? [];
//                     $secondaryModuleColumns = $fieldMappings->get($validatedData['secondarymodules'] ?? '', []);
//                     $primarySelectColumnsFormatted = [];
//                     $secondarySelectColumnsFormatted = [];
//                     if (!is_array($primarySelectColumns)) {
//                         $primarySelectColumns = [];
//                     }

//                     if (!is_array($secondarySelectColumns)) {
//                         $secondarySelectColumns = [];
//                     }

// foreach ($primarySelectColumns as $columnName) {
//     if (in_array($columnName, $primaryModuleColumns)) {
//         $primarySelectColumnsFormatted[] = "$primaryModuleTableName.$columnName";
//     } else {
//         Log::error('Invalid primary column:', ['column' => $columnName]);
//         throw new Exception("Invalid primary column: $columnName");
//     }
// }

// foreach ($secondarySelectColumns as $columnName) {
//     if (in_array($columnName, $secondaryModuleColumns)) {
//         $secondarySelectColumnsFormatted[] = "$secondaryModuleTableName.$columnName" ?? nullValue();
//     } else {
//         Log::error('Invalid secondary column:', ['column' => $columnName]);
//         throw new Exception("Invalid secondary column: $columnName");
//     }
// }
// Log::info('Primary Select Columns:', ['selectColumns' => $primarySelectColumns]);
// Log::info('Secondary Select Columns:', ['selectColumns' => $secondarySelectColumns]);
// $filterCriteria = $validatedData['requests'] ?? [];
// $query = DB::table($primaryModuleTableName)
//     ->select($primarySelectColumns) // Select the columns
//     ->groupBy($primarySelectColumns); // Group by selected columns
// foreach ($filterCriteria as $criteria) {
//     $field = $criteria['field_name'] ?? null;
//     $value = $criteria['field_value'] ?? '';
//     if ($field && in_array($field, $primaryModuleColumns)) {
//         $query->orWhere($field, 'like', $value . '%');
//     }
// }
// $perPage = $validatedData['requests'][0]['per_page'] ?? 10;


// try {
//     $results = $query->paginate($perPage);
//     $primaryQuery = $query->get(); // Execute the query

//     Log::info('Primary Results:', ['primaryResults' => $primaryQuery]);
// } catch (Exception $e) {
//     Log::error('Primary Query Error:', ['error' => $e->getMessage()]);
//     return response()->json(['status' => 500, 'message' => 'Error executing primary query: ' . $e->getMessage()], 500);
// }





// // Query for secondary module
// try {
//     // Assuming $secondaryModuleTableName is derived based on selected secondary module
//     $secondaryTableName = $secondaryModuleTableName; // e.g., 'jo_tasks'
//     $secondarySelectColumns = isset($request['secondary_select_column']) ? $request['secondary_select_column'] : [];

//     if (empty($secondarySelectColumns)) {
//         Log::info('No secondary select columns provided.');
//         $secondaryQuery = collect(); // Return an empty collection or handle as needed
//     } else {
//         // Build the secondary query
//         $secondaryQuery = DB::table($secondaryTableName)
//             ->select($secondarySelectColumns)
//             ->whereRaw('1=1');  // Placeholder if no filtering condition is needed

//         // Only add groupBy if there are columns to group by
//         if (!empty($secondarySelectColumns)) {
//             $secondaryQuery = $secondaryQuery->groupBy($secondarySelectColumns);
//         }

//         $secondaryQuery = $secondaryQuery->get();
//     }

//     Log::info('Secondary Results:', ['secondaryResults' => $secondaryQuery]);
// } catch (Exception $e) {
//     Log::error('Secondary Query Error:', ['error' => $e->getMessage()]);
//     return response()->json(['status' => 500, 'message' => 'Error executing secondary query'], 500);
// }

// //$queryid = DB::table('jo_selectquery')->insertGetId(['startindex' => 0, 'numofobjects' => 0], 'queryid');
// foreach ($primarySelectColumns as $index => $columnName) {
//     $fullColumnName = "{$primaryModuleTableName}:{$columnName}:{$primaryTabName}:{$columnName}";
//     DB::table('jo_selectcolumn')->updateOrInsert(
//         ['queryid' => $queryid, 'columnindex' => $index],
//         ['columnname' => $fullColumnName, 'updated_at' => now()]
//     );
//     Log::info('Upserting into jo_selectcolumn', ['queryid' => $queryid, 'columnindex' => $index, 'columnname' => $fullColumnName]);
// }

// foreach ($secondarySelectColumns as $index => $columnName) {
//     $fullColumnName = "{$secondaryModuleTableName}:{$columnName}:{$secondaryTabName}:{$columnName}";
//     DB::table('jo_selectcolumn')->updateOrInsert(
//         ['queryid' => $queryid, 'columnindex' => $index],
//         ['columnname' => $fullColumnName, 'updated_at' => now()]
//     );
//     Log::info('Upserting into jo_selectcolumn', ['queryid' => $queryid, 'columnindex' => $index, 'columnname' => $fullColumnName]);
// }


// // Combine or process results as needed
// $combinedResults = [
//     'primary' => $primaryQuery,
//     'secondary' => $secondaryQuery
// ];

// // Log the combined results
// Log::info('Combined Results:', ['combinedResults' => $combinedResults]);
//             foreach ($validatedData['requests'] as $index => $requestItem) {
//                 $fieldName = $requestItem['field_name'];
//                 $fieldValue = $requestItem['field_value'];
//                 $condition = $requestItem['condition'];

//                 switch ($condition) {
//                     case 'starts_with':
//                         $primaryQuery->where($fieldName, 'like', $fieldValue . '%');
//                         break;
//                     case 'ends_with':
//                         $primaryQuery->where($fieldName, 'like', '%' . $fieldValue);
//                         break;
//                     case 'equals':
//                         $primaryQuery->where($fieldName, '=', $fieldValue);
//                         break;
//                     case 'not_equals':
//                         $primaryQuery->where($fieldName, '!=', $fieldValue);
//                         break;
//                     case 'contains':
//                         $primaryQuery->where($fieldName, 'like', '%' . $fieldValue . '%');
//                         break;
//                     case 'not_contains':
//                         $primaryQuery->where($fieldName, 'not like', '%' . $fieldValue . '%');
//                         break;
//                     default:
//                         // Handle invalid condition if needed
//                         break;
//                 }
//                 $conditions[] = "{$primaryModuleTableName}:{$fieldName}:{$fieldValue}:{$condition}";

//                 // Append 'AND' if there are subsequent conditions
//                 if ($index < count($validatedData['requests']) - 1) {
//                     $conditions[] = 'AND';
//                 }
//             }
//             // Example for fetching valid columns from a table in Laravel
// $validSecondaryColumns = Schema::getColumnListing($secondaryModuleTableName);

//             foreach ($validatedData['secondary_requests'] as $index => $requestItem) {
//                 $fieldName = $requestItem['field_name'];
//                 $fieldValue = $requestItem['field_value'];
//                 $condition = $requestItem['condition'];
                
//                 // Ensure $fieldName is a valid column in your table
//                 if (!in_array($fieldName, $validSecondaryColumns)) {
//                     Log::warning('Invalid field name:', ['fieldName' => $fieldName]);
//                     continue;
//                 }
                
//                 // Ensure $fieldValue is sanitized if necessary
//                 $fieldValue = htmlspecialchars($fieldValue, ENT_QUOTES, 'UTF-8');
            
//                 switch ($condition) {
//                     case 'starts_with':
//                         $secondaryQuery->where($fieldName, 'like', $fieldValue . '%');
//                         break;
//                     case 'ends_with':
//                         $secondaryQuery->where($fieldName, 'like', '%' . $fieldValue);
//                         break;
//                     case 'equals':
//                         $secondaryQuery->where($fieldName, '=', $fieldValue);
//                         break;
//                     case 'not_equals':
//                         $secondaryQuery->where($fieldName, '!=', $fieldValue);
//                         break;
//                     case 'contains':
//                         $secondaryQuery->where($fieldName, 'like', '%' . $fieldValue . '%');
//                         break;
//                     case 'not_contains':
//                         $secondaryQuery->where($fieldName, 'not like', '%' . $fieldValue . '%');
//                         break;
//                     default:
//                         Log::warning('Unknown condition:', ['condition' => $condition]);
//                         break;
//                 }
            
//                 // Build the condition string for logging or other purposes
//                 $conditions[] = "{$secondaryModuleTableName}:{$fieldName}:{$fieldValue}:{$condition}";
//             }
            
//                 // Append 'AND' if there are subsequent conditions
//                 if ($index < count($validatedData['secondary_requests']) - 1) {
//                     $conditions[] = 'AND';
//                 }
            
            
//             $conditionString = implode(' ', $conditions);
//             Log::info("Conditions Query:",['conditionString' => $conditionString]);
            
//             $perPage = $validatedData['requests'][0]['per_page'] ?? 10;
//             $results = $query->paginate($perPage);
//             // Check if any results found
//             if ($results->isEmpty()) {
//                 return response()->json([
//                     'status' => 404,
//                     'message' => 'No matching records found',
//                 ], 404);
//             }

//             $responseData = [];

//             foreach ($results as $result) {
//                 Log::info('Processing Result:', ['result' => $result]);
            
//                 $data = [];
            
//                 // Add primary column data
//                 foreach ($validatedData['select_column'] as $column) {
//                     if (property_exists($result, $column)) {
//                         $data[$column] = $result->$column ?? null;
//                     } else {
//                         Log::warning('Primary Column Not Found:', ['column' => $column]);
//                         $data[$column] = null;
//                     }
//                 }
            
//                 // Add secondary column data (if necessary)
//                 if (!empty($validatedData['secondary_select_column']) && isset($secondaryResults)) {
//                     // Use a common key for correlation, e.g., primary module's `id` and secondary module's `related_id`
//                     $primaryKey = $result->id; // or the correct key to match with secondary results
                    
//                     // Find matching secondary result
//                     $matchingSecondary = null;
//                     foreach ($secondaryResults as $secResult) {
//                         if ($secResult->related_id == $primaryKey) { // Adjust based on your actual foreign key
//                             $matchingSecondary = $secResult;
//                             break;
//                         }
//                     }
            
//                     // Log matching secondary result for debugging
//                     Log::info('Matching Secondary Result:', ['matchingSecondary' => $matchingSecondary]);
            
//                     if ($matchingSecondary) {
//                         foreach ($validatedData['secondary_select_column'] as $column) {
//                             if (property_exists($matchingSecondary, $column)) {
//                                 $data[$column] = $matchingSecondary->$column ?? null;
//                             } else {
//                                 Log::warning('Secondary Column Not Found:', ['column' => $column]);
//                                 $data[$column] = null;
//                             }
//                         }
//                     } else {
//                         foreach ($validatedData['secondary_select_column'] as $column) {
//                             Log::warning('No Matching Secondary Data:', ['column' => $column]);
//                             $data[$column] = null;
//                         }
//                     }
//                 } else {
//                     foreach ($validatedData['secondary_select_column'] as $column) {
//                         Log::warning('Secondary Data Not Retrieved:', ['column' => $column]);
//                         $data[$column] = null;
//                     }
//                 }
            
//                 $responseData[] = $data;
//             }
            
//             Log::info('Final Response Data:', ['responseData' => $responseData]);
            
            
            
//             // Prepare recipients information

//             $recipients = [];
//             foreach ($validatedData['recipients'] as $member) {
//                 $memberInfo = [];
//                 if (isset($member['Users'])) {
//                     $user = User::where('role', $member['Users'])->first();
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
//                         $memberInfo['group_id'] = $group->id;
//                     } else {
//                         throw ValidationException::withMessages(['recipients' => "Group with group_name '{$member['Groups']}' not found"]);
//                     }
//                 }
//                 Log::info('Member Info:', $memberInfo);

//                 $recipients[] = $memberInfo;
//             }
//    foreach ($validatedData['select_column'] as $index => $columnName) {
//     $fullColumnName = "{$primaryModuleTableName}:{$columnName}:{$primaryTabName}:{$columnName}";
//     DB::table('jo_selectcolumn')->updateOrInsert(
//         ['queryid' => $queryid, 'columnindex' => $index],
//         ['columnname' => $fullColumnName, 'updated_at' => now()]
//     );
//     Log::info('Upserting into jo_selectcolumn', [
//         'queryid' => $queryid,
//         'columnindex' => $index,
//         'columnname' => $fullColumnName
//     ]);
// }

// foreach ($validatedData['secondary_select_column'] as $index => $columnName) {
//     $fullColumnName = "{$secondaryModuleTableName}:{$columnName}:{$secondaryTabName}:{$columnName}";
//     DB::table('jo_selectcolumn')->updateOrInsert(
//         ['queryid' => $queryid, 'columnindex' => $index + count($validatedData['select_column'])],
//         ['columnname' => $fullColumnName, 'updated_at' => now()]
//     );
//     Log::info('Upserting into jo_selectcolumn', [
//         'queryid' => $queryid,
//         'columnindex' => $index + count($validatedData['select_column']),
//         'columnname' => $fullColumnName
//     ]);
// }
// // Assuming $primaryModuleTableName and $secondaryModuleTableName are set correctly
// $maxColumnIndex = RelCriteria::where('queryid', $queryid)->max('columnindex');
// $startingIndex = $maxColumnIndex !== null ? $maxColumnIndex + 1 : 0;

// // Combine primary and secondary requests
// $combinedRequests = array_merge(
//     array_map(function($item) use ($primaryModuleTableName) {
//         return [
//             'field_name' => $item['field_name'],
//             'field_value' => $item['field_value'],
//             'condition' => $item['condition'],
//             'module' => 'primary',
//             'table' => $primaryModuleTableName
//         ];
//     }, $validatedData['requests']),
//     array_map(function($item) use ($secondaryModuleTableName) {
//         return [
//             'field_name' => $item['field_name'],
//             'field_value' => $item['field_value'],
//             'condition' => $item['condition'],
//             'module' => 'secondary',
//             'table' => $secondaryModuleTableName
//         ];
//     }, $validatedData['secondary_requests'] ?? []) // Handle secondary requests if they exist
// );

// // Store criteria
// foreach ($combinedRequests as $index => $requestItem) {
//     $isPrimary = $requestItem['module'] === 'primary';
//     $moduleTableName = $requestItem['table'];
//     $tabName = $isPrimary ? 'Primary Tab' : 'Secondary Tab';

//     // Generate the column name
//     $columnName = "{$moduleTableName}:{$requestItem['field_name']}:{$tabName}:{$requestItem['field_name']}";

//     // Log the criteria being created
//     Log::info('Creating RelCriteria:', [
//         'queryid' => $queryid,
//         'columnindex' => $startingIndex + $index,
//         'columnname' => $columnName,
//         'comparator' => $requestItem['condition'],
//         'value' => $requestItem['field_value'],
//         'groupid' => 1,
//         'column_condition' => $index < count($combinedRequests) - 1 ? 'AND' : ''
//     ]);

//     // Create the RelCriteria entry
//     RelCriteria::create([
//         'queryid' => $queryid,
//         'columnindex' => $startingIndex + $index,
//         'columnname' => $columnName,
//         'comparator' => $requestItem['condition'],
//         'value' => $requestItem['field_value'],
//         'groupid' => 1,
//         'column_condition' => $index < count($combinedRequests) - 1 ? 'AND' : '',
//     ]);
// }



//         if ($report && $report->reportid) {
//             $reportModule = new ReportModules();
//             $reportModule->reportmodulesid = $report->reportid;
//             $reportModule->primarymodule = $primaryTabName;
//             $reportModule->secondarymodules =$secondaryTabName ?? null;
//             $reportModule->save();
//         } else {
//             throw new Exception("Invalid report or report ID.");
//         }
//         //dd($reportModule);



//         $reportName = $report->reportname;
//         $calculations = []; // Define or fetch the calculations
//         $fileFormat = $request->input('fileformat');
//         $formattedCalculations = []; 
    
//         // Generate the file content
//         $fileContent = $this->generateFileContent($fileFormat, $data, $responseData, $reportName, $calculations);
//         Log::info('Generated File Content Length:', ['length' => strlen($fileContent)]);
    
//         // Update the schedule report
//         $scheduleReport = new ScheduleReports();
//         $scheduleReport->reportid = $report->reportid;
//         $scheduleReport->scheduleid = $request->input('scheduleid');
//         $scheduleReport->recipients = json_encode($request->input('recipients'));
//         $scheduleReport->schdate = $request->input('schdate');
//         $scheduleReport->schtime = $request->input('schtime');
//         $scheduleReport->schdayoftheweek = $request->input('schdayoftheweek');
//         $scheduleReport->schdayofthemonth = $request->input('schdayofthemonth');
//         $scheduleReport->schannualdates = $request->input('schannualdates');
//         $scheduleReport->specificemails = $request->input('specificemails');
//         $scheduleReport->next_trigger_time = $request->input('next_trigger_time');
//         $scheduleReport->fileformat = $fileFormat;
//         $scheduleReport->save();
    
//         // Send the email
//         Mail::to($request->input('specificemails'))
//     ->send(new ReportMail($fileContent, $fileFormat, $reportName, $data, $formattedCalculations,$responseData));
    
//            // $reportSummaryData = [];
           

//            if (isset($validatedData['calculations']) && is_array($validatedData['calculations'])) {
//             $reportSummaryData = [];
//             $calculationResults = []; // Initialize array to hold the calculations
        
//             foreach ($validatedData['calculations'] as $index => $calculation) {
//                 $column = $calculation['column'] ?? null;
//                 $type = $calculation['type'] ?? null;
        
//                 // Validate that both $column and $type are not null
//                 if (is_null($column) || is_null($type)) {
//                     Log::warning('Skipping calculation due to missing column or type', ['index' => $index, 'column' => $column, 'type' => $type]);
//                     continue;
//                 }
        
//                 // Map the column name to the correct format
//                 $columnName = "{$tableMappings[$validatedData['primarymodule']]}:{$column}:{$type}";
        
//                 // Collect data to be inserted
//                 $reportSummaryData[] = [
//                     'reportsummaryid' => $reportId,
//                     'summarytype' => $index, // Using $type as summary type (sum, average, etc.)
//                     'columnname' => $columnName,
//                 ];
        
//                 // Add to calculation results
//                 if (!isset($calculationResults[$column])) {
//                     $calculationResults[$column] = [];
//                 }
        
//                 // Perform the calculation based on type
//                 try {
//                     $result = $this->performCalculation($type, $columnName);
//                     $calculationResults[$column][$type] = $result;
//                     Log::info('Calculation result', ['column' => $column, 'type' => $type, 'result' => $result]);
//                 } catch (Exception $e) {
//                     Log::error('Error performing calculation', ['error' => $e->getMessage()]);
//                 }
//             }
        
//             // Insert data into the jo_reportsummary table
//             try {
//                 if (!empty($reportSummaryData)) {
//                     DB::table('jo_reportsummary')->insert($reportSummaryData);
//                 } else {
//                     Log::warning('No data to insert into jo_reportsummary');
//                 }
//             } catch (\Exception $e) {
//                 Log::error('Error inserting report summary data:', ['error' => $e->getMessage()]);
//             }
        
//             // Format the calculation results for response
//             $formattedCalculations = [];
//             foreach ($calculationResults as $column => $results) {
//                 foreach ($results as $type => $value) {
//                     $formattedCalculations[$column][$type] = $value;
//                 }
//             }
        
//             // Return or process $formattedCalculations as needed
//         }
        
            
    
//             DB::commit(); 
//             return response()->json([
//                 'status' => 200,
//                 'message' => 'Report created successfully',
//                 'report' => $report,
//                 'reportModule' => $reportModule,
//                 'scheduleReport' => $scheduleReport,
//                 'results' => $conditionString,
//                 //'combinedresults'=>$combinedResults,
//                 'data' => $responseData,
//                 'calculations'=>$formattedCalculations,
//                 // 'pagination' => [
//                 //     'total' => $results->total(),
//                 //     'current_page' => $results->currentPage(),
//                 //     'last_page' => $results->lastPage(),
//                 //     'per_page' => $results->perPage(),
//                 // ],
//             ], 200);

//         } catch (ValidationException $e) {
//             DB::rollBack(); 
//             Log::error('Validation Error', ['errors' => $e->errors()]);
//             return response()->json([
//                 'status' => 422,
//                 'message' => $e->errors(),
//             ], 422);
//         } catch (Exception $e) {
//             DB::rollBack(); 
//             Log::error('General Error', ['message' => $e->getMessage()]);
//             return response()->json([
//                 'status' => 500,
//                 'message' => $e->getMessage(),
//             ], 500);
//         }  
//     }

    private function performCalculation($type, $columnName)
    {
        // Parse column name to get table and column
        list($table, $column) = explode(':', $columnName);
    
        // Initialize result
        $result = null;
    
        try {
            // Perform the calculation based on type
            switch ($type) {
                case 'min':
                    $result = DB::table($table)->min($column);
                    break;
    
                case 'max':
                    $result = DB::table($table)->max($column);
                    break;
    
                case 'sum':
                    $result = DB::table($table)->sum($column);
                    break;
    
                case 'avg':
                    $result = DB::table($table)->avg($column);
                    break;
    
                // Add more cases as needed
                default:
                    Log::warning('Unknown calculation type: ' . $type);
                    break;
            }
        } catch (Exception $e) {
            Log::error('Error performing calculation:', ['error' => $e->getMessage()]);
        }
    
        return $result;
    }

    
    // protected function sendScheduledReport($scheduleReport)
    // {
    //     $fileFormat = $scheduleReport->fileformat;
    //     $reportId = $scheduleReport->reportid;
    //     $recipients = json_decode($scheduleReport->recipients, true);
    //     $fileContent = $this->export($reportId, $fileFormat);

    //     Mail::to($recipients)->send(new ReportMail($fileContent, $fileFormat));
    // }
    public function exportStoredRequests($recordId)
    {
        try {
            // Fetch the stored report details
            $report = Report::findOrFail($recordId);
            $scheduleReport = ScheduleReports::where('reportid', $recordId)->firstOrFail();

            // Fetch stored select columns
            $selectColumns = JoSelectcolumn::where('queryid', $report->id)->get();

            // Fetch stored criteria
            $criteria = RelCriteria::where('queryid', $report->id)->get();

            // Fetch stored sort columns
            $sortColumns = ReportSortCol::where('reportid', $report->id)->get();

            // Prepare data for export
            $data = [
                'report' => $report,
                'scheduleReport' => $scheduleReport,
                'selectColumns' => $selectColumns,
                'criteria' => $criteria,
                'sortColumns' => $sortColumns,
            ];

            // Export to Excel
            return Excel::download(new CustomersExport($data), 'stored_requests_' . $recordId . '.xlsx');
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
//SHOW


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


//UPDATE

public function update(Request $request, $reportId)
{
    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'reportname' => 'required|string',
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
            'select_column' => 'required|array|max:25',
            'select_column.*' => 'required|string',
            'group_by' => 'nullable|array|max:3',
            'group_by.*.field_name' => 'required|string',
            'group_by.*.sort_order' => 'required|string|in:asc,desc',
            'calculations' => 'nullable|array',
            'calculations.*.column' => 'required|string',
            'calculations.*.type' => 'required|in:sum,avg,min,max',
            'all_conditions' => 'nullable|array',
            'all_conditions.*.columnname' => 'required|string',
            'all_conditions.*.module' => 'required|string',
            'all_conditions.*.comparator' => 'required|string',
            'all_conditions.*.value' => 'required|string',
            'all_conditions.*.column_condition' => 'nullable|string|in:AND,OR',
            'any_conditions' => 'nullable|array',
            'any_conditions.*.columnname' => 'required|string',
            'any_conditions.*.module' => 'required|string',
            'any_conditions.*.comparator' => 'required|string',
            'any_conditions.*.value' => 'required|string',
            'any_conditions.*.column_condition' => 'nullable|string|in:AND,OR'
        ]);

        // Check if the report exists
        $report = Report::find($reportId);
        if (!$report) {
            return response()->json(['status' => 404, 'message' => 'Report not found'], 404);
        }

        // Update the report details
        $report->update([
            'reportname' => $validatedData['reportname'],
            'folderid' => $validatedData['folderid'],
            'description' => $validatedData['description'],
            'reporttype' => 'tabular', // Assuming 'tabular' remains unchanged
            'state' => 'CUSTOM',
            'customizable' => 1,
            'category' => 1,
            'owner' => 1,
            'sharingtype' => 'Public'
        ]);

        // Get the primary module ID and related secondary modules
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

        // Update the related secondary module names
        $secondaryModuleNames = [];
        if (!empty($validatedData['secondarymodules'])) {
            $secondaryModuleNames = DB::table('jo_tabs')
                ->whereIn('tabid', $validatedData['secondarymodules'])
                ->pluck('name')
                ->take(2) // Ensure only the first two values are taken
                ->toArray();
        }

        // Update jo_reportmodules table
        $secondaryModulesString = implode(':', $secondaryModuleNames);
        DB::table('jo_reportmodules')
            ->updateOrInsert(
                ['reportmodulesid' => $reportId],
                ['primarymodule' => $primaryModuleName, 'secondarymodules' => $secondaryModulesString]
            );

        // Clear existing select columns
        DB::table('jo_selectcolumn')->where('queryid', $report->queryid)->delete();

        // Insert new select columns
        $columnIndex = 0;
        foreach ($validatedData['select_column'] as $column) {
            $isPrimaryModuleField = DB::table('jo_fields')
                ->where('tabid', $primaryModuleId)
                ->where('fieldname', $column)
                ->exists();

            if ($isPrimaryModuleField) {
                $columnName = "{$primaryModuleName}:{$column}:{$primaryModuleName}:{$column}";
            } else {
                foreach ($relatedSecondaryModules as $secondaryModuleId) {
                    $secondaryModuleName = DB::table('jo_tabs')->where('tabid', $secondaryModuleId)->value('name');
                    $isSecondaryModuleField = DB::table('jo_fields')
                        ->where('tabid', $secondaryModuleId)
                        ->where('fieldname', $column)
                        ->exists();

                    if ($isSecondaryModuleField) {
                        $columnName = "{$secondaryModuleName}:{$column}:{$secondaryModuleName}:{$column}";
                        break;
                    }
                }
            }

            DB::table('jo_selectcolumn')->insert([
                'queryid' => $report->queryid,
                'columnindex' => $columnIndex,
                'columnname' => $columnName,
            ]);

            $columnIndex++;
        }

        // Clear existing sorting columns
        DB::table('jo_reportsortcol')->where('reportid', $reportId)->delete();

        // Insert new sorting columns
        if (isset($validatedData['group_by'])) {
            foreach ($validatedData['group_by'] as $group) {
                $fieldName = $group['field_name'];
                $sortOrder = $group['sort_order'];

                $tableName = DB::table('jo_fields')
                    ->join('jo_tabs', 'jo_fields.tabid', '=', 'jo_tabs.tabid')
                    ->where('jo_fields.fieldname', $fieldName)
                    ->where(function ($query) use ($primaryModuleId, $relatedSecondaryModules) {
                        $query->where('jo_fields.tabid', $primaryModuleId)
                              ->orWhereIn('jo_fields.tabid', $relatedSecondaryModules);
                    })
                    ->value('jo_fields.tablename');

                if (!$tableName) {
                    continue;
                }

                $columnName = "{$tableName}:{$fieldName}";

                DB::table('jo_reportsortcol')->insert([
                    'reportid' => $reportId,
                    'columnname' => "{$columnName}:{$sortOrder}",
                    'sortorder' => $sortOrder
                ]);
            }
        }

        // Clear existing calculations
        DB::table('jo_reportsummary')->where('reportsummaryid', $reportId)->delete();

        // Perform calculations and store results
        $calculationResults = [];
        if (isset($validatedData['calculations'])) {
            foreach ($validatedData['calculations'] as $index => $calc) {
                $columnName = $calc['column'];
                $calcType = $calc['type'];
                $summaryType = $index;

                $tableName = DB::table('jo_fields')
                    ->join('jo_tabs', 'jo_fields.tabid', '=', 'jo_tabs.tabid')
                    ->where('jo_fields.fieldname', $columnName)
                    ->where(function ($query) use ($primaryModuleId, $relatedSecondaryModules) {
                        $query->where('jo_fields.tabid', $primaryModuleId)
                              ->orWhereIn('jo_fields.tabid', $relatedSecondaryModules);
                    })
                    ->value('jo_fields.tablename');

                if (!$tableName) {
                    continue;
                }

                $calculationResults[] = [
                    'reportsummaryid' => $reportId,
                    'summarytype' => $summaryType,
                    'columnname' => "{$tableName}:{$columnName}:{$calcType}",
                   // 'summarytype' => $calcType
                ];
            }

            DB::table('jo_reportsummary')->insert($calculationResults);
        }

        // Clear existing conditions
        DB::table('jo_relcriteria')->where('queryid', $reportId)->delete();

        // Insert new conditions
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
      
        
        $reportRequest = $request->all();
        $results = $this->getResultsBasedOnConditions($reportRequest);
        // if ($conditions) {
        //     DB::table('jo_relcriteria')->insert($conditions);
        // }
          $storedData = [
            'report' => $report,
            'modules' => [
                'primarymodule' => $primaryModuleName,
                'secondarymodules' => $secondaryModulesString,
            ],
            'share_report' => $validatedData['share_report'] ?? null,
            'schedule_reports' => $validatedData['schedule_reports'] ?? null,
           'select_column'=>$validatedData['select_column'],
           'calculations' => $calculationResults ?? null,
           'group_by'=>$validatedData['group_by'] ?? null,
           'results' => $results 
        ];
        
       
        // return response()->json([
        //     'message' => 'Report created successfully',
        //     'data' => $storedData,
        // ], 201);
        // Return a success response
        return response()->json([
            'message' => 'Report updated successfully',
            'data'=>$storedData
        ],201);
    } catch (\Exception $e) {
        // Handle exceptions
        return response()->json(['status' => 500, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}



//DELETE

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
        DB::table('jo_reportsummary')->where('reportsummaryid',$reportId)->delete();
        DB::table('jo_selectquery')->where('queryid',$report->queryid)->delete();
        DB::table('jo_selectcolumn')->where('queryid',$report->queryid)->delete();

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
public function export(Request $request, $reportId)
{
    // Fetch the report details
    $report = DB::table('jo_reports')->where('reportid', $reportId)->first();

    if (!$report) {
        return response()->json([
            'status' => 400,
            'message' => 'Report not found',
        ], 400);
    }

    $reportName = $report->reportname;

    // Fetch columns and conditions for the report
    $columns = DB::table('jo_selectcolumn')
        ->where('queryid', $report->queryid)
        ->orderBy('columnindex')
        ->get(['columnname']);

    $conditions = DB::table('jo_relcriteria')
        ->where('queryid', $report->queryid)
        ->get(['columnname', 'comparator', 'value']);

    // Format columns with correct table names
    $formattedColumns = $columns->map(function ($column) {
        $parts = explode(':', $column->columnname);
        return [
            'table' => $this->getTableName($parts[0]), // Map module name to table name
            'field' => $parts[1],
            'label' => $parts[3] ?? $parts[1],
        ];
    });

    // Group columns by table
    $columnsByTable = $formattedColumns->groupBy('table');
    $data = [];

    // Process each table separately
    foreach ($columnsByTable as $table => $columns) {
        $query = DB::table($table);

        foreach ($columns as $column) {
            $query->addSelect($column['field']);
        }

        // Apply conditions relevant to the current table
        foreach ($conditions as $condition) {
            $columnParts = explode(':', $condition->columnname);
            if ($this->getTableName($columnParts[0]) === $table) {
                $columnField = $columnParts[1];

                switch ($condition->comparator) {
                    case 'starts_with':
                        $query->where($columnField, 'like', $condition->value . '%');
                        break;
                    case 'ends_with':
                        $query->where($columnField, 'like', '%' . $condition->value);
                        break;
                    case 'contains':
                        $query->where($columnField, 'like', '%' . $condition->value . '%');
                        break;
                    case 'equals':
                        $query->where($columnField, '=', $condition->value);
                        break;
                    case 'not_equals':
                        $query->where($columnField, '!=', $condition->value);
                        break;
                    default:
                        Log::warning('Unsupported comparator: ' . $condition->comparator);
                        break;
                }
            }
        }

        // Execute the query and process results
        $tableData = $query->get();

        foreach ($tableData as $row) {
            $formattedRow = [];
            foreach ($columns as $column) {
                $formattedRow[$column['label']] = $row->{$column['field']} ?? '';
            }

            // Filter out empty rows
            if (array_filter($formattedRow)) {
                $data[] = $formattedRow;
            }
        }
    }

    // Fetch report summary calculations
    $summaryCalculations = DB::table('jo_reportsummary')
        ->where('reportsummaryid', $reportId)
        ->get();

    // Determine the summary type map dynamically
    $summaryTypeMap = $this->getSummaryTypeMap($summaryCalculations);

    // Initialize calculations array
    $calculations = [];

    foreach ($summaryCalculations as $summary) {
        $parts = explode(':', $summary->columnname);
        $table = $this->getTableName($parts[0]); // Corrected to match the table name in columnname
        $column = $parts[1]; // Corrected to match the column name in columnname
        $summaryTypeIndex = $summary->summarytype;
        $summaryType = $summaryTypeMap[$summaryTypeIndex] ?? null;

        if ($summaryType === null) {
            Log::warning('Unsupported summary type index: ' . $summaryTypeIndex);
            continue;
        }

        // Perform the calculation
        $query = DB::table($table);
        if ($summaryType === 'sum') {
            $calculationResult = $query->sum($column);
        } elseif ($summaryType === 'avg') {
            $calculationResult = $query->avg($column);
        } elseif ($summaryType === 'min') {
            $calculationResult = $query->min($column);
        } elseif ($summaryType === 'max') {
            $calculationResult = $query->max($column);
        } else {
            Log::warning('Unsupported summary type: ' . $summaryType);
            continue;
        }

        // Store the result in the calculations array
        if (!isset($calculations[$column])) {
            $calculations[$column] = [
                'sum' => null,
                'avg' => null,
                'min' => null,
                'max' => null,
            ];
        }

        $calculations[$column][$summaryType] = $calculationResult;
    }

    Log::info('Export Data:', $data);
    Log::info('Calculations:', $calculations);

    $fileFormat = $request->input('fileformat');
    $filename = $reportName ?: 'report';
    $totalRecords = count($data);

    switch ($fileFormat) {
        case 'excel':
            return Excel::download(new ReportExport($data, $formattedColumns->toArray()), $filename . '.xlsx');

        case 'csv':
            return response()->streamDownload(function () use ($data, $formattedColumns) {
                $handle = fopen('php://output', 'w');

                // Convert $formattedColumns to array
                $headers = array_column($formattedColumns->toArray(), 'label');
                fputcsv($handle, $headers);

                // Write each row
                foreach ($data as $row) {
                    $rowArray = array_map(function($field) use ($row) {
                        return $row[$field] ?? '';
                    }, array_column($formattedColumns->toArray(), 'field'));
                    fputcsv($handle, $rowArray);
                }

                fclose($handle);
            }, $filename . '.csv', [
                'Content-Type' => 'text/csv',
            ]);

        case 'pdf':
            $pdf = PDF::loadView('report.pdf', [
                'exportData' => $data,
                'formattedColumns' => $formattedColumns,
                'reportName' => $reportName,
                'totalRecords' => $totalRecords,
                'calculations' => $calculations, // Pass calculations to the view
            ]);
            return $pdf->stream($filename . '.pdf');

        default:
            return response()->json([
                'status' => 400,
                'message' => 'Invalid file format',
            ], 400);
    }
}

// Helper method to map module names to table names
private function getTableName($moduleName)
{
    $tableMap = [
        'Invoices' => 'jo_invoices',
        'Customers'=>'jo_customers',
        'Products'=>'jo_products',
        'Payments'=>'jo_payments',
        'Expenses'=>'jo_expenses',
    
        
    ];

    return $tableMap[$moduleName] ?? $moduleName; // Default to moduleName if not mapped
}

// Helper method to get summary type map dynamically
// private function getSummaryTypeMap($summaryCalculations)
// {
//     // Map summary types based on your application needs
//     return [
//         '1' => 'sum',
//         '2' => 'avg',
//         '3' => 'min',
//         '4' => 'max',
//         // Add other mappings here
//     ];
// }

protected function generateFileContent($format, $data, $responseData, $reportName, $calculations)
{
    $totalRecords = count($data);

    // Ensure $responseData is an array of arrays
    $responseDataArray = is_array($responseData) ? $responseData : (array) $responseData;
    
    Log::info('Response Data:', ['responseData' => $responseDataArray]);
    Log::info('Data:', ['data' => $data]);
    Log::info('Total Records:', ['count' => $totalRecords]);

    if ($totalRecords === 0) {
        Log::warning('No records found in data.');
    }

    switch ($format) {
        case 'csv':
            $output = fopen('php://memory', 'r+');
    
            if (!empty($responseDataArray) && isset($responseDataArray[0])) {
                $headers = array_keys($responseDataArray[0]);
                fputcsv($output, $headers);
                Log::info('CSV Headers:', ['headers' => $headers]);
    
                foreach ($responseDataArray as $row) {
                    $rowArray = [];
                    foreach ($headers as $header) {
                        $rowArray[] = $row[$header] ?? 'N/A';
                    }
    
                    Log::info('Row Array:', ['row' => $rowArray]);
                    fputcsv($output, $rowArray);
                }
            } else {
                Log::warning('Response data is empty or invalid.');
            }
    
            rewind($output);
            $csvContent = stream_get_contents($output);
            fclose($output);
    
            if (empty($csvContent)) {
                Log::warning('CSV content is empty.');
            } else {
                Log::info('CSV content length:', ['length' => strlen($csvContent)]);
            }
    
            return $csvContent;

            case 'xlsx':
                // Generate Excel file content and return it for download
                $excelContent = Excel::raw(new class($responseDataArray) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                    private $data;
                    private $headers;
            
                    public function __construct($data)
                    {
                        $this->data = $data;
                        // Extract headers from the first row of data if available
                        $this->headers = !empty($data) && isset($data[0]) ? array_keys($data[0]) : [];
                    }
            
                    // Return only the data, without manually adding headers
                    public function array(): array
                    {
                        return $this->data;
                    }
            
                    // Specify the headings for the Excel sheet
                    public function headings(): array
                    {
                        return $this->headers;
                    }
                }, \Maatwebsite\Excel\Excel::XLSX);
            
                if (empty($excelContent)) {
                    Log::warning('Excel content is empty.');
                } else {
                    Log::info('Excel content length:', ['length' => strlen($excelContent)]);
                }
            
                return $excelContent;
            

        // case 'pdf':
        //     $pdf = PDF::loadView('report.pdf', [
        //         'exportData' => $data,
        //         'responseData' => $responseDataArray,
        //         'reportName' => $reportName,
        //         'totalRecords' => $totalRecords,
        //         'calculations' => $calculations,
        //     ]);
        //     $pdfContent = $pdf->output();

        //     if (empty($pdfContent)) {
        //         Log::warning('PDF content is empty.');
        //     } else {
        //         Log::info('PDF content length:', ['length' => strlen($pdfContent)]);
        //     }

        //     return $pdfContent;

        default:
            Log::warning('Invalid format specified.');
            return '';
    }
}



// Define a function to determine the correct mapping based on existing data
protected function getSummaryTypeMap($summaryCalculations)
{
    $summaryTypeMap = [];

    foreach ($summaryCalculations as $summary) {
        $summaryTypeIndex = $summary->summarytype;
        $columnParts = explode(':', $summary->columnname);
        $operation = $columnParts[2] ?? null; // Assuming the operation is in the 3rd part

        // Map based on detected operation type
        if ($operation) {
            $summaryTypeMap[$summaryTypeIndex] = $operation;
        }
    }

    return $summaryTypeMap;
}

// public function sendReport(Request $request)
//     {
//         $fileContent = $this->generateFileContent($request->input('fileformat'));
//         $fileFormat = $request->input('fileformat');
//         $recipientEmail = $request->input('email');

//         Mail::to($recipientEmail)->send(new ReportMail($fileContent, $fileFormat));

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Report sent successfully',
//         ]);
//     }

   

// private function getTableNameByPrimaryModule($primaryModule)
// {
//     // Debugging statement
//     Log::info("Received primaryModule value: " . $primaryModule);

//     // Map primarymodule values to table names
//     switch ($primaryModule) {
//         case 1:
//             return 'jo_customers';
//         // Add other cases as needed
//         default:
//             throw new \Exception('Invalid primarymodule value: ' . $primaryModule);
//     }
// }


    //     private function generatePdfReport(Collection $data, $filename)
    // {
    //     // Load the view and pass the data
    //     $pdf = Pdf::loadView('report', ['data' => $data]);

    //     // Define the path where the PDF will be saved
    //     $path = storage_path('app/public/reports/' . $filename);

    //     // Save the PDF to the specified path
    //     $pdf->save($path);

    //     // Return a response indicating the file was saved
    //     return response()->json(['status' => 'success', 'message' => 'PDF saved successfully', 'path' => $path]);
    // }
    protected function getDataById($id)
    {
        // Replace with your actual data fetching logic
        // Example: return YourModel::find($id);
        return Report::find($id);
    }


}
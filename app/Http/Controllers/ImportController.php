<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Field;
use Illuminate\Support\Facades\Session;


class ImportController extends Controller
{
    public function showForm($module)
    {
        if (in_array($module, ['customers', 'products','equipments','clients','leads','employees','estimates','invoices','expenses','incomes','organizations','payments','projects','recuring-expenses','employment-types','tags','tasks','teamtasks','teams','proposals','proposal-templates','vendors','departments','documents'])) {
            return view('import.import', ['module' => $module]);
        }
        
        abort(404); // Handle invalid module gracefully
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
            'encoding' => 'required|string',
            'delimiter' => 'required|string',
            'hasHeader' => 'nullable|boolean',
        ]);

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('csv_files', $filename);

            $request->session()->put('importData', [
                'file_path' => 'csv_files/' . $filename,
                'hasHeader' => $request->has('hasHeader'),
                'encoding' => $validated['encoding'],
                'delimiter' => $validated['delimiter'],
            ]);

            return redirect()->route($request->has('hasHeader') ? 'import.step2' : 'import.step2', ['module' => $request->module]);
        }

        return back()->withInput()->withErrors(['csv_file' => 'Please upload a CSV file.']);
    }

    public function showStep2Form($module)
    {
        $tabid = $this->getTabIdForModule($module);
        $fields = Field::where('tabid', $tabid)->get();

        return view('import.import_step2', [
            'module' => $module,
            'fields' => $fields,
        ]);
    }
    public function processStep2(Request $request, $module)
    {
        Log::info('Processing Step 2', ['module' => $module, 'request_data' => $request->all()]);
        
        try {
            $validated = $request->validate([
                'duplicateHandling' => 'nullable|in:skip,overwrite,merge',
                'matchingFields' => 'nullable|array|min:1',
                'matchingFields.*' => 'nullable|string',
                'default_values.*' => 'nullable|string',
            ]);
            
            Log::info('Validation Passed', ['validated_data' => $validated]);
        
            $request->session()->put('step2Data', [
                'duplicateHandling' => $validated['duplicateHandling'],
                //'matchingFields' => $validated['matchingFields'],
                //'default_values' => $validated['default_values'], // Save default values here
            ]);
            
            // Log session data before redirecting
            Log::info('Step 2 Data in Session:', session('step2Data'));
    
            return redirect()->route('import.fieldMapping', ['module' => $module]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error:', $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
    }
    
    public function showFieldMappingForm(Request $request, $module)
    {
        // Retrieve import data from the session
        $importData = session('importData');
        
        // Check if import data exists
        if (!$importData) {
            return redirect()->route('import.summary', ['module' => $module])
                             ->withErrors(['message' => 'No import data found.']);
        }
        
        // Get CSV headers from the file
        $csvHeaders = $this->getCSVHeaders($importData['file_path'], $importData['encoding'], $importData['delimiter']);
        
        // Get the first row values from the CSV
        $firstRow = $this->parseCSV(storage_path('app/' . $importData['file_path']), $importData['encoding'], $importData['delimiter'], $importData['hasHeader'])[0] ?? [];
        
        // If 'hasHeader' is false, generate headers based on column indexes
        if (!$importData['hasHeader']) {
            $csvHeaders = array_keys($firstRow);
        }
        
        // Retrieve CRM fields based on the module
        $fields = Field::where('tabid', $this->getTabIdForModule($module))->get();
        
        // Default values for CRM fields
        $defaultValues = $request->session()->get('default_values', []);
    
        // Pass data to the view
        return view('import.field_mapping', [
            'module' => $module,
            'csvHeaders' => $csvHeaders,
            'fields' => $fields,
            'firstRow' => $firstRow,
            'hasHeader' => $importData['hasHeader'],
            'defaultValues' => $defaultValues, // Add default values here
        ]);
    }
    public function processImport(Request $request, $module)
{
    Log::info('Reached processImport method');
    
    try {
        // Validate the incoming request
        $validated = $request->validate([
            'crm_fields.*' => 'nullable|string',
            'default_values.*' => 'nullable|string',
            //'save_as_custom_mapping' => 'nullable|boolean',
           // 'custom_mapping_name' => 'nullable|string|max:255',
        ]);

        // Retrieve import data from the session
        $importData = $request->session()->get('importData');
        if (!$importData) {
            Log::error('No import data found in session.');
            return redirect()->route('import.form', ['module' => $module])->withErrors(['message' => 'No import data found.']);
        }

        $csvFilePath = storage_path('app/' . $importData['file_path']);
        Log::info('CSV File Path:', [$csvFilePath]);

        // Get CSV headers
        $headers = $this->getCSVHeaders($importData['file_path'], $importData['encoding'], $importData['delimiter']);
        Log::info('CSV Headers:', $headers);

        // Parse the CSV data
        $data = $this->parseCSV($csvFilePath, $importData['encoding'], $importData['delimiter'], $importData['hasHeader']);
        Log::info('Parsed CSV Data:', $data);

        // Get CRM fields and default values from the request
        $crmFields = $validated['crm_fields'];
        $defaultValues = $validated['default_values'];
        Log::info('CRM Fields:', $crmFields);
        Log::info('Default Values:', $defaultValues);

        // Check if the CSV data is empty
        if (empty($data)) {
            Log::error('Parsed CSV data is empty.');
            return redirect()->back()->withErrors(['csv_file' => 'Parsed CSV data is empty.'])->withInput();
        }

        // Check if CRM fields are empty
        if (empty(array_filter($crmFields))) {
            Log::error('CRM Fields are empty or not properly set.');
            return redirect()->back()->withErrors(['csv_file' => 'CRM fields are empty or not properly set.'])->withInput();
        }

        // Map CSV data to CRM fields
        $mappedData = $this->mapCSVToCRMFields($data, $headers, $crmFields, $defaultValues, $importData['hasHeader']);
        Log::info('Mapped Data:', $mappedData);

        // Store the mapped data in the database
        $summary = $this->storeDataInDatabase($mappedData, $module);

        // Store summary in the session and redirect to the summary page
        session(['summary' => $summary]);
        return redirect()->route('import.summary', ['module' => $module]);
    } catch (\Exception $e) {
        Log::error('Error Processing CSV:', ['error' => $e->getMessage()]);
        return redirect()->back()->withErrors(['csv_file' => 'Error processing CSV file.'])->withInput();
    }
}
  public function showSummary($module)
    {
        $summary = session('summary', [
            'total_records' => 0,
            'created' => 0,
            'overwritten' => 0,
            'skipped' => 0,
            'merged' => 0,
            'failed' => 0,
        ]);

        return view('import.summary', ['module' => $module, 'summary' => $summary]);
    }
    private function storeDataInDatabase($mappedData, $module)
    {
        $tableName = $this->getTableNameForModule($module);
        $summary = [
            'total_records' => count($mappedData),
            'created' => 0,
            'overwritten' => 0,
            'skipped' => 0,
            'merged' => 0,
            'failed' => 0,
        ];
    
        $duplicateHandling = session('step2Data.duplicateHandling', 'skip');
    
        foreach ($mappedData as $row) {
            $filteredRow = array_filter($row, fn($value) => !empty($value));
    
            try {
                $uniqueIdentifier = $filteredRow['primary_email'] ?? null;
    
                if ($uniqueIdentifier) {
                    $existingRecord = DB::table($tableName)->where('primary_email', $uniqueIdentifier)->first();
    
                    if ($existingRecord) {
                        if ($duplicateHandling === 'overwrite') {
                            DB::table($tableName)->where('primary_email', $uniqueIdentifier)->update($filteredRow);
                            $summary['overwritten']++;
                        } elseif ($duplicateHandling === 'merge') {
                            $mergedData = array_merge((array)$existingRecord, $filteredRow);
                            DB::table($tableName)->where('primary_email', $uniqueIdentifier)->update($mergedData);
                            $summary['merged']++;
                        } else {
                            $summary['skipped']++;
                        }
                    } else {
                        DB::table($tableName)->insert($filteredRow);
                        $summary['created']++;
                    }
                } else {
                    DB::table($tableName)->insert($filteredRow);
                    $summary['created']++;
                }
            } catch (\Exception $e) {
                $summary['failed']++;
            }
        }
    
        return $summary;
    }
        private function mapCSVToCRMFields($data, $headers, $crmFields, $defaultValues, $hasHeader)
    {
        $mappedData = [];
    
        foreach ($data as $rowIndex => $row) {
            $mappedRow = [];
    
            foreach ($crmFields as $index => $field) {
                if ($field === null) {
                    // Skip fields that are null in crmFields
                    continue;
                }
    
                // Determine the CSV header or index based on whether the CSV has headers
                $header = $hasHeader ? ($headers[$index] ?? null) : $index;
                $value = $hasHeader && $header !== null ? ($row[$header] ?? null) : ($row[$index] ?? null);
    
                // Fetch the default value based on the CRM field
                $defaultValue = $defaultValues[$index] ?? null;
    
                // Map the value or use default if the value is empty or null
                $mappedValue = isset($value) && $value !== '' ? $value : $defaultValue;
    
                // Log detailed information for debugging
                Log::info('Mapping Field:', [
                    'field' => $field, 
                    'header' => $header, 
                    'value' => $value, 
                    'default' => $defaultValue, 
                    'mapped' => $mappedValue
                ]);
                
                $mappedRow[$field] = $mappedValue;
            }
    
            $mappedData[] = $mappedRow;
        }
    
        return $mappedData;
    }
    
    public function cancelImport($module)
    {
        // Check if 'importData' and 'step2Data' exist before attempting to forget them
        if (Session::has('importData')) {
            Session::forget('importData');
        }
        
        if (Session::has('step2Data')) {
            Session::forget('step2Data');
        }
    
        return redirect()->route('import.form', ['module' => $module])
                         ->with('status', 'Import process cancelled.');
    }

    private function getTabIdForModule($module)
    {
        switch ($module) {
            case 'customers':
                return 1;
            case 'teamtasks':
                return 2;
            case 'tasks':
                return 3;
            case 'invoices':
                return 7;
            case 'payments':
                return 8;
            case 'pipelines':
                return 9;
            case 'estimates':
                return 10;
            case 'incomes':
                return 11;
            case 'proposals':
                return 12;   
            case 'equipments':
                return 13;
            case 'products':
                return 14;
            case 'expenses':
                return 15;
            case 'documents':
                return 16;
             case 'teams':
                return 17;
            case 'recuring-expenses':
                return 18;
            case 'proposal-templates':
                return 19;
            case 'organizations':
                return 20;
            case 'clients':
                return 21;
            case 'departments':
                return 22;
            case 'employment-types':
                return 23;
            case 'tags':
                return 24;
            case 'vendors':
                return 25;
            case 'leads';
                return 26;
            case 'projects':
                return 27;
            default:
                abort(404);
        }
    }

    private function getCSVHeaders($filePath, $encoding, $delimiter)
{
    $delimiter = $this->normalizeDelimiter($delimiter);
    $fileContents = Storage::get($filePath);
    $fileContents = preg_replace('/^\xEF\xBB\xBF/', '', $fileContents); // Remove BOM if present
    $lines = explode(PHP_EOL, $fileContents);
    $lines = array_filter($lines);

    if (empty($lines)) {
        Log::warning('CSV file is empty or has no valid lines.');
        return [];
    }

    return str_getcsv($lines[0], $delimiter);
}

private function parseCSV($filePath, $encoding, $delimiter, $hasHeader)
{
    $delimiter = $this->normalizeDelimiter($delimiter);

    $data = [];
    $file = fopen($filePath, 'r');

    if ($encoding !== 'UTF-8') {
        $fileContent = stream_get_contents($file);
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', $encoding);
        fclose($file);
        $file = fopen('php://memory', 'r+');
        fwrite($file, $fileContent);
        rewind($file);
    }

    // Get the header row if it exists
    $headerRow = $hasHeader ? fgetcsv($file, 1000, $delimiter) : null;

    while (($row = fgetcsv($file, 1000, $delimiter)) !== false) {
        if (empty(array_filter($row))) {
            // Skip empty rows
            continue;
        }

        // Handle cases based on whether headers are present
        if ($hasHeader && $headerRow) {
            $combinedRow = array_combine($headerRow, $row);
            if ($combinedRow !== false) {
                $data[] = $combinedRow;
            } else {
                Log::warning('Row does not match header:', $row);
            }
        } else {
            // If no headers, just store rows as-is
            $data[] = $row;
        }
    }

    fclose($file);

    // Log parsed data for debugging
    Log::info('Parsed CSV Data:', $data);

    return $data;
}


    private function getTableNameForModule($module)
    {
        $modules = [
            'customers' => 'jo_customers',
            'products' => 'jo_products',
            'equipments'=>'jo_equipments',
            'clients'=>'jo_clients',
            'leads'=>'jo_leads',
            'estimates'=>'jo_estimates',
            'invoices'=>'jo_invoices',
            'expenses'=>'jo_expenses',
            'incomes'=>'jo_incomes',
            'organizations'=>'jo_organizations',
            'payments'=>'jo_payments',
            'pipelines'=>'jo_pipelines',
            'projects'=>'jo_projects',
            'recuring-expenses'=>'jo_recuring_expenses',
            'employment-types'=>'jo_employment_types',
            'tags'=>'jo_tags',
            'tasks'=>'jo_tasks',
            'teamtasks'=>'jo_teamtasks',
            'teams'=>'jo_teams',
            'proposals'=>'jo_proposals',
            'proposal-templates'=>'jo_proposal_templates',
            'vendors'=>'jo_vendors',
            'departments'=>'jo_departments',
            'documents'=>'jo_documents'

        ];

        return $modules[$module] ?? null;
    }

    private function normalizeDelimiter($delimiter)
    {
        switch ($delimiter) {
            case '\t':
                return "\t";
            case ',':
            case ';':
                return $delimiter;
            default:
                return ',';
        }
    }

public function showImported($module)
{
    // Log entry into the method
    Log::info('Showing imported data for module:', ['module' => $module]);

    // Get the table name based on the module
    $tableName = $this->getTableNameForModule($module);

    // Define the fields based on the module
    $moduleFields = [
        'customers' => ['id', 'name', 'primary_email', 'primary_phone','city','country'],
        'products' => ['id', 'name', 'code', 'product_type', 'product_category', 'tags'],
        'equipments' => ['id', 'name', 'type', 'manufactured_year', 'sn', 'max_share_period', 'initial_cost'],
        'clients' => ['id', 'name', 'primary_email', 'primary_phone'],
        'leads' => ['id', 'name', 'primary_email', 'primary_phone'],
        'estimates' => ['id', 'estimatenumber', 'contacts', 'currency', 'status'],
        'invoices' => ['id', 'invoicenumber', 'contacts', 'currency', 'status'],
        'expenses' => ['id', 'contacts', 'amount', 'tax_deductible', 'projects'],
        'incomes' => ['id', 'employees_that_generate_income', 'contacts', 'pick_date'],
        'organizations' => ['id', 'organization_name', 'currency', 'official_name'],
        'payments' => ['id', 'invoice_number', 'contacts', 'projects', 'payment_date', 'payment_method', 'currency', 'tags', 'amount', 'note'],
        'pipelines' => ['name', 'description', 'is_active', 'stages'],
        'projects' => ['name', 'code', 'project_url', 'owner'],
        'recuring-expenses' => ['category_name', 'split_expense', 'value', 'currency'],
        'employment-types' => ['employment_type_name', 'tags'],
        'tags' => ['tags_name', 'tag_color'],
        'tasks' => ['tasksnumber', 'projects', 'status', 'choose', 'addorremoveemployee', 'title'],
        'teamtasks' => ['tasknumber', 'projects', 'title'],
        'teams' => ['team_name', 'add_or_remove_projects', 'add_or_remove_managers', 'add_or_remove_members'],
        'proposals' => ['author', 'template', 'job_post_url', 'proposal_content'],
        'proposal-templates' => ['select_employee', 'name', 'content'],
        'vendors' => ['vendor_name', 'phone', 'email'],
        'departments' => ['departments', 'add_or_remove_employee', 'tags'],
        'documents' => ['document_name', 'document_url']
    ];

    if (!array_key_exists($module, $moduleFields)) {
        abort(404, 'Invalid module.');
    }

    $fields = $moduleFields[$module];

    // Fetch the imported data
    $records = DB::table($tableName)->select($fields)->get();

    // Check if records are empty and handle accordingly
    if ($records->isEmpty()) {
        Log::info('No records found for module:', ['module' => $module]);
        return view('import.show_imported', [
            'module' => $module,
            'records' => [],
            'fields' => $fields
        ]);/*->withErrors(['message' => 'No data found for the selected module.']);*/
    }

    // Pass data to the view
    return view('import.show_imported', [
        'module' => $module,
        'records' => $records,
        'fields' => $fields
    ]);
}


}
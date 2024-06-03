<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Block;
use App\Models\Field;
use App\Models\Tab;
use App\Models\RelatedList;
use App\Models\ParentTab;
use App\Models\ParentTabRel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class ModuleStudioController extends Controller
{
    public function step1()
    {
        try {
            $tabs = Tab::all();
            $tabName = $tabs->pluck('name')->toArray();
            return view('modulestudio.step1');
        } catch (\Exception $e) {
            error_log($e->getMessage(), 3, '/var/www/html/module-app/error.log');
            return response()->json(['error' => 'An error occurred while loading step 1.'], 500);
        }
    }
    public function checkModuleName(Request $request)
   {
    $moduleName = $request->input('module_name');
    $exists = Tab::where('name', $moduleName)->exists();
    return response()->json(['exists' => $exists]);
   }
    
    public function step1Post(Request $request)
    {
        try {
            $moduleName = $request->input('module_name');
            if (Tab::where('name', $moduleName)->exists()) {
                return redirect()->back()->withErrors(['module_name' => 'The module name already exists.']);
            }
            error_log(print_r(["1"], true), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
            $request->session()->put('step1', $request->all());
            return redirect()->route('form.step2');
        } catch (\Exception $e) {
            error_log($e->getMessage(), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
            return response()->json(['error' => 'An error occurred while processing step 1.'], 500);
        }
    }
    
    public function step2()
    {
        try {
            $step1 = session('step1');
            $moduleName=$step1['module_name'];
            return view('modulestudio.step2',compact('moduleName'));
        } catch (\Exception $e) {
            error_log($e->getMessage(), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
            return response()->json(['error' => 'An error occurred while loading step 2.'], 500);
        }
    }
    
    public function step2Post(Request $request)
    {
        try {
            // error_log(print_r(["2"], true), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
            $step1 = $request->session()->get('step1');
            $moduleName = $step1['module_name'];
            $tableName = 'jo_' . strtolower($moduleName) . "s";
            $migrationName = 'create_' . str_replace(' ', '_', $tableName) . '_table';
            Artisan::call('make:migration', ['name' => $migrationName]);    
            $migrationTimestamp = now()->format('Y_m_d_His');
            $migrationPath = database_path('migrations') . '/' . $migrationTimestamp . "_${migrationName}.php";
            $migrationContent = File::get($migrationPath);
            $fields = json_decode($request->input('dragDropModalData'), true);
            $fieldsSchema = '';
            foreach ($fields as $item) {
               
                $columnName = $item['columnName'];
                $columnType = $item['columnType'];
                $mandatory = $item['mandatory'];
                if ($mandatory)
                    $fieldsSchema .= "\$table->$columnType('$columnName');\n";
                else
                    $fieldsSchema .= "\$table->$columnType('$columnName')->nullable();\n";
            }
            $moduleid = strtolower($moduleName) . 'id';
    
            $migrationContent = str_replace(
                '$table->id();',
                "\$table->increments('{$moduleid}');\n$fieldsSchema",
                $migrationContent
            );
    
            File::put($migrationPath, $migrationContent);
    
            Artisan::call('migrate', [
                '--path' => 'database/migrations/' . $migrationTimestamp . "_${migrationName}.php"
            ]);
    
            $request->session()->put('step2', $request->all());
    
            return redirect()->route('form.step3');
        } catch (\Exception $e) {
            error_log($e->getMessage(), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
            return response()->json(['error' => 'An error occurred while processing step 2.'], 500);
        }
    }
    
    public function step3()
    {
        try {
            $tabs = Tab::all();
            $tabName = $tabs->pluck('name')->toArray();
            return view('modulestudio.step3', ['modules' => $tabName]);
        } catch (\Exception $e) {
            error_log($e->getMessage(), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
            return response()->json(['error' => 'An error occurred while loading step 3.'], 500);
        }
    }
    
    public function step3Post(Request $request)
    {
        try {
            $request->session()->put('step3', $request->all());
            return redirect()->route('form.step4');
        } catch (\Exception $e) {
            error_log($e->getMessage(), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
            return response()->json(['error' => 'An error occurred while processing step 3.'], 500);
        }
    }
    
    public function step4(Request $request)
    {
        try {
            $step1 = $request->session()->get('step1');
            $step2 = $request->session()->get('step2');
            $step3 = $request->session()->get('step3');
    
            if (isset($step2['dragDropModalData'])) {
                $fields = json_decode($step2['dragDropModalData'], true);
            } else {
                $fields = [];
            }
    
            $columnName = [];
            foreach ($fields as $item) {
                $columnName[] = $item['columnName'];
            }
    
            if (isset($columnName)) {
                return view('modulestudio.step4', ['modules' => $columnName]);
            } else {
                return view('modulestudio.step4');
            }
        } catch (\Exception $e) {
            error_log($e->getMessage(), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
            return response()->json(['error' => 'An error occurred while loading step 4.'], 500);
        }
    }
    
    public function step4Post(Request $request)
    {
        try {
            error_log(print_r(["4"], true), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
    
            $step1 = $request->session()->get('step1');
            $step2 = $request->session()->get('step2');
            $step3 = $request->session()->get('step3');
            $blockModalData = json_decode($step2['blockModalData'], true);
            $fields = json_decode($step2['dragDropModalData'], true);
    
            $tab = Tab::where('name', $step3['label'])->first();
            $tabId = $tab->tabid;

            $lastTab = Tab::latest('tabid')->first();
            $lastTabId = $lastTab ? $lastTab->tabid : 0;

            try {
            $tab = new Tab();
            $tab->tabid = $lastTabId +1;
            $tab->name = $step1['module_name'];
            $tab->presence = 0;
            $tab->tablabel = $step1['module_name'];
            $tab->tabsequence = -1;
            $tab->modifiedby = null;
            $tab->modifiedtime = now();
            $tab->customized = 0;
            $tab->ownedby = 0;
            $tab->isentitytype = 1;
            $tab->trial = 0;
            $tab->version = $step1['version'] ?? null;
            $tab->parent = $step1['menu'] ?? null;
            $tab->save();
        } catch (\PDOException $e) {
            if ($e->getCode() == '23505') { // Unique violation error code for PostgreSQL
            error_log(print_r(["step4" => $e], true), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
                
                return response()->json(['error' => 'The name ' . $step1['module_name'] . ' already exists.'], 409); // Conflict HTTP status code
            }
            throw $e; // Re-throw if it's not a unique constraint violation
        }

        $lastTab = Tab::latest('tabid')->first();
        $lastTabId = $lastTab ? $lastTab->tabid : 0;
        $lastBlock = Block::latest('blockid')->first();
        $lastBlockId = $lastBlock ? $lastBlock->blockid : 0;

        foreach ($blockModalData as $data) {
            $blockData = new Block();
            $blockData->blockid = $lastBlockId+1;
            $blockData->tabid = $lastTabId;
            $blockData->blocklabel = $data['editedLabel'];
            $blockData->sequence = 1;
            $blockData->show_title = $data['showTitle'] ? 1 : 0;
            $blockData->visible = $data['visible'] ? 1 : 0;
            $blockData->create_view = $data['createView'] ? 1 : 0;
            $blockData->edit_view = $data['editView'] ? 1 : 0;
            $blockData->detail_view = $data['detailView'] ? 1 : 0;
            $blockData->display_status = $data['displayStatus'] ? 1 : 0;
            $blockData->iscustom = $data['isCustom'] ? 1 : 0;
            $blockData->save();
        }
    
            error_log(print_r(["blocktable" => $blockData], true), 3, '/var/www/html/module-app/error.log');
    
            $lastBlock = Block::latest('blockid')->first();
            $lastBlockId = $lastBlock ? $lastBlock->blockid : 0;

            $lastField = Field::latest('fieldid')->first();
            $lastFieldId = $lastField ? $lastField->fieldid : 0;
    
            foreach ($fields as $data) {
                $fieldData = new Field();
                $fieldData->fieldid = $lastFieldId+1;
                $fieldData->tabid = $lastTabId;
                $fieldData->columnname = $data['columnName'] ?? '';
                $fieldData->tablename = $data['tableNameModal'] ?? null;
                $fieldData->generatedtype = $data['generatedType'] ?? 0;
                $fieldData->uitype = $data['uiType'] ?? '';
                $fieldData->fieldname = $data['fieldName'] ?? '';
                $fieldData->fieldlabel = $data['labelName'] ?? '';
                $fieldData->readonly = $data['readonly'] ?? 0;
                $fieldData->presence = $data['presence'] ?? 1;
                $fieldData->defaultvalue = $data['defaultValue'] ?? null;
                $fieldData->maximumlength = $data['maximumLength'] ?? null;
                $fieldData->sequence = $data['sequence'] ?? null;
                $fieldData->block = $lastBlockId;
                $fieldData->displaytype = $data['displayType'] ?? null;
                $fieldData->typeofdata = $data['typeOfData'] ?? null;
                $fieldData->quickcreate = $data['quickCreate'] ?? 1;
                $fieldData->quickcreatesequence = $data['quickCreateSequence'] ?? null;
                $fieldData->info_type = $data['infoType'] ?? null;
                $fieldData->masseditable = $data['massEditable'] ?? 1;
                $fieldData->helpinfo = $data['helpInfo'] ?? null;
                $fieldData->summaryfield = $data['summaryField'] ?? 0;
                $fieldData->headerfield = $data['headerField'] ?? 0;
                $fieldData->save();
    
                // error_log(print_r(["fieldtable" => $fieldData], true), 3, '/var/www/html/module-app/error.log');
            }
    
            $relatedData = new RelatedList();
            $relatedData->tabid = $lastTabId;
            $relatedData->related_tabid = $tabId;
            $relatedData->name = $step3['method'];
            $relatedData->sequence = 1;
            $relatedData->label = $step3['label'];
            $relatedData->presence = 0;
            $relatedData->actions = 'ADD';
            $relatedData->relationfieldid = 0;
            $relatedData->source = $step3['source'] ?? null;
            $relatedData->relationtype = $step3['relation_type'] ?? null;
            $relatedData->save();
    
            // error_log(print_r(["relatedtable" => $relatedData], true), 3, '/var/www/html/module-app/error.log');
    
            if (isset($step1['menu'])) {
                $parentTab = ParentTab::where('parenttab_label', $step1['menu'])->first();
                $parentTabId = $parentTab->parenttabid;
    
                $parentTabRelData = new ParentTabRel();
                $parentTabRelData->parenttabid = $parentTabId;
                $parentTabRelData->tabid = $lastTabId;
                $parentTabRelData->sequence = 2;
                $parentTabRelData->save();
            }
    
            error_log(print_r(["parentreltable" => $parentTabRelData], true), 3, '/var/www/html/module-app/error.log');
    
            $moduleName = $step1['module_name'];
            $tableName = strtolower($moduleName) . "s";
    
            $artisan = Artisan::call('make:model', ['name' => $moduleName]);
            // error_log(print_r(["artisan" => $artisan], true), 3, '/var/www/html/module-app/error.log');
    
            $this->generateModel($moduleName, $tableName);
            $controllerName = $moduleName . 'Controller';
            Artisan::call('make:controller', ['name' => $controllerName]);
            $this->generateRoutes($moduleName);
            $this->generateCrud($controllerName, $moduleName, $fields);
    
            if (isset($step1['menu'])) {
                $jsondata = [
                    "title" => $step1['menu'],
                    "name" => strtolower($step1['menu']),
                    "options" => [
                        [
                            "name" => $moduleName,
                            "unixname" => strtolower($moduleName),
                        ],
                    ]
                ];
            } else {
                $jsondata = null;
            }
    
            $jsondata = json_encode($jsondata);
            return response()->json($jsondata);
        } catch (\Exception $e) {
            error_log($e->getMessage(), 3, '/var/www/html/git_commit/joforce_ai_erp/error.log');
            return response()->json(['error' => 'An error occurred while processing step 4.'], 500);
        }
    }
    
        public function success()
        {
            return view('modulestudio::success');
        }

    private function generateModel($moduleName, $tableName)
{
    // Generate the model
    Artisan::call('make:model', ['name' => $moduleName]);

    // Path to the generated model
    $modelPath = app_path("Models/{$moduleName}.php");
    
    // Create the directory if it doesn't exist
    if (!File::exists(dirname($modelPath))) {
        File::makeDirectory(dirname($modelPath), 0755, true);
    }
    $modelContent = File::get($modelPath);
    // Model content
    $modelContent = <<<EOD
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class $moduleName extends Model
{
    use HasFactory;
    protected \$table = '$tableName';
    protected \$guarded = [];

    protected static function newFactory(): {$moduleName}Factory
    {
        // return {$moduleName}Factory::new();
    }
}
EOD;

    // Write the model content to the file
    File::put($modelPath, $modelContent);
}

    protected function generateRoutes($moduleName)
{
    $routePath = base_path('routes/web.php');
    $routes = "\nRoute::resource('" . strtolower($moduleName) . "', '" . $moduleName . "Controller');";
    File::append($routePath, $routes);
}

protected function generateCrud($controllerName, $moduleName, $parsedFields)
{
    $controllerPath = app_path('Http/Controllers/' . $controllerName . '.php');
    $moduleModel = ucfirst(strtolower($moduleName));

    // Determine validation rules dynamically based on the parsed fields
    $validationRules = [];
    foreach ($parsedFields as $field) {
        $mandatory = $field['mandatory'];
        if($mandatory)
        $validationRules[] = "'{$field['columnName']}' => 'required|string|max:255'";
        else
        $validationRules[] = "'{$field['columnName']}' => 'nullable|string|max:255'";
    }
    $validationRules = implode(",\n            ", $validationRules);

    $crudMethods = <<<EOD
    namespace App\Http\Controllers;

    use App\Models\\$moduleModel;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;

    class $controllerName extends Controller
    {
        public function index()
        {
            \${$moduleName}s = $moduleModel::all();
            return response()->json(['status' => 200, 'data' => \${$moduleName}s], 200);
        }

        public function store(Request \$request)
        {
            \$validator = Validator::make(\$request->all(), [
                $validationRules
            ]);

            if (\$validator->fails()) {
                return response()->json(['status' => 422, 'errors' => \$validator->messages()], 422);
            }

            \$data = \$request->all();
            \${$moduleName} = $moduleModel::create(\$data);

            return response()->json(['status' => 200, 'message' => '$moduleModel created successfully', 'data' => \${$moduleName}], 200);
        }

        public function show(\$id)
        {
            \${$moduleName} = $moduleModel::find(\$id);
            if (\${$moduleName}) {
                return response()->json(['status' => 200, 'data' => \${$moduleName}], 200);
            } else {
                return response()->json(['status' => 404, 'message' => '$moduleModel not found'], 404);
            }
        }

        public function update(Request \$request, \$id)
        {
            \$validator = Validator::make(\$request->all(), [
                $validationRules
            ]);

            if (\$validator->fails()) {
                return response()->json(['status' => 422, 'errors' => \$validator->messages()], 422);
            }

            \${$moduleName} = $moduleModel::find(\$id);
            if (\${$moduleName}) {
                \${$moduleName}->update(\$request->all());
                return response()->json(['status' => 200, 'message' => '$moduleModel updated successfully', 'data' => \${$moduleName}], 200);
            } else {
                return response()->json(['status' => 404, 'message' => '$moduleModel not found'], 404);
            }
        }

        public function destroy(\$id)
        {
            \${$moduleName} = $moduleModel::find(\$id);
            if (\${$moduleName}) {
                \${$moduleName}->delete();
                return response()->json(['status' => 200, 'message' => '$moduleModel deleted successfully'], 200);
            } else {
                return response()->json(['status' => 404, 'message' => '$moduleModel not found'], 404);
            }
        }
    }
    EOD;

    // Write the content to the controller file
    File::put($controllerPath, "<?php\n" . $crudMethods);
}

public function handleRequest(Request $request)
{
    // Process the request data
    $data = $request->all();

    // For example, let's return a JSON response with a success message
    return response()->json([
        'message' => 'Request was successful!',
        'data' => $data
    ]);
}
}

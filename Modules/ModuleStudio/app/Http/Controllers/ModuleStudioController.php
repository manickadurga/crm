<?php

namespace Modules\ModuleStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Modules\ModuleStudio\Models\Modulestudio;
use Modules\ModuleStudio\Models\Tab;
use Modules\ModuleStudio\Models\Block;
use Modules\ModuleStudio\Models\Field;
use Modules\ModuleStudio\Models\RelatedList;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class ModuleStudioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('modulestudio::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('modulestudio::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $moduleName = $request->input('module_name');
        $fields = $request->input('fields');
        // dd($moduleName);
        // Generate migration file
        $this->generateMigration($moduleName, $fields);

        return response()->json(['message' => 'Module migration created successfully'], 200);
    }

    private function generateMigration($moduleName, $fields)
    {
        
        $migrationName = 'create_' . str_replace(' ', '_', strtolower($moduleName)) . '_table';
        $migrationFileName = date('Y_m_d_His') . '_' . $migrationName;

        $fieldsString = '';
        foreach ($fields as $field) {
            $fieldsString .= $field['name'] . ':' . $field['type'] . ',';
        }

        $fieldsString = rtrim($fieldsString, ',');
        
        // Generate migration file
        // Artisan::call('module:make', [
        //     'name' => $moduleName
        // ]);
        Artisan::call('module:make', ['name' => $moduleName]);

        // Artisan::call('make:model',['name'=>$moduleName]);
        // Artisan::call('make:migration', [
        //     'name' => $migrationFileName,
        //     '--create' => $moduleName,
        // ]);
        $output = Artisan::output();
        
       
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('modulestudio::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('modulestudio::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function step1()
    {
        return view('modulestudio::step1');
    }

    public function step1Post(Request $request)
    {

        // dd($request);
        $request->validate([
            'module_name' => 'required|string',
            'version' => 'nullable|string',
            'singular_translation' => 'nullable|string',
            'plural_translation' => 'nullable|string',
            'menu' => 'nullable|string'
        ]);

        // Store the first step data in the session
        $request->session()->put('step1', $request->all());

        return redirect()->route('form.step2');
    }

    public function step2()
    {
        return view('modulestudio::step2');
    }

    public function step2Post(Request $request)
    {
  
    $step1 = $request->session()->get('step1');
    $moduleName = $step1['module_name'];
    $tableName = strtolower($moduleName) . "s";
    $migrationName = 'create_' . str_replace(' ', '_', $tableName) . '_table';

    Artisan::call('make:migration', ['name' => $migrationName]);

    // Get the timestamp of the last created migration
    $latestMigration = DB::table('migrations')->latest('batch')->first();
    $migrationTimestamp = now()->format('Y_m_d_His');

    // Get the path of the created migration file
    $migrationPath = database_path('migrations') . '/' . $migrationTimestamp . "_${migrationName}.php";

    sleep(1);

    // Modify the migration file
    $migrationContent = File::get($migrationPath);

    $fields = json_decode($request->input('dragDropModalData'), true);

    $fieldsSchema = '';
    // Iterate through each item in dragDropModalData array
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

    // Run the specific migration
    Artisan::call('migrate', [
        '--path' => 'database/migrations/' . $migrationTimestamp . "_${migrationName}.php"
    ]);

        $request->session()->put('step2', $request->all());

        return redirect()->route('form.step3');
        
    }

    Public function step3(){
        $tabs = Tab::all();
        $tabName = $tabs->pluck('name')->toArray();
        return view('modulestudio::step3',['modules' => $tabName]);
    }
    
    Public function step3Post(Request $request)
    {
        $request->session()->put('step3', $request->all());            
        return redirect()->route('form.step4');
    }
    
    public function step4(Request $request){

       
    $step1=$request->session()->get('step1');
    // Retrieve the stored data from the session
    $step2 = $request->session()->get('step2');
    $step3 = $request->session()->get('step3');

    if (isset($step2['dragDropModalData'])) {
        $fields = json_decode($step2['dragDropModalData'], true);
    } else {
        $fields = [];
    }
     
    foreach ($fields as $item) {
        $columnName[] = $item['columnName'];
     }
     if(isset($columnName)){
       return view('modulestudio::step4',['modules' => $columnName]);
     }
     else
      return view('modulestudio::step4');
    }
    
    public function step4Post(Request $request){
        $step1 = $request->session()->get('step1');
        $step2 = $request->session()->get('step2');
        $step3 = $request->session()->get('step3');
        $blockModalData = json_decode($step2['blockModalData'], true);
        $fields = json_decode($step2['dragDropModalData'], true);

        $tab = Tab::where('name', $step3['label'])->first();
        $tabId = $tab->tabid;
       
        $formField = new Modulestudio();
        $formField->module_name = $step1['module_name'];
        $formField->version = $step1['version'];
        $formField->singular_translation = $step1['singular_translation'];
        $formField->plural_translation = $step1['plural_translation'];
        $formField->menu = $step1['menu'];

        $formField->save();
       
        $tab = new Tab();
        $tab->name = $step1['module_name'];
        $tab->presence = 0; 
        $tab->tablabel = $step1['module_name'];
        $tab->modifiedby = null; 
        $tab->modifiedtime = now();
        $tab->customized = 0;
        $tab->ownedby = 0; 
        $tab->isentitytype = 1; 
        $tab->trial = 0; 
        $tab->version = $step1['version'] ?? null;
        $tab->parent = $step1['menu'] ?? null;
        $tab->save();

        $lastTab = Tab::latest('tabid')->first();
        $lastTabId = $lastTab ? $lastTab->tabid : 0;

        foreach ($blockModalData as $data) {
            $blockData = new Block();
            $blockData->tabid = $lastTabId;
            $blockData->blocklabel = $data['editedLabel'];
            $blockData->sequence = 1;
            $blockData->show_title =$data['showTitle'] ? 1 : 0;
            $blockData->visible = $data['visible'] ? 1 : 0;
            $blockData->create_view =  $data['createView'] ? 1 : 0;
            $blockData->edit_view = $data['editView'] ? 1 : 0;
            $blockData->detail_view = $data['detailView'] ? 1 : 0;
            $blockData->display_status = $data['displayStatus'] ? 1 : 0;
            $blockData->iscustom = $data['isCustom'] ? 1 : 0;
            $blockData->save();
        }
       
        $lastBlock = Tab::latest('tabid')->first();
        $lastBlockId = $lastBlock ? $lastBlock->blockid : 0;


        foreach ($fields as $data) {
            $fieldData = new Field();
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
            $fieldData->block = $lastBlockId ; 
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
        }
        
        $relatedData = new RelatedList();
        $relatedData->tabid = $lastTabId;
        $relatedData->related_tabid = $tabId;
        $relatedData->name = $step3['method'];
        $relatedData->sequence = 1;
        $relatedData->label = $step3['label'];
        $relatedData->presence = 0; // default to 0 if not provided
        $relatedData->actions = 'ADD';
        $relatedData->relationfieldid = 0;
        $relatedData->source = $step3['source'] ?? null;
        $relatedData->relationtype = $step3['relation_type'] ?? null;

        // Save the instance to the database
        $relatedData->save();

        $parentTab = ParentTab::where('parenttab_label', $step1['menu'])->first();
        $parentTabId = $parentTab->parenttabid;
        
        

        $parentTabRelData = new ParentTabRel();
        $parentTabRelData->parenttabid = $parentTabId;
        $parentTabRelData->tabid = $lastTabId;
        $parentTabRelData->sequence = '';
        $parentTabRelData->save();

        
        $moduleName = $step1['module_name'];
        $tableName = strtolower($moduleName)."s";
        
        Artisan::call('make:model', ['name' => $moduleName]);

        $this->generateModel($moduleName, $tableName);
        $controllerName = $moduleName . 'Controller';
        Artisan::call('make:controller', ['name' => $controllerName]);
        $this->generateRoutes($moduleName);
        $this->generateCrud($controllerName, $moduleName, $fields);
        //     // Clear the session data
        $request->session()->forget('step1');
        $request->session()->forget('step2');
        $request->session()->forget('step3');
        
        return redirect()->route('form.success');

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


}

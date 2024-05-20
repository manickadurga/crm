<?php

namespace Modules\ModuleStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Modules\ModuleStudio\Models\Modulestudio;
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
        dd($output);
       
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
        // dd($request);
        // Retrieve the first step data from the session
        $step1 = $request->session()->get('step1');
        $moduleName = $step1['module_name'];
        $fields = $request->input('fields');
        $parsedFields = [];
        foreach ($fields as $field) {
            $parsedFields[] = json_decode($field, true);
        }
        // dd($parsedFields);

        // Validate the fields array
        $request->validate([
            'fields' => 'required|array',
        ]);
        
        // Create a new FormField entry
        $formField = new Modulestudio();
        $formField->module_name = $step1['module_name'];
        $formField->version = $step1['version'];
        $formField->singular_translation = $step1['singular_translation'];
        $formField->plural_translation = $step1['plural_translation'];
        $formField->fields = json_encode($request->fields); // Store fields as JSON
        $formField->save();
    
        
        $tableName = strtolower($moduleName);
        $migrationName = 'create_' . str_replace(' ', '_', strtolower($moduleName)) . '_table';

        // $migrationName = 'create_' . $tableName . '_table';
        Artisan::call('make:migration', ['name' => $migrationName]);
    
        // Get the path of the created migration file
        $migrationPath = database_path('migrations') . '/' . now()->format('Y_m_d_His') . "_${migrationName}.php";
        
        // Modify the migration file
        $migrationContent = File::get($migrationPath);
    
        $fieldsSchema = '';
        foreach ($parsedFields as $field) {
            $columnName = $field['columnName'];
            $columnType = $field['columnType'];
            $mandatory = $field['mandatory'];
            if($mandatory)
            $fieldsSchema .= "\$table->$columnType('$columnName');\n";
            else
            $fieldsSchema .= "\$table->$columnType('$columnName')->nullable;\n";


        }
    
        $migrationContent = str_replace(
            '$table->id();',
            "\$table->id();\n$fieldsSchema",
            $migrationContent
        );
    
        File::put($migrationPath, $migrationContent);
    
        // Run the migration
        Artisan::call('migrate');
        // Artisan::call('make:model', ['name' => $moduleName]);
        $this->generateModel($moduleName, $tableName);
    // Generate the controller
        $controllerName = $moduleName . 'Controller';
        Artisan::call('make:controller', ['name' => $controllerName]);
        $this->generateRoutes($moduleName);
        $this->generateCrud($controllerName, $moduleName, $parsedFields);
        // Clear the session data
        $request->session()->forget('step1');

        return redirect()->route('form.success');
    }
    private function generateModel($moduleName, $tableName)
{
    // dd($tableName);
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


    public function success()
    {
        return view('modulestudio::success');
    }
}

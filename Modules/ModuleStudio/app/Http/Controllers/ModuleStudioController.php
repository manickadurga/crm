<?php

namespace Modules\ModuleStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

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
}

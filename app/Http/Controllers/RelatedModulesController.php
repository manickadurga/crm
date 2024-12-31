<?php

namespace App\Http\Controllers;

use App\Models\RelatedModule;
use Illuminate\Http\Request;

class RelatedModulesController extends Controller
{
    public function index()
    {
        $relatedModules = RelatedModule::all();
        return response()->json($relatedModules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tabid' => 'required|exists:jo_tabs,tabid',
            'relatedto_tabid' => 'required|exists:jo_tabs,tabid',
        ]);

        $relatedModule = RelatedModule::create($validatedData);
        return response()->json($relatedModule, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $relatedModule = RelatedModule::findOrFail($id);
        return response()->json($relatedModule);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'tabid' => 'sometimes|required|exists:jo_tabs,tabid',
            'relatedto_tabid' => 'sometimes|required|exists:jo_tabs,tabid',
        ]);

        $relatedModule = RelatedModule::findOrFail($id);
        $relatedModule->update($validatedData);
        return response()->json($relatedModule);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $relatedModule = RelatedModule::findOrFail($id);
        $relatedModule->delete();
        return response()->json(null, 204);
    }
}

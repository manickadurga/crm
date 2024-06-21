<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tags;



class TagsController extends Controller
{public function index()
    {
        try {
            $tags = Tags::all();
            return response()->json($tags, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tags::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        $tags = $request->validate([
            'name'=>'required|string',
            'tag_color'=>'required|string',
            'tenant_level'=>'nullable|boolean',
            'description'=>'nullable|string',
            'orgid' => 'integer',
            
        ]);          
            
        Tags::create($tags);
        
        return response()->json([ 'message' => 'created successfully']);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);    }
}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $tags = Tags::findOrFail($id);
            return response()->json($tags);
        //  } catch (ModelNotFoundException $e) {
            // return response()->json(['message' => 'tasks not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
    


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('tags::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name'=>'required|string',
                'tag_color'=>'required|string',
                'tenant_level'=>'nullable|boolean',
                'description'=>'nullable|string',
                'orgid' => 'integer',
            ]); 
    
            $tags = Tags::findOrFail($id); // Retrieve the proposal instance
            $tags->update($validatedData); // Update the instance with the validated data
    
            return response()->json($tags, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        {

            try {
                $tags = Tags::findOrFail($id); // Ensure you use the correct model
                $tags->delete();
                return response()->json(['message' => 'deleted successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to delete', 'error' => $e->getMessage()], 500);
            }
        }
    
    }
    
    }
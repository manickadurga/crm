<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Equipments;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\Tags;

class EquipmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       try {
            $equipments = Equipments::all();
            if ($equipments->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No records found',
                ], 404);
            }
    
            return response()->json([
                'status' => 200,
                'customers' => $equipments,
            ], 200);
        } catch (Exception $e) {
            
            // Log the error
            Log::error('Failed to retrieve customers: ' . $e->getMessage());
    
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve customers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
                'name' => 'required|string',
                'type' => 'nullable|string',
                'manufactured_year' => 'nullable|integer',
                'sn' => 'nullable|string',
                'max_share_period' => 'nullable|integer',
                'initial_cost' => 'nullable|integer',
                'currency' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'auto_approve' => 'boolean|nullable',
        ]);
            // Validate each tag
            if (isset($validatedData['tags'])) {
                $tags = [];
                foreach ($validatedData['tags'] as $id) {
                    $tag = Tags::find($id);
                    if ($tag) {
                        $tags[] = [
                            'tags_name' => $tag->tags_name,
                            'tag_color' => $tag->tag_color,
                        ];
                    } else {
                        throw ValidationException::withMessages(['tags' => "Tag with ID '$id' not found"]);
                    }
                }
                $validatedData['tags'] = json_encode($tags);
            }
    

        $equipment = Equipments::create($validatedData);
        return response()->json($equipment, 201);
    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (Exception $e) {
        Log::error('Failed to create equipment: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to create equipment'], 500);
    }
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $equipment = Equipments::findOrFail($id);
            return response()->json($equipment);
        } catch (Exception $e) {
            Log::error('Failed to fetch equipment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch equipment'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'type' => 'nullable|string',
                'manufactured_year' => 'nullable|integer',
                'sn' => 'nullable|string',
                'max_share_period' => 'nullable|integer',
                'initial_cost' => 'nullable|integer',
                'currency' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'auto_approve' => 'boolean|nullable',
                
            ]);
             // Validate each tag
             if (isset($validatedData['tags'])) {
                $tags = [];
                foreach ($validatedData['tags'] as $id) {
                    $tag = Tags::find($id);
                    if ($tag) {
                        $tags[] = [
                            'tags_name' => $tag->tags_name,
                            'tag_color' => $tag->tag_color,
                        ];
                    } else {
                        throw ValidationException::withMessages(['tags' => "Tag with ID '$id' not found"]);
                    }
                }
                $validatedData['tags'] = json_encode($tags);
            }
    
          
    
            $equipment = Equipments::findOrFail($id);
            $equipment->update($validatedData);
            return response()->json($equipment);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (Exception $e) {
            Log::error('Failed to update equipment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update equipment'], 500);
        }
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $equipment = Equipments::findOrFail($id);
            $equipment->delete();
            return response()->json(['message' => 'Equipment deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete equipment: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete equipment'], 500);
        }
    }
}

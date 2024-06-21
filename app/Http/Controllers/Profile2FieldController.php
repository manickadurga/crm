<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile2Field;
use Illuminate\Support\Facades\Log;

class Profile2FieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $profile2fields = Profile2Field::all();
            return response()->json($profile2fields);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_profile2field: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch jo_profile2field'], 500);
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
                'profileid' => 'required|integer',
                'tabid' => 'required|integer',
                'fieldid' => 'required|integer',
                'visible' => 'required|boolean',
                'readonly' => 'required|boolean',
            ]);

            $profile2field = Profile2Field::create($validatedData);
            return response()->json($profile2field, 201);
        } catch (\Exception $e) {
            Log::error('Error creating jo_profile2field: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create jo_profile2field'], 500);
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
            $profile2field = Profile2Field::findOrFail($id);
            return response()->json($profile2field);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_profile2field: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch jo_profile2field'], 500);
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
                'profileid' => 'required|integer',
                'tabid' => 'required|integer',
                'fieldid' => 'required|integer',
                'visible' => 'required|boolean',
                'readonly' => 'required|boolean',
            ]);

            $profile2field = Profile2Field::findOrFail($id);
            $profile2field->update($validatedData);
            return response()->json($profile2field);
        } catch (\Exception $e) {
            Log::error('Error updating jo_profile2field: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update jo_profile2field'], 500);
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
            $profile2field = Profile2Field::findOrFail($id);
            $profile2field->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting jo_profile2field: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete jo_profile2field'], 500);
        }
    }
}
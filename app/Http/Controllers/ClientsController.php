<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clients;
use Illuminate\Validation\ValidationException;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Clients::all();
        return response()->json($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'image' => 'nullable|string',
                'name' => 'required|string|max:255',
                'primary_email' => 'nullable|string|max:255',
                'primary_phone' => 'nullable|string|max:255',
                'website' => 'nullable|string|max:255',
                'fax' => 'nullable|string|max:255',
                'fiscal_information' => 'nullable|string',
                'projects' => 'nullable|array',
                'contact_type' => 'nullable|string|max:255',
                'tags' => 'nullable|array',
                'location' => 'nullable|array',
                'type' => 'nullable|string|max:255',
                'type_suffix' => 'nullable|integer',
                'orgid' => 'nullable|integer',
            ]);

            $client = Clients::create($validatedData);
            return response()->json($client, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create client', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $client = Clients::findOrFail($id);
            return response()->json($client);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve client', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'image' => 'nullable|string',
                'name' => 'required|string|max:255',
                'primary_email' => 'nullable|string|max:255',
                'primary_phone' => 'nullable|string|max:255',
                'website' => 'nullable|string|max:255',
                'fax' => 'nullable|string|max:255',
                'fiscal_information' => 'nullable|string',
                'projects' => 'nullable|array',
                'contact_type' => 'nullable|string|max:255',
                'tags' => 'nullable|array',
                'location' => 'nullable|array',
                'type' => 'nullable|string|max:255',
                'type_suffix' => 'nullable|integer',
                'orgid' => 'nullable|integer',
            ]);

            $client = Clients::findOrFail($id);
            $client->update($validatedData);
            return response()->json($client);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update client', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $client = Clients::findOrFail($id);
            $client->delete();
            return response()->json(['message' => 'Client deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete client', 'error' => $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MailScannerFolders;
use Exception;
use Illuminate\Support\Facades\Log;

class MailScannerFoldersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $folders = MailScannerFolders::all();
            return response()->json($folders);
        } catch (Exception $e) {
            Log::error('Error fetching folders: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching folders'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'folderid' => 'required|integer',
                'scannerid' => 'required|integer',
                'foldername' => 'required|string',
                'lastscan' => 'required|string',
                'rescan' => 'required|integer',
                'renabled' => 'required|integer',
            ]);

            $folder = MailScannerFolders::create($validatedData);
            return response()->json($folder, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage());
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error creating folder: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating folder'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $folder = MailScannerFolders::findOrFail($id);
            return response()->json($folder);
        } catch (Exception $e) {
            Log::error('Error fetching folder: ' . $e->getMessage());
            return response()->json(['message' => 'Folder not found'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'folderid' => 'required|integer',
                'scannerid' => 'required|integer',
                'foldername' => 'required|string',
                'lastscan' => 'required|string',
                'rescan' => 'required|integer',
                'renabled' => 'required|integer',
            ]);

            $folder = MailScannerFolders::findOrFail($id);
            $folder->update($validatedData);
            return response()->json($folder);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage());
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error updating folder: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating folder'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $folder = MailScannerFolders::findOrFail($id);
            $folder->delete();
            return response()->json(null, 204);
        } catch (Exception $e) {
            Log::error('Error deleting folder: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting folder'], 500);
        }
    }
}

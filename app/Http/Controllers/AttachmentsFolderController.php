<?php

namespace App\Http\Controllers;

use App\Models\AttachmentsFolder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AttachmentsFolderController extends Controller
{
    public function index()
    {
        try {
            $folders = AttachmentsFolder::all();
            return response()->json($folders);
        } catch (Exception $e) {
            Log::error('Error fetching folders: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching folders', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'folderid' => 'required|integer',
                'foldername' => 'required|string|max:255',
                'description' => 'required|string',
                'createdby' => 'required|integer',
                'sequence' => 'required|integer',
            ]);

            $folder = AttachmentsFolder::create($request->all());
            return response()->json($folder, 201);
        } catch (ValidationException $e) {
            Log::error('Validation error while creating folder: ' . $e->getMessage());
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error creating folder: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating folder', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $folder = AttachmentsFolder::findOrFail($id);
            return response()->json($folder);
        } catch (Exception $e) {
            Log::error('Error fetching folder: ' . $e->getMessage());
            return response()->json(['message' => 'Folder not found', 'error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'folderid' => 'integer',
                'foldername' => 'string|max:255',
                'description' => 'string',
                'createdby' => 'integer',
                'sequence' => 'integer',
            ]);

            $folder = AttachmentsFolder::findOrFail($id);
            $folder->update($request->all());
            return response()->json($folder);
        } catch (ValidationException $e) {
            Log::error('Validation error while updating folder: ' . $e->getMessage());
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error updating folder: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating folder', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $folder = AttachmentsFolder::findOrFail($id);
            $folder->delete();
            return response()->json(['message' => 'Resource deleted'], 204);
        } catch (Exception $e) {
            Log::error('Error deleting folder: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting folder', 'error' => $e->getMessage()], 500);
        }
    }
}

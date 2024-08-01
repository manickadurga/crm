<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\Crmentity;
use Exception;


class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            // Set the number of items per page, default is 10
            $perPage = $request->input('per_page', 10);

            // Get paginated documents with specific fields including 'id', 'document_name', 'document_url'
            $documents = Document::select('id', 'document_name', 'documemt_url')
                ->paginate($perPage);

            // Prepare array to hold formatted documents
            $formattedDocuments = [];

            // Iterate through each document to format data
            foreach ($documents as $document) {
                $formattedDocuments[] = [
                    'id' => $document->id,
                    'document_name' => $document->document_name,
                    'document_url' => $document->document_url,
                ];
            }

            // Return JSON response with formatted data and pagination information
            return response()->json([
                'status' => 200,
                'documents' => $formattedDocuments,
                'pagination' => [
                    'total' => $documents->total(),
                    'title' => 'Document',
                    'per_page' => $documents->perPage(),
                    'current_page' => $documents->currentPage(),
                    'last_page' => $documents->lastPage(),
                    'from' => $documents->firstItem(),
                    'to' => $documents->lastItem(),
                ],
            ], 200);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to retrieve documents: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve documents',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $document = Document::find($id);
        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }
        return response()->json($document);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // Validate the incoming request data
            $validatedData = Validator::make($request->all(), [
                'document_name' => 'required|string|max:255',
                'document_url' => 'nullable|url',
            ])->validate();
    
            // Create Crmentity record via CrmentityController
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Documents', $validatedData['document_name']);
    
            // Set crmid as the id in the document data
            $validatedData['id'] = $crmid;
    
            // Create the Document with the crmid as id
            $document = Document::create($validatedData);

            DB::commit();
    
            return response()->json([
                'message' => 'Document and Crmentity created successfully',
                'document' => $document,
            ], 201);
    
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Exception in DocumentController@store: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create document and Crmentity',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();
    
        try {
            // Find the document by ID
            $document = Document::find($id);
            if (!$document) {
                return response()->json(['message' => 'Document not found'], 404);
            }
    
            // Validate the incoming request data
            $validatedData = $request->validate([
                'document_name' => 'sometimes|required|string|max:255',
                'document_url' => 'nullable|url',
            ]);
    
            // Update the document
            $document->fill($validatedData);
            $document->save();
    
            // Update the related Crmentity entry
            $crmentity = Crmentity::where('crmid', $id)->where('setype', 'Documents')->first();
            if ($crmentity) {
                $crmentity->update([
                    'label' => $validatedData['document_name'] ?? $crmentity->label,
                    'modifiedby' => auth()->id(), // Assuming you have authentication setup
                    'modifiedtime' => now(),
                ]);
            }
    
            // Commit the transaction
            DB::commit();
    
            return response()->json([
                'message' => 'Document and Crmentity updated successfully',
                'document' => $document,
                'crmentity' => $crmentity ?? 'Crmentity entry not found',
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Rollback the transaction on validation error
            DB::rollBack();
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Rollback the transaction on general error
            DB::rollBack();
            Log::error('Exception in DocumentController@update: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating document'], 500);
        }
    }

    public function destroy($id)
    {
        $document = Document::find($id);
        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        try {
            $document->delete();
            return response()->json(['message' => 'Document deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Exception in DocumentController@destroy: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting document'], 500);
        }
    }
        public function search(Request $request)
{
    try {
        $validatedData = $request->validate([
            'id' => 'nullable|integer',
            'document_name' => 'nullable|string|max:255',
            'document_url' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1',
        ]);
        $query = Document::query();
        if (!empty($validatedData['id'])) {
            $query->where('id', $validatedData['id']);
        }
        if (!empty($validatedData['document_name'])) {
            $query->where('document_name', 'like', '%' . $validatedData['document_name'] . '%');
        }
        if (!empty($validatedData['document_url'])) {
            $query->where('documemt_url', 'like', '%' . $validatedData['document_url'] . '%');
        }
        $perPage = $validatedData['per_page'] ?? 10;
        $documents = $query->paginate($perPage);
        if ($documents->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No matching records found',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'documents' => $documents->items(),
            'pagination' => [
                'total' => $documents->total(),
                'per_page' => $documents->perPage(),
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'from' => $documents->firstItem(),
                'to' => $documents->lastItem(),
            ],
        ], 200);

    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Failed to search documents: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to search documents: ' . $e->getMessage()], 500);
    }
}
}

<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\Operation;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    public function index()
    {
        try {
            $operations = Operation::all();
            return response()->json($operations);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_operations: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch operations'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'operationid' => 'required|integer',
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
            ]);

            $operation = Operation::create($validatedData);
            return response()->json($operation, 201);
        } catch (\Exception $e) {
            Log::error('Error creating jo_operation: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create operation'], 500);
        }
    }

    public function show($id)
    {
        try {
            $operation = Operation::findOrFail($id);
            return response()->json($operation);
        } catch (\Exception $e) {
            Log::error('Error fetching jo_operation: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch operation'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'operationid' => 'required|integer',
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
            ]);

            $operation = Operation::findOrFail($id);
            $operation->update($validatedData);
            return response()->json($operation);
        } catch (\Exception $e) {
            Log::error('Error updating jo_operation: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update operation'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $operation = Operation::findOrFail($id);
            $operation->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting jo_operation: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete operation'], 500);
        }
    }
}

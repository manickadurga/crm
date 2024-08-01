<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\Crmentity;
use App\Models\Departments;

class DepartmentsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $departments = Departments::select('id', 'departments', 'add_or_remove_employees')
                                      ->paginate($perPage);
            return response()->json([
                'status' => 200,
                'departments' => $departments->items(),
                'pagination' => [
                    'total' => $departments->total(),
                    'title' => 'Departments',
                    'per_page' => $departments->perPage(),
                    'current_page' => $departments->currentPage(),
                    'last_page' => $departments->lastPage(),
                    'from' => $departments->firstItem(),
                    'to' => $departments->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve departments: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'departments' => 'required|string|max:255',
                'add_or_remove_employees' => 'required|array',
                'add_or_remove_employees.*' => 'exists:jo_manage_employees,id',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
            ]);

            // Create Crmentity record via CrmentityController
            $crmentityController = new CrmentityController();
            $crmid = $crmentityController->createCrmentity('Departments', $validatedData['departments']);

            // Assign crmid to the department data
            $validatedData['id'] = $crmid; // Set crmid as the id

            // Create the department with the crmid as id
            $department = Departments::create($validatedData);

            return response()->json([
                'message' => 'Department created successfully',
                'department' => $department,
            ], 201);

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
            // Handle general errors
            return response()->json([
                'error' => 'Failed to create department',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    
    public function show($id)
    {
        try {
            $proposal = Departments::findOrFail($id);
            return response()->json($proposal);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Department not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'departments' => 'required|string|max:255',
                'add_or_remove_employees' => 'nullable|array',
                'add_or_remove_employees.*' => 'exists:jo_manage_employees,id',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
            ]);
    
            // Find the department by ID and update it
            $department = Departments::findOrFail($id);
            $department->update($validatedData);
    
            // Update the associated Crmentity record
            $crmentity = Crmentity::where('crmid', $department->id)->firstOrFail();
            $crmentity->update([
                'label' => $validatedData['departments'],
                //'modifiedby' => auth()->id(), // Assuming you want to set the current user's ID as the modifier
                'modifiedtime' => now(),
                'status' => 'Updated', // Example status update, customize as needed
            ]);
    
            return response()->json([
                'message' => 'Department and Crmentity updated successfully',
                'department' => $department,
                'crmentity' => $crmentity, // Include Crmentity in the response if needed
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors(), // Returns the validation errors
            ], 422);
        } catch (\Exception $e) {
            // Handle general errors
            Log::error('Failed to update department: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update department',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function destroy($id)
    {
        {
            try {
                $departments = Departments::findOrFail($id); // Ensure you use the correct model
                $departments->delete();
                return response()->json(['message' => 'deleted successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to delete', 'error' => $e->getMessage()], 500);
            }
        }

    }
    public function search(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = Departments::select('id', 'departments', 'add_or_remove_employee');
            if ($search) {
                $query->where('departments', 'LIKE', "%{$search}%")
                      ->orWhere('add_or_remove_employee', 'LIKE', "%{$search}%");
            }
            $departments = $query->paginate($perPage);
            return response()->json([
                'status' => 200,
                'departments' => $departments->items(),
                'pagination' => [
                    'total' => $departments->total(),
                    'per_page' => $departments->perPage(),
                    'current_page' => $departments->currentPage(),
                    'last_page' => $departments->lastPage(),
                    'from' => $departments->firstItem(),
                    'to' => $departments->lastItem(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to search departments: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Failed to search departments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    }


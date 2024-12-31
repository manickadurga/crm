<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Crmentity;

class EmployeesController extends Controller
{
    public function index()
    {
        try {
            $employees = Employee::all();
            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve employees.'], 500);
        }
    }
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validatedData= Validator::make($request->all(), [
            'image' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'username' => 'nullable|string',
            'email' => 'required|email|unique:jo_manage_employees',
            'password' => 'required',
            'date' => 'nullable|date',
            'reject_date' => 'nullable|date',
            'offer_date' => 'nullable|date',
            'accept_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
        ])->validate();

        // if ($validator->fails()) {
        //     return response()->json($validator->errors(), 400);
        // }

        // Get the validated data
        //$validatedData = $validator->validated();

        // Create the employee record
        $employee = Employee::create($validatedData);

        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('manage', $validatedData['first_name']);

        // Set crmid as the id in the document data
        $validatedData['id'] = $crmid;
        $employee = Employee::create($validatedData);
        DB::commit();

        return response()->json([
            'message' => 'Employee and Crmentity created successfully',
            'employee' => $employee,
        ], 201);

    } catch (ValidationException $e) {
        DB::rollBack();
        Log::error('Validation failed while creating employee: ' . $e->getMessage());
        return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create employee and Crmentity: ' . $e->getMessage());
        Log::error($e->getTraceAsString()); 
        return response()->json(['error' => 'Failed to create employee and Crmentity: ' . $e->getMessage()], 500);
    }
}

    public function show($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            return response()->json($employee);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }
    }
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
    
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|string',
                'first_name' => 'nullable|string',
                'last_name' => 'nullable|string',
                'username' => 'nullable|string',
                'email' => 'nullable|email|unique:jo_manage_employees,email,' . $id, // Exclude current employee from unique validation
                'password' => 'nullable|string', // Make password nullable in case it's not being updated
                'date' => 'nullable|date',
                'reject_date' => 'nullable|date',
                'offer_date' => 'nullable|date',
                'accept_date' => 'nullable|date',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            // Get the validated data
            $validatedData = $validator->validated();
    
            // Find and update the employee record
            $employee = Employee::findOrFail($id);
            $employee->update($validatedData);
    
            // Find the corresponding Crmentity entry
            $crmentity = Crmentity::where('crmid', $id)->where('setype', 'manage')->first();
    
            if ($crmentity) {
                // Update existing Crmentity record
                $crmentity->update([
                    'label' => $validatedData['first_name'],
                    'modifiedtime' => now(),
                   // 'modifiedby' => auth()->id(), // Assuming you have authentication setup
                ]);
            } else {
                // Create a new Crmentity record if it does not exist
                Crmentity::create([
                    'crmid' => $id,
                    'setype' => 'Employees',
                    'label' => $validatedData['first_name'],
                    'createdtime' => now(),
                    'modifiedtime' => now(),
                   // 'createdby' => auth()->id(), // Assuming you have authentication setup
                    //'modifiedby' => auth()->id(),
                ]);
            }
    
            // Commit the transaction
            DB::commit();
    
            return response()->json([
                'message' => 'Employee and Crmentity updated successfully',
                'employee' => $employee,
            ], 200);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update employee and Crmentity: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update employee and Crmentity'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete employee.'], 500);
        }
    }
}

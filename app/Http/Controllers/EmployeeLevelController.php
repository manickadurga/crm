<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLevel;
use Exception;
use Illuminate\Http\Request;

class EmployeeLevelController extends Controller
{
  public function index(){
    try{
        $employeelevels=EmployeeLevel::all();
        return response()->json(['data'=>$employeelevels],200);
    }
    catch(Exception $e){
        return response()->json(['error' => 'Failed to retrieve employee levels', 'message' => $e->getMessage()], 500);

    }
  }

  public function store(Request $request)
{
    // Validate the request
    $validatedData = $request->validate([
        'level_name' => 'required|string',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:jo_tags,id',
    ]);

    try {
        // Create CRM entity
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Employee Levels', $validatedData['level_name']);

        // Create EmployeeLevel with CRM ID
        $employeelevels = new EmployeeLevel();
        $employeelevels->level_name = $validatedData['level_name'];
        $employeelevels->tags = json_encode($validatedData['tags']); // Convert array to JSON
        $employeelevels->id = $crmid; // Add CRM ID to employee level data
        $employeelevels->save();

        return response()->json([
            'message' => 'Employee Level Created Successfully',
            'data' => $employeelevels
        ], 201);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Failed to Create Employee Level',
            'message' => $e->getMessage()
        ], 500);
    }
}
  public function show($id){
    try{
        $employeelevels=EmployeeLevel::findorFail($id);
        return response()->json(['data'=>$employeelevels],200);
    }
    catch(Exception $e){
        return response()->json(['error'=>'Failed to retrieve Employee Level Details','message'=>$e->getMessage(),404]);
    }
  }
  public function update(Request $request, $id)
{
    // Validate the request
    $validatedData = $request->validate([
        'level_name' => 'required|string',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:jo_tags,id',
    ]);

    try {
        // Find the EmployeeLevel record
        $employeelevel = EmployeeLevel::findOrFail($id);

        // Retrieve the existing CRM entity ID (assuming 'id' is the CRM ID)
        $crmid = $employeelevel->id;

        // Instantiate CrmentityController
        $crmentityController = new CrmentityController();

        // Update the CRM entity
        $updated = $crmentityController->updateCrmentity($crmid, [
            'label' => $validatedData['level_name'],
            // Add other fields if needed
        ]);

        // Check if the CRM entity was updated successfully
        if (!$updated) {
            throw new Exception('Failed to update Crmentity');
        }

        // Update the EmployeeLevel record
        $employeelevel->level_name = $validatedData['level_name'];
        $employeelevel->tags = json_encode($validatedData['tags']); // Convert array to JSON
        $employeelevel->save();

        return response()->json([
            'message' => 'Employee Level updated successfully',
            'employee_level' => $employeelevel,
        ], 200);
    } catch (Exception $e) {
        // Log the exception for debugging
        //\Log::error('Failed to update Employee Level: ' . $e->getMessage());

        return response()->json([
            'error' => 'Failed to update Employee Level',
            'message' => $e->getMessage()
        ], 500);
    }
}
  public function destroy($id){
    try{
        $employeelevels=EmployeeLevel::findorFail($id);
        $employeelevels->delete();
        return response(['message'=>'Employee Level Details Deleted Successfully'],200);
    }
    catch(Exception $e){
        return response(['message'=>'Failed to Delete Employee Level','error'=>$e->getMessage()],500);
    }
  }
}

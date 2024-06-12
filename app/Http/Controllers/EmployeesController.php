<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Retrieve all employees
            $employees = Employee::all();
            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve employees.'], 500);
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
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|string',
                'first_name' => 'nullable|string',
                'last_name' => 'nullable|string',
                'username' => 'nullable|string',
                'email' => 'required|email|unique:jo_employees',
                'password' => 'required',
                'date' => 'nullable|date',
                'reject_date' => 'nullable|date',
                'offer_date' => 'nullable|date',
                'accept_date' => 'nullable|date',
                'tags' => 'nullable|array',
                'orgid' => 'nullable|integer',
                // Add more validation rules as needed
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            // Create a new employee
            $employee = Employee::create($request->all());

            return response()->json($employee, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create employee.'], 500);
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
            // Retrieve the specified employee
            $employee = Employee::findOrFail($id);
            return response()->json($employee);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Employee not found.'], 404);
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
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|string',
                'first_name' => 'nullable|string',
                'last_name' => 'nullable|string',
                'username' => 'nullable|string',
                'email' => 'required|email|unique:jo_employees,email,' . $id,
                'password' => 'required',
                'date' => 'nullable|date',
                'reject_date' => 'nullable|date',
                'offer_date' => 'nullable|date',
                'accept_date' => 'nullable|date',
                'tags' => 'nullable|json',
                'orgid' => 'nullable|integer',
                // Add more validation rules as needed
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            // Update the specified employee
            $employee = Employee::findOrFail($id);
            $employee->update($request->all());

            return response()->json($employee, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update employee.'], 500);
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
            // Delete the specified employee
            $employee = Employee::findOrFail($id);
            $employee->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete employee.'], 500);
        }
    }
}

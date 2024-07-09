<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Crmentity;
use App\Models\Tags;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
        DB::beginTransaction();
    
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
                'tags.*.tags_name' => 'exists:jo_tags,tags_name',
                'tags.*.tag_color' => 'exists:jo_tags,tag_color',
                'orgid' => 'nullable|integer',
                // Add more validation rules as needed
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
    
            $validatedData = $validator->validated();
    
            // Handle tags
            if (isset($validatedData['tags'])) {
                $tags = [];
                foreach ($validatedData['tags'] as $tag) {
                    $tagModel = Tags::where('tags_name', $tag['tags_name'])
                                    ->where('tag_color', $tag['tag_color'])
                                    ->first();
                    if ($tagModel) {
                        $tags[] = [
                            'tags_name' => $tagModel->tags_name,
                            'tag_color' => $tagModel->tag_color,
                        ];
                    } else {
                        throw ValidationException::withMessages(['tags' => "Tag '{$tag['tags_name']}' with color '{$tag['tag_color']}' not found"]);
                    }
                }
                $validatedData['tags'] = json_encode($tags);
            }
    
            // Retrieve or create a new Crmentity record
            $defaultCrmentity = Crmentity::where('setype', 'Invoices')->first();
    
            if (!$defaultCrmentity) {
                // Log an error if default Crmentity not found
                Log::error('Default Crmentity for Employees not found');
                throw new \Exception('Default Crmentity not found');
            }
    
            // Create a new Crmentity record with a new crmid
            $newCrmentity = new Crmentity();
            $newCrmentity->crmid = Crmentity::max('crmid') + 1;
            $newCrmentity->smcreatorid = $defaultCrmentity->smcreatorid ?? 0; // Replace with appropriate default
            $newCrmentity->smownerid = $defaultCrmentity->smownerid ?? 0; // Replace with appropriate default
            $newCrmentity->setype = 'Employees';
            $newCrmentity->description = $defaultCrmentity->description ?? '';
            $newCrmentity->createdtime = now();
            $newCrmentity->modifiedtime = now();
            $newCrmentity->viewedtime = now();
            $newCrmentity->status = $defaultCrmentity->status ?? '';
            $newCrmentity->version = $defaultCrmentity->version ?? 0;
            $newCrmentity->presence = $defaultCrmentity->presence ?? 0;
            $newCrmentity->deleted = $defaultCrmentity->deleted ?? 0;
            $newCrmentity->smgroupid = $defaultCrmentity->smgroupid ?? 0;
            $newCrmentity->source = $defaultCrmentity->source ?? '';
            $newCrmentity->label = $validatedData['first_name'];
            $newCrmentity->save();
    
            // Set the new crmid as the employee ID
            $validatedData['id'] = $newCrmentity->crmid;
    
            // Create the employee entry
            $employee = Employee::create($validatedData);
    
            DB::commit();
    
            return response()->json(['message' => 'Employee created successfully', 'employee' => $employee], 201);
    
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation failed while creating employee: ' . $e->getMessage());
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create employee: ' . $e->getMessage());
            Log::error($e->getTraceAsString()); // Log the stack trace for detailed debugging
            return response()->json(['error' => 'Failed to create employee: ' . $e->getMessage()], 500);
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

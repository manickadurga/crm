<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Validation\ValidationException;
use App\Models\Employee;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Inventoryproductrel;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tags;
use App\Models\Customers;
use App\Models\Tasks;
use App\Models\Crmentity;
use App\Models\Leads;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EstimateController extends Controller
{

    public function index(Request $request)
    {
        try {
            // Set the number of items per page, default is 10
            $perPage = $request->input('per_page', 10);
    
            // Get paginated estimates with specific fields including 'id'
            $estimates = Estimate::select('id', 'estimatenumber', 'contacts', 'estimatedate', 'duedate', 'discount', 'total', 'tax1', 'tax2', 'estimate_status')
                ->paginate($perPage);
    
            // Prepare array to hold formatted estimates
            $formattedEstimates = [];
    
            // Iterate through each estimate to format data
            foreach ($estimates as $estimate) {
                $contactName = null;
    
                // Attempt to find the contact name in each table
                if ($contact = Customers::find($estimate->contacts)) {
                    $contactName = $contact->name;
                } elseif ($contact = Clients::find($estimate->contacts)) {
                    $contactName = $contact->name;
                } elseif ($contact = Leads::find($estimate->contacts)) {
                    $contactName = $contact->name;
                }
    
                // Build formatted estimate array and embed 'id'
                $formattedEstimates[] = [
                    'id' => $estimate->id,
                    'estimatenumber' => $estimate->estimatenumber,
                    'estimatedate' => $estimate->estimatedate,
                    'duedate' => $estimate->duedate,
                    'contacts' => $contactName, // Embed the contact name
                    'discount' => $estimate->discount,
                    'total' => $estimate->total,
                    'tax1' => $estimate->tax1,
                    'tax2' => $estimate->tax2,
                    'Status' => $estimate->estimate_status,
                ];
            }
    
            // Return JSON response with formatted data and pagination information
            return response()->json([
                'status' => 200,
                'estimates' => $formattedEstimates,
                'pagination' => [
                    'total' => $estimates->total(),
                    'per_page' => $estimates->perPage(),
                    'current_page' => $estimates->currentPage(),
                    'last_page' => $estimates->lastPage(),
                    'from' => $estimates->firstItem(),
                    'to' => $estimates->lastItem(),
                ],
            ], 200);
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve estimates: ' . $e->getMessage());
    
            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve estimates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'estimatenumber' => 'required|numeric',
                'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                    // Check if the contact ID exists in any of the specified tables
                    $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                    $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                    $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();
    
                    if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                        $fail("The selected contact ID does not exist in any of the specified tables.");
                    }
                }],// Contact ID
                'estimatedate' => 'required|date',
                'duedate' => 'required|date',
                'discount' => 'required|string',
                'discount_suffix' => 'nullable|string|in:%,"flat"',
                'currency' => 'required|string',
                'terms' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'tax1' => 'nullable|numeric',
                'tax2' => 'nullable|numeric',
                'applydiscount' => 'boolean',
                'taxtype' => 'nullable|string',
                'subtotal' => 'nullable|numeric',
                'total' => 'nullable|numeric',
                'tax_percent' => 'nullable|numeric',
                'discount_percent' => 'nullable|numeric',
                'tax_amount' => 'nullable|numeric',
                'estimate_status' => 'nullable|string',
                'organization_name' => 'required|numeric|exists:jo_organizations,id', // Ensure organization exists
            ])->validate();
            if (isset($validatedData['tags'])) {
                $validatedData['tags'] = json_encode($validatedData['tags']);
            }
            $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Estimates', $validatedData['estimatenumber']);

        // Create the customer with the crmid
        $validatedData['id'] = $crmid; 
            $estimate = Estimate::create($validatedData);
            $estimate->tags = json_decode($estimate->tags);
    
            // Return success response
            return response()->json([
                'status' => 200,
                'message' => 'Estimate added successfully',
                'estimate' => $estimate,
            ], 200);
    
        } catch (\Exception $e) {
            // Rollback transaction if any exception occurs
            DB::rollBack();
    
            // Return error response if something goes wrong
            return response()->json([
                'status' => 500,
                'message' => 'Estimate addition failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            $estimate = Estimate::findOrFail($id);
            return response()->json($estimate);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Estimate not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
    public function update(Request $request, int $id)
    {
        DB::beginTransaction();
    
        try {
            // Validate the request data
            $validatedData = Validator::make($request->all(), [
                'estimatenumber' => 'nullable|numeric',
                'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                    // Check if the contact ID exists in any of the specified tables
                    $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                    $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                    $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();
    
                    if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                        $fail("The selected contact ID does not exist in any of the specified tables.");
                    }
                }], // Contact ID  // Ensure contacts is a valid customer ID
                'estimatedate' => 'nullable|date',
                'duedate' => 'nullable|date',
                'discount' => 'nullable|string',
                'discount_suffix' => 'nullable|string|in:%,"flat"',
                'currency' => 'nullable|string',
                'terms' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:jo_tags,id',
                'tax1' => 'nullable|numeric',
                'tax2' => 'nullable|numeric',
                'applydiscount' => 'nullable|boolean',
                'taxtype' => 'nullable|string',
                'subtotal' => 'nullable|numeric',
                'total' => 'nullable|numeric',
                'tax_percent' => 'nullable|numeric',
                'discount_percent' => 'nullable|numeric',
                'tax_amount' => 'nullable|numeric',
                'estimate_status' => 'nullable|string',
                'organization_name' => 'nullable|exists:jo_organizations,id', // Ensure organization exists
            ])->validate();
    
            // Find the estimate by ID
            $estimate = Estimate::findOrFail($id);
    
            // Convert tags array to JSON string
            if (isset($validatedData['tags'])) {
                $validatedData['tags'] = json_encode($validatedData['tags']);
            }
    
            // Update the estimate fields based on the request data
            $estimate->fill($validatedData);
            $estimate->save();
    
            // Find or create the corresponding Crmentity record
            $crmentity = Crmentity::where('crmid', $id)->where('setype', 'Estimates')->first();
    
            if ($crmentity) {
                // Update existing Crmentity record
                $crmentity->update([
                    'label' => $validatedData['estimatenumber'], // Use the appropriate field for the label
                    'modifiedtime' => now(),
                    'status' => $validatedData['estimate_status'] ?? $crmentity->status, // Update status if provided
                ]);
            } else {
                // Create a new Crmentity record if not found
                Crmentity::create([
                    'crmid' => $id,
                    'setype' => 'Estimates',
                    'label' => $validatedData['estimatenumber'], // Use the appropriate field for the label
                    'createdtime' => now(),
                    'modifiedtime' => now(),
                    'status' => $validatedData['estimate_status'] ?? 'Pending', // Default status
                    'createdby' => auth()->id(), // Assuming you have authentication setup
                    'modifiedby' => auth()->id(),
                ]);
            }
    
            DB::commit();
    
            // Decode tags back to array for response
            $estimate->tags = json_decode($estimate->tags);
    
            return response()->json([
                'status' => 200,
                'message' => 'Estimate updated successfully',
                'estimate' => $estimate,
            ], 200);
    
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            return response()->json([
                'status' => 404,
                'message' => 'Estimate not found'
            ], 404);
    
        } catch (ValidationException $ex) {
            DB::rollBack();
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed',
                'errors' => $ex->errors()
            ], 422);
    
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Failed to update estimate and Crmentity: ' . $ex->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while updating the estimate',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
    

    /**
     * Remove the specified estimate from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $estimate = Estimate::findOrFail($id);
            $estimate->delete();
            return response()->json(['message' => 'Estimate deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Estimate not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete estimate', 'message' => $e->getMessage()], 500);
        }
    }
    public function search(Request $request)
{
    try {
        // Get search parameters from the request
        $searchTerm = $request->input('q', '');
        $perPage = $request->input('per_page', 10);

        // Build the query
        $query = Estimate::query();

        // Apply filters based on search terms
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('estimatenumber', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('contacts', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('estimatedate', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('duedate', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('discount', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('total', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('status', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Paginate the results
        $estimates = $query->paginate($perPage);

        // Prepare array to hold formatted estimates
        $formattedEstimates = [];

        // Iterate through each estimate to format data
        foreach ($estimates as $estimate) {
            $formattedEstimates[] = [
                'id' => $estimate->id,
                'estimatenumber' => $estimate->estimatenumber,
                'estimatedate' => $estimate->estimatedate,
                'duedate' => $estimate->duedate,
                'contacts' => $estimate->contacts,
                'discount' => $estimate->discount,
                'total' => $estimate->total,
                'tax1' => $estimate->tax1,
                'tax2' => $estimate->tax2,
                'estimate_status' => $estimate->status,
            ];
        }

        // Return JSON response with formatted data and pagination information
        return response()->json([
            'status' => 200,
            'estimates' => $formattedEstimates,
            'pagination' => [
                'total' => $estimates->total(),
                'per_page' => $estimates->perPage(),
                'current_page' => $estimates->currentPage(),
                'last_page' => $estimates->lastPage(),
                'from' => $estimates->firstItem(),
                'to' => $estimates->lastItem(),
            ],
        ], 200);
    } catch (\Exception $e) {
        // Log the error
        Log::error('Failed to search estimates: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'status' => 500,
            'message' => 'Failed to search estimates',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function fetchData(Request $request) {
        try {
    
            // $value = $request->query('value', 'employee');
            // Define initial options array
            $options = [
                ['value' => 'employees', 'label' => 'Employee'],
                ['value' => 'projects', 'label' => 'Projects'],
                ['value' => 'tasks', 'label' => 'Tasks'],
                ['value' => 'products', 'label' => 'Products'],
                ['value' => 'expenses', 'label' => 'Expenses'],
            ];

            $value = 'employees';
    
            if ($value === 'tasks') {
              
                $tasks = Tasks::select('id', 'title')->get();
    
                $newOptions = $tasks->map(function ($task) {
                    return [
                        'value' => $task->id,
                        'label' => $task->title
                    ];
                });
    
                return response()->json([
                    'status' => 200,
                    'options' => $newOptions
                ]);
            } elseif ($value === 'products') {
    
                $product = Product::select('id', 'name')->get();
                $newOptions = [];
               
                $products = Product::query()->get();
                foreach ($products as $product) {
                    $newOptions[] = [
                        'value' => $product->id,
                        'label' => $product->name 
                    ];
                }
                return response()->json([
                    'status' => 200,
                    'options' => $newOptions
                ]);
            }elseif ($value === 'employees') {
    
                $employees = Employee::select('id', 'firstname','lastname')->get();
                $newOptions = [];
               
                $employees = Employee::query()->get();
                foreach ($employees as $employee) {
                    $newOptions[] = [
                        'value' => $employee->id,
                        'label' => trim($employee->firstname . ' ' . $employee->lastname)
                    ];
                }
                return response()->json([
                    'status' => 200,
                    'options' => $newOptions
                ]);
            }elseif($value === 'projects'){
                $projects = Project::select('id','projects')->get();
                $newOptions=[];
    
                $projects = Project::query()->get();
                foreach($projects as $project){
                    $newOptions[] = [
                        'value' => $project->id,
                        'label'=>$project->projects
                    ];
                }
                return response()->json([
                    'status' => 200,
                    'options' => $newOptions
                ]);
            }elseif($value === 'expenses'){
                $expenses = Expense::select('id','expense')->get();
                $newOptions=[];
    
                $expenses = Expense::query()->get();
                foreach($expenses as $expense){
                    $newOptions[] = [
                        'value' => $expense->id,
                        'label' => $expense->expense
                    ];
    
                }
                return response()->json([
                    'status' => 200,
                    'options' => $newOptions
                ]);
            }
    
            else {
               
                throw new \Exception('Invalid value provided.');
            }
        } catch (\Exception $e) {
           
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }
    public function getDetails($type, $value)
{
    try {
        // Validate the ID
        $validator = Validator::make(['value' => $value], [
            'value' => 'required|integer'
        ]);

        if ($validator->fails()) {
            throw new \Exception('Invalid ID');
        }

        // Fetch the details based on the type
        if ($type === 'tasks') {
            $item = Tasks::find($value);
            if ($item) {
                return response()->json([
                    'status' => 200,
                    'task' => [
                        'id' => $item->id,
                        'title' => $item->title,
                        'description' => $item->description ?? ''
                    ]
                ]);
            } else {
                throw new \Exception('Task not found');
            }
        } elseif ($type === 'products') {
            $item = Product::find($value);
            if ($item) {
                return response()->json([
                    'status' => 200,
                    'product' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description ?? '',
                        'list_price' => $item->list_price,
                        'quantity' => $item->quantity
                    ]
                ]);
            } else {
                throw new \Exception('Product not found');
            }
        }elseif($type === 'employees'){
            $item = Employee::find($value);
            $Employee = $item->firstname . ' ' . $item->lastname;
            if($item) {
                return response()->json([
                    'status' => 200,
                    'employee'=>[
                        'id' => $item->id,
                        'employee'=>$Employee,
                        'description'=>$item->description,
                        'quantity'=>$item->quantity
                    ]
                    ]);
            }else{
                throw new \Exception('Employee not found');
            }
        }elseif($type === 'projects'){
            $item = Project::find($value);
            if($item){
                return response()->json([
                    'status' => 200,
                    'projects'=>[
                        'id'=>$item->id,
                        'projects'=>$item->projects,
                        'description'=>$item->description
                    ]
                ]);
            }else{
                throw new \Exception('Projects not found');
            }
        }elseif($type === 'expenses'){
            $item = Expense::find($value);
            if($item){
                return response()->json([
                    'status' => 200,
                    'expenses'=>[
                        'id'=>$item->id,
                        'expenses'=>$item->expense,
                        'description'=>$item->description
                    ]
                ]);
            }else{
                throw new Exception('Expenses not found');
            }
        }
         else {
            throw new \Exception('Invalid type specified');
        }
    } catch (\Exception $e) {
        // Handle exceptions here, you can log the error or return an error response
        return response()->json([
            'status' => 404,
            'error' => $e->getMessage()
        ], 404);
    }
}
public function addTasks(Request $request, $id)
{
    try {
        // Validate the request
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.task_id' => 'required|exists:jo_tasks,id',
            'tasks.*.quantity' => 'required|integer|min:1',
            'tasks.*.description'=>'nullable|string',
            'tasks.*.list_price'=>'required|integer'
        ]);

        // Fetch the invoice
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'estimate not found'
            ]);
        }

        // Process each employee/product entry
        foreach ($request->tasks as $taskData) {
            // Create a new inventory product relationship record
            $inventoryProductRel = new InventoryProductRel();

            // Populate inventory product relationship fields
            $inventoryProductRel->id = $estimate->id; // Link to the invoice ID
            $inventoryProductRel->product_id = $taskData['task_id'];
            $inventoryProductRel->quantity = $taskData['quantity'];
            $inventoryProductRel->description=$taskData['description'];
            $inventoryProductRel->list_price=$taskData['list_price'];

            // Save the inventory record
            $inventoryProductRel->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Task added successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }

}
public function addProducts(Request $request, $id)
{
    try {
        // Validate the request
        $request->validate([
            'product' => 'required|array',
            'product.*.product_id' => 'required|exists:jo_products,id',
            'product.*.quantity' => 'required|integer|min:1',
            'product.*.discount_percent' => 'required|numeric|between:0,100',
            'product.*.list_price' => 'required|numeric|min:0',
            'product.*.description'=>'nullable|string',
            'taxtype' => 'required|string|in:individual,group',
            'tax_percent' => 'required_if:taxtype,individual,group|numeric|between:0,100',
           
        ]);
        
        // Fetch the estimate
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'Estimate not found'
            ]);
        }

        // Initialize subtotal and discount amounts
        $subtotal = $estimate->subtotal ?? 0;
        $discountAmount = 0;

        // Process each product in the request
        foreach ($request->product as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $discountPercent = $item['discount_percent'];
            $listprice=$item['list_price'];
            $description=$item['description'];

            // Fetch the product from the products table
            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Product with ID ' . $productId . ' not found'
                ]);
            }

            // Calculate line total for this product
            $lineTotal = $listprice * $quantity;

            // Calculate discount amount and subtract it from line total
            $discountAmount += ($lineTotal * $discountPercent) / 100;
            $lineTotal -= ($lineTotal * $discountPercent) / 100;

            // Increment the current subtotal by the line total
            $subtotal += $lineTotal;

            // Create a new inventory product relationship record
            $inventoryProductRel = new Inventoryproductrel();

            // Populate inventory product relationship fields
            $inventoryProductRel->id = $estimate->id; // Link to the estimate ID
            $inventoryProductRel->product_id = $product->id;
            $inventoryProductRel->quantity = $quantity; // Set quantity from request
            $inventoryProductRel->list_price = $listprice;
            $inventoryProductRel->description = $description;
            $inventoryProductRel->discount_percent = $discountPercent; // Set discount percentage

            // Save the inventory record
            $inventoryProductRel->save();
        }

        // Update the estimate subtotal with the accumulated total
        $estimate->subtotal = $subtotal;

        // Calculate total discount percent for the invoice based on total discount amount
        $totalInvoiceAmount = $estimate->subtotal + $discountAmount;
        $invoiceDiscountPercent = ($discountAmount / $totalInvoiceAmount) * 100;

        // Update the invoice discount_percent field
        $estimate->discount_percent = $invoiceDiscountPercent;

        // Calculate tax based on the tax type
        $taxAmount = 0;
        if ($request->taxtype === 'individual' || $request->taxtype === 'group') {
            $taxPercent = $request->tax_percent;
            $taxAmount = ($estimate->subtotal * $taxPercent) / 100;
        }

        // Update the estimate tax amount and tax fields
        $estimate->tax_amount = $taxAmount;
        $estimate->taxtype = $request->taxtype;
        $estimate->tax_percent = $request->tax_percent ?? 0; // Set tax_percent, default to 0 if not provided

        // Calculate total including tax (discount is already applied in subtotal)
        $estimate->total = $estimate->subtotal + $taxAmount;

        // Save the updated estimate
        $estimate->save();

        return response()->json([
            'status' => 200,
            'message' => 'Products added to invoice successfully',
            'subtotal' => $estimate->subtotal,
            'tax_amount' => $estimate->tax_amount,
            'total' => $estimate->total,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}
public function addEmpProducts(Request $request, $id)
{
    try {
        // Validate the request
        $request->validate([
            'employee' => 'required|array',
            'employee.*.emp_id' => 'required|exists:jo_employees,id',
            'employee.*.quantity' => 'required|integer|min:1',
            'employee.*.list_price'=>'required|integer',
            'employee.*.description'=>'nullable|string'
        ]);

        // Fetch the invoice
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'estimate not found'
            ]);
        }

        // Process each employee/product entry
        foreach ($request->employee as $empData) {
            // Create a new inventory product relationship record
            $inventoryProductRel = new InventoryProductRel();

            // Populate inventory product relationship fields
            $inventoryProductRel->id = $estimate->id; // Link to the estimate ID
            $inventoryProductRel->product_id = $empData['emp_id'];
            $inventoryProductRel->quantity = $empData['quantity'];
            $inventoryProductRel->list_price=$empData['list_price'];
            $inventoryProductRel->description=$empData['description'] ?? null;


            // Save the inventory record
            $inventoryProductRel->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Employee added successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}

public function addProjects(Request $request, $id)
{
    try {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'projects' => 'required|array',
            'projects.*.project_id' => 'required|exists:jo_projects,id',
            'projects.*.list_price' => 'required|numeric|min:0',
            'projects.*.description' => 'nullable|string',
            'projects.*.quantity' => 'required|integer|min:1',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()->all()
            ]);
        }

        // Fetch the estimate
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'Estimate not found'
            ]);
        }

        // Process each project entry
        foreach ($request->projects as $proData) {
            // Create a new inventory product relationship record
            $inventoryProductRel = new InventoryProductRel();

            // Populate inventory product relationship fields
            $inventoryProductRel->id = $estimate->id; // Link to the estimate ID
            $inventoryProductRel->product_id = $proData['project_id'];
            $inventoryProductRel->description = $proData['description'] ?? null; // Handle nullable description
            $inventoryProductRel->quantity = $proData['quantity'];
            $inventoryProductRel->list_price = $proData['list_price'];

            // Save the inventory record
            $inventoryProductRel->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Projects added successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}


public function addExpenses(Request $request, $id)
{
    try {
        // Validate the request
        $request->validate([
            'expenses' => 'required|array',
            'expenses.*.expense_id' => 'required|exists:jo_expenses,id',
            'expenses.*.quantity' => 'required|integer|min:1',
            'expenses.*.list_price' => 'required|numeric|min:0',
            'expenses.*.discount_percent' => 'required|numeric|between:0,100',
            'expenses.*.description' => 'nullable|string',
        ]);

        // Fetch the estimate
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'Estimate not found'
            ]);
        }

        // Initialize subtotal and discount amounts
        $subtotal = $estimate->subtotal ?? 0;
        $discountAmount = 0;

        // Process each expense entry
        foreach ($request->expenses as $expData) {
            $expenseId = $expData['expense_id'];
            $quantity = $expData['quantity'];
            $listPrice = $expData['list_price'];
            $discountPercent = $expData['discount_percent'];
            $description = $expData['description'];

            // Fetch the expense from the expenses table
            $expense = Expense::find($expenseId);

            if (!$expense) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Expense with ID ' . $expenseId . ' not found'
                ]);
            }

            // Calculate line total for this expense
            $lineTotal = $listPrice * $quantity;

            // Calculate discount amount and subtract it from line total
            $discountAmount += ($lineTotal * $discountPercent) / 100;
            $lineTotal -= ($lineTotal * $discountPercent) / 100;

            // Increment the current subtotal by the line total
            $subtotal += $lineTotal;

            // Create a new inventory product relationship record
            $inventoryProductRel = new Inventoryproductrel();

            // Populate inventory product relationship fields
            $inventoryProductRel->id = $estimate->id; // Link to the estimate ID
            $inventoryProductRel->product_id = $expense->id;
            $inventoryProductRel->quantity = $quantity; // Set quantity from request
            $inventoryProductRel->list_price = $listPrice;
            $inventoryProductRel->description = $description;
            $inventoryProductRel->discount_percent = $discountPercent; // Set discount percentage

            // Save the inventory record
            $inventoryProductRel->save();
        }

        // Update the estimate subtotal with the accumulated total
        $estimate->subtotal = $subtotal;

        // Calculate total discount percent for the invoice based on total discount amount
        $totalInvoiceAmount = $estimate->subtotal + $discountAmount;
        $invoiceDiscountPercent = ($discountAmount / $totalInvoiceAmount) * 100;

        // Update the invoice discount_percent field
        $estimate->discount_percent = $invoiceDiscountPercent;

        // Calculate tax based on the tax type
        $taxAmount = 0;
        if ($request->taxtype === 'individual' || $request->taxtype === 'group') {
            $taxPercent = $request->tax_percent;
            $taxAmount = ($estimate->subtotal * $taxPercent) / 100;
        }

        // Update the estimate tax amount and tax fields
        $estimate->tax_amount = $taxAmount;
        $estimate->taxtype = $request->taxtype;
        $estimate->tax_percent = $request->tax_percent ?? 0; // Set tax_percent, default to 0 if not provided

        // Calculate total including tax (discount is already applied in subtotal)
        $estimate->total = $estimate->subtotal + $taxAmount;

        // Save the updated estimate
        $estimate->save();

        return response()->json([
            'status' => 200,
            'message' => 'Expenses added to estimate successfully',
            'subtotal' => $estimate->subtotal,
            'tax_amount' => $estimate->tax_amount,
            'total' => $estimate->total,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}

public function getTasks($id)
{
    try {
        // Fetch the invoice
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'estimate not found'
            ]);
        }

        // Fetch the employee-product relationships for this invoice
        $tasks = InventoryProductRel::where('id', $id)->get(); // Adjust the column name if necessary

        if ($tasks->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No Projects found for this estimate'
            ]);
        }

        // Fetch the projects from jo_project table
        $jotasks = Tasks::where('id', $id)->get(); // Adjust the column name if necessary

        if ($jotasks->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No joProjects found for this estimate'
            ]);
        }

        // Prepare the response data for InventoryProductRel
        $inventoryProductResponse = [];
        foreach ($tasks as $task) {
            $inventoryProductResponse[] = [
                'task_id' => $task->product_id,
                'quantity' => $task->quantity,
            ];
        }

        // Prepare the response data for joProjects
        $joTaskResponse = [];
        foreach ($jotasks as $jotask) {
            $joTaskResponse[] = [
                'project_id' => $jotask->id,
                // 'name' => $jotask->projects,
                'description' => $jotask->description
                // Add any other necessary fields from jo_project table
            ];
        }

        return response()->json([
            'status' => 200,
            'data' => [
                'inventoryProducts' => $inventoryProductResponse,
                'joProjects' => $joTaskResponse
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching projects', ['exception' => $e]);
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}


public function getProductDetails($id)
{
    try {
        // Fetch the estimate by ID
        $estimate = Estimate::find($id);

        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'estimate not found'
            ]);
        }

        // Return the estimate details
        return response()->json([
            'status' => 200,
            'estimate' => $estimate,
            'subtotal' => $estimate->subtotal,
            'tax_amount' => $estimate->tax_amount,
            'total' => $estimate->total,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}

public function getEmpProducts($id)
{
    try {
        // Fetch the invoice
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'estimate not found'
            ]);
        }

        // Fetch the employee-product relationships for this invoice
        $employeeProducts = InventoryProductRel::where('id', $id)->get();

        if ($employeeProducts->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No employee products found for this estimate'
            ]);
        }

        // Prepare the response data with employee names
        $responseData = [];
        foreach ($employeeProducts as $employeeProduct) {
            // Fetch employee details from jo_employee table based on emp_id
            $employee = Employee::find($employeeProduct->id);
            
            if ($employee) {
                $employeeName = $employee->firstname . ' ' . $employee->lastname;
            } else {
                $employeeName = 'Unknown'; // Handle case where employee is not found
            }

            $responseData[] = [
                'emp_id' => $employeeProduct->product_id,
                'employee_name' => $employeeName,
                'quantity' => $employeeProduct->quantity
            ];
        }

        return response()->json([
            'status' => 200,
            'data' => $responseData
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching employee products', ['exception' => $e]);
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}
public function getProjects($id)
{
    try {
        // Fetch the invoice
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'estimate not found'
            ]);
        }

        // Fetch the employee-product relationships for this invoice
        $Projects = InventoryProductRel::where('id', $id)->get(); // Adjust the column name if necessary

        if ($Projects->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No Projects found for this estimate'
            ]);
        }

        // Fetch the projects from jo_project table
        $joProjects = Project::where('id', $id)->get(); // Adjust the column name if necessary

        if ($joProjects->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No joProjects found for this estimate'
            ]);
        }

        // Prepare the response data for InventoryProductRel
        $inventoryProductResponse = [];
        foreach ($Projects as $Project) {
            $inventoryProductResponse[] = [
                'pro_id' => $Project->product_id,
                'quantity' => $Project->quantity
            ];
        }

        // Prepare the response data for joProjects
        $joProjectResponse = [];
        foreach ($joProjects as $joProject) {
            $joProjectResponse[] = [
                'project_id' => $joProject->id,
                'name' => $joProject->projects,
                'description' => $joProject->description
                // Add any other necessary fields from jo_project table
            ];
        }

        return response()->json([
            'status' => 200,
            'data' => [
                'inventoryProducts' => $inventoryProductResponse,
                'joProjects' => $joProjectResponse
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching projects', ['exception' => $e]);
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}
public function getExpenses($id)
{
    try {
        // Fetch the invoice
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json([
                'status' => 404,
                'message' => 'estimate not found'
            ]);
        }

        // Fetch expenses_id and quantity related to the invoice
        $expenses = InventoryProductRel::where('id', $id)->get();

        $inventoryProductResponse = [];
        foreach ($expenses as $Expense) {
            $inventoryProductResponse[] = [
                'expenses_id' => $Expense->product_id,
                'quantity' => $Expense->quantity
            ];
        }

        return response()->json([
            'status' => 200,
            'expenses' => $inventoryProductResponse
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}

public function downloadEstimate($id)
{
    try {
        // Retrieve the estimate
        $estimate = Estimate::findOrFail($id);

        // Retrieve the organization name from jo_organizations using the estimate's organization_id
        $organization = DB::table('jo_organizations')->where('id', $estimate->organization_name)->first();
        if (!$organization) {
            return response()->json([
                'status' => 404,
                'message' => 'Organization not found'
            ], 404);
        }

        // Retrieve the contact name using the contacts_id stored in the estimate
        $contactId = $estimate->contacts;
        $contactName = null;

        // Check jo_customers for contact name
        $customer = DB::table('jo_customers')->where('id', $contactId)->first();
        if ($customer) {
            $contactName = $customer->name;
        } else {
            // Check jo_leads for contact name if not found in jo_customers
            $lead = DB::table('jo_leads')->where('id', $contactId)->first();
            if ($lead) {
                $contactName = $lead->name;
            } else {
                // Check jo_clients for contact name if not found in jo_leads
                $client = DB::table('jo_clients')->where('id', $contactId)->first();
                if ($client) {
                    $contactName = $client->name;
                } else {
                    // If contact not found in any table, return a 404 error response
                    return response()->json([
                        'status' => 404,
                        'message' => 'Contact not found'
                    ], 404);
                }
            }
        }

        // Retrieve the associated items and their details based on estimate ID
        $items = DB::table('jo_inventoryproductrel')
            ->leftJoin('jo_products', 'jo_inventoryproductrel.product_id', '=', 'jo_products.id')
            ->leftJoin('jo_tasks', 'jo_inventoryproductrel.product_id', '=', 'jo_tasks.id')
            ->leftJoin('jo_projects', 'jo_inventoryproductrel.product_id', '=', 'jo_projects.id')
            ->leftJoin('jo_employees', 'jo_inventoryproductrel.product_id', '=', 'jo_employees.id')
            ->leftJoin('jo_expenses', 'jo_inventoryproductrel.product_id', '=', 'jo_expenses.id')
            ->where('jo_inventoryproductrel.id', $id) // Match the estimate ID
            ->select(
                'jo_inventoryproductrel.list_price',
                'jo_inventoryproductrel.quantity',
                'jo_inventoryproductrel.product_id',
                'jo_inventoryproductrel.description',
                'jo_products.name as product_name',
                'jo_tasks.title as task_title',
                'jo_projects.project_name as project_name',
                'jo_employees.first_name as employee_firstname',
                'jo_expenses.amount as expense_amount'
            )
            ->get();

        // Build the HTML content
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Estimate</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .container {
                    display: flex;
                    justify-content: space-between;
                }
                .left {
                    text-align: left;
                }
                .right {
                    text-align: right;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='left'>
                    <p><b>FROM:</b></p>
                    <p>{$organization->organization_name}</p> <!-- Use organization_name -->
                </div>
                <div class='right'>
                    <h3>Estimate Number: {$estimate->estimatenumber}</h3>
                    <p>Estimate Date: {$estimate->estimatedate}</p>
                    <p>Due Date: {$estimate->duedate}</p>
                    <p>Currency: {$estimate->currency}</p>
                </div>
            </div>
            <p><b>TO:</b></p>
            <p>{$contactName}</p> <!-- Display contact name -->
            <table>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>";

        // Initialize total value for the estimate
        $totalValue = 0;

        // Append item data to the HTML content
        foreach ($items as $item) {
            $total = $item->quantity * $item->list_price;
            $totalValue += $total; // Add to the estimate total value

            // Determine the item type and details
            $itemDetails = '';
            if (!is_null($item->product_name)) {
                $itemDetails = "Product: {$item->product_name}";
            } elseif (!is_null($item->task_title)) {
                $itemDetails = "Task: {$item->task_title}";
            } elseif (!is_null($item->project_name)) {
                $itemDetails = "Project: {$item->project_name}";
            } elseif (!is_null($item->employee_firstname)) {
                $itemDetails = "Employee: {$item->employee_firstname}";
            } elseif (!is_null($item->expense_amount)) {
                $itemDetails = "Expense: {$item->expense_amount}";
            }

            $htmlContent .= "
                <tr>
                    <td>{$itemDetails}</td>
                    <td>" . ($item->description ?? '') . "</td>
                    <td>{$item->quantity}</td>
                    <td>{$item->list_price}</td>
                    <td>{$total}</td>
                </tr>";
        }

        $htmlContent .= "
            </table>
            <div class='right'>
                <p><b>Tax Value:</b> {$estimate->tax1}</p>
                <p><b>Tax Value 2:</b> {$estimate->tax2}</p>
                <p><b>Discount Value:</b> {$estimate->discount}</p>
                <p><b>Total Value:</b> {$totalValue}</p>
            </div>
            <p><b>Terms:</b></p>
            <p>{$estimate->terms}</p>
        </body>
        </html>";

        // Generate and download the PDF
        $pdf = Pdf::loadHTML($htmlContent);
        return $pdf->download('estimate_' . $estimate->id . '.pdf');
    } catch (\Exception $e) {
        // Log the error message
        Log::error('Failed to generate estimate PDF', ['error' => $e->getMessage()]);

        // Return error response if something goes wrong
        return response()->json([
            'status' => 500,
            'message' => 'Failed to generate estimate PDF',
            'error' => $e->getMessage()
        ], 500);
    }
}


}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use App\Models\Customers;
use App\Models\Employee;
use App\Models\Expense;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Inventoryproductrel;
use App\Models\Invoices;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tags;
use App\Models\Tasks;
use App\Models\Crmentity;
use App\Models\Leads;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Set the number of items per page, default is 10
            $perPage = $request->input('per_page', 10);
    
            // Get paginated invoices with specific fields including 'id'
            $invoices = Invoices::select('id', 'invoicenumber', 'contacts', 'invoicedate', 'duedate', 'discount', 'total', 'tax1', 'tax2', 'invoice_status')
                ->paginate($perPage);
    
            // Prepare array to hold formatted invoices
            $formattedInvoices = [];
    
            // Iterate through each invoice to format data
            foreach ($invoices as $invoice) {
                $contactName = null;
    
                // Attempt to find the contact name in each table
                if ($contact = Customers::find($invoice->contacts)) {
                    $contactName = $contact->name;
                } elseif ($contact = Clients::find($invoice->contacts)) {
                    $contactName = $contact->name;
                } elseif ($contact = Leads::find($invoice->contacts)) {
                    $contactName = $contact->name;
                }
    
                // Build formatted invoice array and embed 'id'
                $formattedInvoices[] = [
                    'id' => $invoice->id,
                    'invoicenumber' => $invoice->invoicenumber,
                    'invoicedate' => $invoice->invoicedate,
                    'duedate' => $invoice->duedate,
                    'contacts' => $contactName, // Embed the contact name
                    'discount' => $invoice->discount,
                    'total' => $invoice->total,
                    'tax1' => $invoice->tax1,
                    'tax2' => $invoice->tax2,
                    'invoice_status' => $invoice->invoice_status,
                ];
            }
    
            // Return JSON response with formatted data and pagination information
            return response()->json([
                'status' => 200,
                'invoices' => $formattedInvoices,
                'pagination' => [
                    'total' => $invoices->total(),
                    'per_page' => $invoices->perPage(),
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ],
            ], 200);
        } catch (Exception $e) {
            // Log the error
            Log::error('Failed to retrieve invoices: ' . $e->getMessage());
    
            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve invoices',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'invoicenumber' => 'required|numeric',
            'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                // Check if the contact ID exists in any of the specified tables
                $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();

                if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                    $fail("The selected contact ID does not exist in any of the specified tables.");
                }
            }],
            'invoicedate' => 'required|date',
            'duedate' => 'required|date',
            'discount' => 'required|string',
            'discount_suffix' => 'nullable|string|in:%,"flat"',
            'currency' => 'required|string',
            'terms' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:jo_tags,id',
            'tax1' => 'nullable|numeric',
            'tax1_suffix' => 'nullable|string',
            'tax2' => 'nullable|numeric',
            'tax2_suffix' => 'nullable|string',
            'applydiscount' => 'boolean',
            'taxtype' => 'nullable|string',
            'subtotal' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'tax_percent' => 'nullable|numeric',
            'discount_percent' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'invoice_status' => 'required|string',
            'organization_name' => 'required|numeric|exists:jo_organizations,id',
        ]);

        // Create Crmentity record via CrmentityController
        $crmentityController = new CrmentityController();
        $crmid = $crmentityController->createCrmentity('Invoices', $validatedData['invoicenumber']);

        // Prepare invoice data including crmid
        $invoiceData = $validatedData;
        $invoiceData['id'] = $crmid;
        // Convert tags to JSON if they exist
        if (isset($invoiceData['tags'])) {
            $invoiceData['tags'] = json_encode($invoiceData['tags']);
        }

        // Create the invoice with the crmid
        $invoice = Invoices::create($invoiceData);

        DB::commit();

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Invoice added successfully',
            'invoice' => $invoice,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 500,
            'message' => 'Invoice addition failed',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function show($id)
{
    try {
        $invoice = Invoices::findOrFail($id);
        return response()->json($invoice);
    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Invoice not found'], 404);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Server Error'], 500);
    }
}
public function update(Request $request, $id)
{
    try {
        // Begin a database transaction
        DB::beginTransaction();

        // Find the invoice by ID or fail
        $invoice = Invoices::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'invoicenumber' => 'nullable|numeric',
            'contacts' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                // Check if the contact ID exists in any of the specified tables
                $existsInClients = DB::table('jo_clients')->where('id', $value)->exists();
                $existsInCustomers = DB::table('jo_customers')->where('id', $value)->exists();
                $existsInLeads = DB::table('jo_leads')->where('id', $value)->exists();

                if (!$existsInClients && !$existsInCustomers && !$existsInLeads) {
                    $fail("The selected contact ID does not exist in any of the specified tables.");
                }
            }],
            'invoicedate' => 'nullable|date',
            'duedate' => 'nullable|date',
            'discount' => 'nullable|string',
            'discount_suffix' => 'nullable|string|in:%,"flat"',
            'currency' => 'nullable|string',
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
            'invoice_status' => 'nullable|string',
            'organization_name' => 'nullable|exists:jo_organizations,id',
        ]);

        

        // Update invoice data
        $invoice->update($validatedData);

        // Find the related Crmentity record
        $crmentity = Crmentity::where('crmid', $invoice->id)->where('setype', 'Invoices')->first();

        if ($crmentity) {
            // Update the Crmentity record
            $crmentity->update([
                'label' => $validatedData['invoicenumber'] ?? $crmentity->label,
                //'status' => $validatedData['invoice_status'] ?? $crmentity->status,
            ]);
        } else {
            throw new Exception('Crmentity record not found.');
        }

        // Commit the transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Invoice and Crmentity updated successfully',
            'invoice' => $invoice,
            'crmentity' => $crmentity,
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        DB::rollBack();
        return response()->json([
            'status' => 404,
            'message' => 'Invoice not found'
        ], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'status' => 422,
            'message' => 'Validation error',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        // Log the error
        Log::error('Failed to update invoice: ' . $e->getMessage());

        return response()->json([
            'status' => 500,
            'message' => 'Invoice update failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function destroy($id)
    {
        try {
            $invoice = Invoices::findOrFail($id);
            $invoice->delete();
            return response()->json(['message' => 'Invoice deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete invoice', 'error' => $e->getMessage()], 500);
        }
    }
    public function search(Request $request)
{
    try {
        $searchTerm = $request->input('q', '');
        $perPage = $request->input('per_page', 10);
        $query = Invoices::query();
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('invoicenumber', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('contacts', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('invoicedate', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('duedate', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('discount', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('total', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('invoice_status', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Paginate the results
        $invoices = $query->paginate($perPage);

        // Prepare array to hold formatted invoices
        $formattedInvoices = [];
        foreach ($invoices as $invoice) {
            $formattedInvoices[] = [
                'id' => $invoice->id,
                'invoicenumber' => $invoice->invoicenumber,
                'invoicedate' => $invoice->invoicedate,
                'duedate' => $invoice->duedate,
                'contacts' => $invoice->contacts,
                'discount' => $invoice->discount,
                'total' => $invoice->total,
                'tax1' => $invoice->tax1,
                'tax2' => $invoice->tax2,
                'invoice_status' => $invoice->invoice_status,
            ];
        }

        // Return JSON response with formatted data and pagination information
        return response()->json([
            'status' => 200,
            'invoices' => $formattedInvoices,
            'pagination' => [
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'from' => $invoices->firstItem(),
                'to' => $invoices->lastItem(),
            ],
        ], 200);
    } catch (Exception $e) {
        // Log the error
        Log::error('Failed to search invoices: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'status' => 500,
            'message' => 'Failed to search invoices',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function fetchData(Request $request) 
{
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
        
        // $label = '';
        // foreach ($options as $option) {
        //     if ($option['value'] === $value) {
        //         $label = $option['label'];
        //         break;
        //     }
        // }
        $value = 'tasks';

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
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ]);
        }

        // Process each employee/product entry
        foreach ($request->tasks as $taskData) {
            // Create a new inventory product relationship record
            $inventoryProductRel = new InventoryProductRel();

            // Populate inventory product relationship fields
            $inventoryProductRel->id = $invoice->id; // Link to the invoice ID
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
            'taxtype' => 'required|string|in:individual,group',
            'tax_percent' => 'required_if:taxtype,individual,group|numeric|between:0,100',
            'product.*.description'=>'nullable|string'
        ]);
        
        // Fetch the invoice
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ]);
        }

        // Initialize subtotal and discount amounts
        $subtotal = $invoice->subtotal ?? 0;
        $discountAmount = 0;

        // Process each product in the request
        foreach ($request->product as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $discountPercent = $item['discount_percent'];
            $listprice = $item['list_price'];
            $description=$item['description'];

            // Fetch the product from the products table
            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Product with ID ' . $productId . ' not found'
                ]);
            }

            // Calculate line total before discount
            $lineTotal = $listprice * $quantity;

            // Calculate discount amount per item
            $discountAmountPerItem = ($listprice * $discountPercent) / 100;

            // Calculate discounted price per item
            $discountedPrice = $listprice - $discountAmountPerItem;

            // Calculate line total after discount
            $lineTotal = $discountedPrice * $quantity;

            // Increment the current subtotal by the line total
            $subtotal += $lineTotal;

            // Create a new inventory product relationship record
            $inventoryProductRel = new InventoryProductRel();

            // Populate inventory product relationship fields
            $inventoryProductRel->id = $invoice->id; // Link to the invoice ID
            $inventoryProductRel->product_id = $product->id;
            $inventoryProductRel->quantity = $quantity; // Set quantity from request
            $inventoryProductRel->list_price = $listprice;
            $inventoryProductRel->discount_percent = $discountPercent; // Set discount percentage
            $inventoryProductRel->description=$description;
            
            // Save the inventory record
            $inventoryProductRel->save();
        }

        // Update the invoice subtotal with the accumulated total
        $invoice->subtotal = $subtotal;

        // Calculate total discount amount
        $totalInvoiceAmount = $invoice->subtotal + $discountAmount;
        $invoiceDiscountPercent = ($discountAmount / $totalInvoiceAmount) * 100;

        // Update the invoice discount_percent field
        $invoice->discount_percent = $invoiceDiscountPercent;

        // Calculate tax based on the tax type
        $taxAmount = 0;
        if ($request->taxtype === 'individual' || $request->taxtype === 'group') {
            $taxPercent = $request->tax_percent;
            $taxAmount = ($invoice->subtotal * $taxPercent) / 100;
        }

        // Update the invoice tax amount and tax fields
        $invoice->tax_amount = $taxAmount;
        $invoice->taxtype = $request->taxtype;
        $invoice->tax_percent = $request->tax_percent ?? 0; // Set tax_percent, default to 0 if not provided

        // Calculate total including tax (discount is already applied in subtotal)
        $invoice->total = $invoice->subtotal + $taxAmount;

        // Save the updated invoice
        $invoice->save();
        
        return response()->json([
            'status' => 200,
            'message' => 'Products added to invoice successfully',
            'subtotal' => $invoice->subtotal,
            'tax_amount' => $invoice->tax_amount,
            'total' => $invoice->total,
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
        $validated = $request->validate([
            'employee' => 'required|array',
            'employee.*.emp_id' => 'required|exists:jo_employees,id',
            'employee.*.quantity' => 'required|integer|min:1',
            'employee.*.description'=>'nullable|string',
            'employee.*.list_price'=>'required|integer'
        ]);

        // Fetch the invoice
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ]);
        }

        // Use a transaction to ensure atomicity
        DB::beginTransaction();
        try {
            // Process each employee/product entry
            foreach ($validated['employee'] as $empData) {
                // Create a new inventory product relationship record
                $inventoryProductRel = new InventoryProductRel();

                // Populate inventory product relationship fields
                $inventoryProductRel->id = $invoice->id; // Correct foreign key
                $inventoryProductRel->product_id = $empData['emp_id']; // Use emp_id field for employee id
                $inventoryProductRel->quantity = $empData['quantity'];
                $inventoryProductRel->description=$empData['description'];
                $inventoryProductRel->list_price=$empData['list_price'];

                // Save the inventory record
                $inventoryProductRel->save();
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Employee products added successfully'
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'error' => 'Failed to add employee products: ' . $e->getMessage()
            ]);
        }
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 422,
            'errors' => $e->errors()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => 'An unexpected error occurred: ' . $e->getMessage()
        ]);
    }
}

public function addProjects(Request $request, $id)
{
    try {
        // Validate the request
        $request->validate([
            'projects' => 'required|array',
            'projects.*.pro_id' => 'required|exists:jo_projects,id',
            'projects.*.quantity' => 'required|integer|min:1',
            'projects.*.description'=>'nullable|string',
            'projects.*.list_price'=>'nullable|integer'
        ]);

        // Fetch the invoice
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ]);
        }

        // Process each employee/product entry
        foreach ($request->projects as $proData) {
            // Create a new inventory product relationship record
            $inventoryProductRel = new InventoryProductRel();

            // Populate inventory product relationship fields
            $inventoryProductRel->id = $invoice->id; // Link to the invoice ID
            $inventoryProductRel->product_id = $proData['pro_id'];
            $inventoryProductRel->list_price=$proData['list_price'];
            $inventoryProductRel->quantity = $proData['quantity'];
            $inventoryProductRel->description=$proData['description'] ?? null;

            // Save the inventory record
            $inventoryProductRel->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Project added successfully'
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
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ]);
        }

        // Fetch the employee-product relationships for this invoice
        $tasks = InventoryProductRel::where('id', $id)->get(); // Adjust the column name if necessary

        if ($tasks->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No Projects found for this invoice'
            ]);
        }

        // Fetch the projects from jo_project table
        $jotasks = Tasks::where('id', $id)->get(); // Adjust the column name if necessary

        if ($jotasks->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No joProjects found for this invoice'
            ]);
        }

        // Prepare the response data for InventoryProductRel
        $inventoryProductResponse = [];
        foreach ($tasks as $task) {
            $inventoryProductResponse[] = [
                'task_id' => $task->product_id,
                'quantity' => $task->quantity
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
public function addExpenses(Request $request, $id)
{
    try {
        // Validate the request
        $request->validate([
            'expenses' => 'required|array',
            'expenses.*.expense_id' => 'required|exists:jo_expenses,id',
            'expenses.*.quantity' => 'required|integer|min:1',
            'expenses.*.description' => 'nullable|string',
            'expenses.*.list_price' => 'required|numeric'
        ]);

        // Fetch the invoice
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ]);
        }

        // Process each expense entry
        foreach ($request->expenses as $expData) {
            // Fetch the expense from the expenses table
            $expense = Expense::find($expData['expense_id']);
            if (!$expense) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Expense with ID ' . $expData['expense_id'] . ' not found'
                ]);
            }

            // Create a new inventory product relationship record
            $inventoryProductRel = new InventoryProductRel();

            // Populate inventory product relationship fields
            $inventoryProductRel->id = $invoice->id; // Link to the invoice ID
            $inventoryProductRel->product_id = $expData['expense_id'];
            $inventoryProductRel->quantity = $expData['quantity'];
            $inventoryProductRel->description = $expData['description'] ?? null;
            $inventoryProductRel->list_price = $expData['list_price'];

            // Save the inventory record
            $inventoryProductRel->save();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Expenses added successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => $e->getMessage()
        ]);
    }
}


public function getProductDetails($id)
{
    try {
        // Fetch the invoice by ID
        $invoice = Invoices::find($id);

        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ]);
        }

        // Return the invoice details
        return response()->json([
            'status' => 200,
            'invoice' => $invoice,
            'subtotal' => $invoice->subtotal,
            'tax_amount' => $invoice->tax_amount,
            'total' => $invoice->total,
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
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ]);
        }

        // Fetch the employee-product relationships for this invoice
        $employeeProducts = InventoryProductRel::where('id', $id)->get();

        if ($employeeProducts->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No employee products found for this invoice'
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
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ]);
        }

        // Fetch the employee-product relationships for this invoice
        $Projects = InventoryProductRel::where('id', $id)->get(); // Adjust the column name if necessary

        if ($Projects->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No Projects found for this invoice'
            ]);
        }

        // Fetch the projects from jo_project table
        $joProjects = Project::where('id', $id)->get(); // Adjust the column name if necessary

        if ($joProjects->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No joProjects found for this invoice'
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
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
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
public function downloadInvoice($id)
{
    try {
        // Retrieve the invoice
        $invoice = Invoices::find($id);
        if (!$invoice) {
            return response()->json([
                'status' => 404,
                'message' => 'Invoice not found'
            ], 404);
        }
        
        // Retrieve the organization name from jo_organizations using the invoice's organization_id
        $organization = DB::table('jo_organizations')->where('id', $invoice->organization_name)->first();
        if (!$organization) {
            return response()->json([
                'status' => 404,
                'message' => 'Organization not found'
            ], 404);
        }

        // Retrieve the contact name using the contact_id stored in the invoice
        $contactId = $invoice->contacts;
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

        // Retrieve the associated items and their details based on invoice ID
        $items = DB::table('jo_inventoryproductrel')
            ->leftJoin('jo_projects', 'jo_inventoryproductrel.product_id', '=', 'jo_projects.id')
            ->leftJoin('jo_tasks', 'jo_inventoryproductrel.product_id', '=', 'jo_tasks.id')
            ->leftJoin('jo_products', 'jo_inventoryproductrel.product_id', '=', 'jo_products.id')
            ->leftJoin('jo_employees', 'jo_inventoryproductrel.product_id', '=', 'jo_employees.id')
            ->leftJoin('jo_expenses', 'jo_inventoryproductrel.product_id', '=', 'jo_expenses.id')
            ->where('jo_inventoryproductrel.id', $id) // Match the invoice ID
            ->select(
                'jo_inventoryproductrel.list_price',
                'jo_inventoryproductrel.quantity',
                'jo_inventoryproductrel.description',
                'jo_projects.project_name as project_name',
                'jo_tasks.title as task_title',
                'jo_products.name as product_name',
                'jo_employees.first_name as employee_firstname',
                'jo_expenses.amount as expense_amount'
            )
            ->get();

        // Build the HTML content for the invoice
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { display: flex; justify-content: space-between; }
                .left { text-align: left; }
                .right { text-align: right; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='left'>
                    <p><b>FROM:</b></p>
                    <p>{$organization->organization_name}</p> <!-- Use organization_name -->
                </div>
                <div class='right'>
                    <h3>Invoice Number: {$invoice->invoicenumber}</h3>
                    <p>Invoice Date: {$invoice->invoicedate}</p>
                    <p>Due Date: {$invoice->duedate}</p>
                    <p>Currency: {$invoice->currency}</p>
                </div>
            </div>
            <p><b>TO:</b></p>
            <p>{$contactName}</p> <!-- Display contact name -->
            <table>
                <tr>
                    <th>Item Details</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>List Price</th>
                    <th>Total Value</th>
                </tr>";

        // Initialize total value for the invoice
        $totalValue = 0;

        // Append item data to the HTML content
        foreach ($items as $item) {
            $total = $item->quantity * $item->list_price;
            $totalValue += $total; // Add to the invoice total value

            // Determine the item details prioritizing projects and falling back to other item types
            $itemDetails = "";
            if (!is_null($item->project_name)) {
                $itemDetails = "Project: {$item->project_name}";
            } elseif (!is_null($item->task_title)) {
                $itemDetails = "Task: {$item->task_title}";
            } elseif (!is_null($item->product_name)) {
                $itemDetails = "Product: {$item->product_name}";
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
                <p><b>Tax Value:</b> {$invoice->tax1}</p>
                <p><b>Tax Value 2:</b> {$invoice->tax2}</p>
                <p><b>Discount Value:</b> {$invoice->discount}</p>
                <p><b>Total Value:</b> {$totalValue}</p>
            </div>
        </body>
        </html>";

        // Generate and download the PDF
        $pdf = PDF::loadHTML($htmlContent);
        return $pdf->download('invoice_' . $invoice->id . '.pdf');
    } catch (\Exception $e) {
        // Log the error message
        Log::error('Failed to generate invoice PDF', ['error' => $e->getMessage()]);

        // Return error response if something goes wrong
        return response()->json([
            'status' => 500,
            'message' => 'Failed to generate invoice PDF',
            'error' => $e->getMessage()
        ], 500);
    }
}

}










<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Income;
use App\Models\TeamTask;
use App\Models\Proposals;
use App\Models\Proposal_template;
use App\Models\tasks;
use App\Models\Teams;
use App\Models\Document;
use App\Models\Invoices;
use App\Models\Estimate;
use App\Models\Equipments;
use App\Models\Customers;
use App\Models\Expense;
use App\Models\Products;
use App\Models\RecuringExpenses;
use App\Models\Pipelines;
use App\Models\Payments;
use App\Models\Organization;
use App\Models\Clients;
use App\Models\Vendors;
use App\Models\Tags;
//use App\Models\Employment_Types;
use App\Models\EmploymentTypes;
use App\Models\Leads;
use App\Models\Product;
use App\Models\Projects;
class QuickcreateController extends Controller
{
    /**
     * Update quickcreate column for mandatory fields
     *
     * @param int $tabid
     * @param array $mandatoryFields
     */
    private function updateQuickCreateColumn(int $tabid, array $mandatoryFields)
    {
        foreach (array_keys($mandatoryFields) as $columnName) {
            Log::info("Updating quickcreate for tabid: $tabid, columnname: $columnName");
            DB::table('jo_fields')
                ->where('tabid', $tabid)
                ->where('columnname', $columnName)
                ->update(['quickcreate' => 0]);
        }
    }

    public function quickCreateIncome(Request $request)
    {
        // Fetch the tabid dynamically for Incomes
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Incomes')
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
            'Employee that generate income' => 'required|string',
            'Contact' => 'required|string',
            'amount' => 'required|integer'
        ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);

        // Fetch the field definitions from the database
        $fields = DB::table('jo_fields')
            ->where('tabid', $tabid)
            ->get();

        // Define optional fields based on database schema
        $optionalFields = [];
        foreach ($fields as $field) {
            if (!isset($mandatoryFields[$field->columnname])) {
                $optionalFields[$field->columnname] = 'nullable';
            }
        }

        // Combine mandatory and optional fields
        $validationRules = array_merge($mandatoryFields, $optionalFields);

        // Validate incoming request data
        $validator = Validator::make($request->all(), $validationRules);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of Income model
        $income = new Income();

        // Assign validated data to the model
        $income->fill($request->all());

        // Save the model instance
        $income->save();

        // Optionally, return a response indicating success
        return response()->json(['message' => 'Income created successfully', 'data' => $income], 201);
    }

    public function quickCreateTeamTask(Request $request)
    {
        // Fetch the tabid dynamically for TeamTask
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Teamtask')
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
            'title' => 'required|string',
            'tasknumber' => 'required|integer',
            'projects' => 'required|string'
        ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);

        // Fetch the field definitions from the database
        $fields = DB::table('jo_fields')
            ->where('tabid', $tabid)
            ->get();

        // Define optional fields based on database schema
        $optionalFields = [];
        foreach ($fields as $field) {
            if (!isset($mandatoryFields[$field->columnname])) {
                $optionalFields[$field->columnname] = 'nullable';
            }
        }

        // Combine mandatory and optional fields
        $validationRules = array_merge($mandatoryFields, $optionalFields);

        // Validate incoming request data
        $validator = Validator::make($request->all(), $validationRules);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of TeamTask model
        $teamTask = new TeamTask();

        // Assign validated data to the model
        $teamTask->fill($request->all());

        // Save the model instance
        $teamTask->save();

        // Optionally, return a response indicating success
        return response()->json(['message' => 'TeamTask created successfully', 'data' => $teamTask], 201);
    }
    public function quickCreateDocument(Request $request)
    {
        try {
            // Fetch the tabid dynamically for Documents
            $tabid = (int) DB::table('jo_tabs')
                ->where('name', 'Documents')
                ->value('tabid');

            if (!$tabid) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Define the specific mandatory fields for quick create
            $mandatoryFields = [
                'document_name' => 'required|string',
                        ];

            // Update quickcreate column for mandatory fields (if needed)
            $this->updateQuickCreateColumn($tabid, $mandatoryFields);

            // Validate incoming request data
            $validator = Validator::make($request->all(), $mandatoryFields);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Create a new instance of Document model
            $document = new Document();
            $document->document_name = $request->input('document_name');
                $document->save();

            // Return success response
            return response()->json(['message' => 'Document created successfully', 'data' => $document], 201);

        } catch (\Exception $e) {
            // Log the error with detailed message
            Log::error('Error creating document: ' . $e->getMessage());

            // Return internal server error response with specific error message
            return response()->json(['error' => 'Internal Server Error: Failed to create document. ' . $e->getMessage()], 500);
        }
    }
    public function quickCreateProposals(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'author' => 'required|string',
                'contact' => 'required|string',
                'proposal_content' => 'required|string',
                'job_post_content' => 'nullable|string',
            ]);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Create a new instance of Proposals model
            $proposal = new Proposals();
            $proposal->fill($request->all());
            $proposal->save();

            // Return success response
            return response()->json(['message' => 'Proposal created successfully', 'data' => $proposal], 201);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error creating proposal: ' . $e->getMessage());

            // Return internal server error response
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    public function quickCreateTeam(Request $request)
{
    try {
        // Fetch the tabid dynamically for Teams
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Teams')
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
            'team_name' => 'required|string',
            'tags' => 'nullable|json',
            'add_or_remove_managers' => 'required|string', // Ensure this matches your actual data type
            'add_or_remove_members' => 'required|string' // Ensure this matches your actual data type
        ];

        // Update quickcreate column for mandatory fields (if needed)
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);

        // Validate incoming request data
        $validator = Validator::make($request->all(), $mandatoryFields);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of Teams model
        $team = new Teams();
        $team->team_name = $request->input('team_name');
        $team->tags = $request->input('tags', null); // Assuming 'tags' is a JSON column
        $team->add_or_remove_managers = $request->input('add_or_remove_managers');
        $team->add_or_remove_members = $request->input('add_or_remove_members');

        // Save the model instance
        $team->save();

        // Return success response
        return response()->json(['message' => 'Team created successfully', 'data' => $team], 201);

    } catch (\Exception $e) {
        // Log the error
        Log::error('Error creating team: ' . $e->getMessage());

        // Return internal server error response
        return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
    }
}

public function quickCreateTasks(Request $request)
    {
        try {
            // Fetch the tabid dynamically for Tasks
            $tabid = (int) DB::table('jo_tabs')
                ->where('name', 'Tasks')
                ->value('tabid');

            if (!$tabid) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Define the specific mandatory fields for quick create
            $mandatoryFields = [
                'tasksnumber' => 'required|integer',
                'projects' => 'required|string',
                'title' => 'required|string',
            ];

            // Update quickcreate column for mandatory fields (if needed)
            $this->updateQuickCreateColumn($tabid, $mandatoryFields);

            // Validate incoming request data
            $validator = Validator::make($request->all(), $mandatoryFields);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Create a new instance of Tasks model
            $task = new tasks();
            $task->fill($request->all());
            $task->save();

            // Return success response
            return response()->json(['message' => 'Task created successfully', 'data' => $task], 201);

        } catch (\Exception $e) {
            // Log the error with detailed message
            Log::error('Error creating task: ' . $e->getMessage());

            // Return internal server error response with specific error message
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
    public function quickCreateInvoices(Request $request)
    {
        try {
            // Fetch the tabid dynamically for Invoices
            $tabid = (int) DB::table('jo_tabs')
                ->where('name', 'Invoices')
                ->value('tabid');

            if (!$tabid) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Define the specific mandatory fields for quick create
            $mandatoryFields = [
                'invoicenumber' => 'required|integer',
                'contacts' => 'required|string',
                'terms' => 'required|string',
                'duedate' => 'required|date',
                'invoicedate' => 'required|date',
            ];

            // Update quickcreate column for mandatory fields (if needed)
            $this->updateQuickCreateColumn($tabid, $mandatoryFields);

            // Validate incoming request data
            $validator = Validator::make($request->all(), $mandatoryFields);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Log the validated data
            Log::info('Validated data: ', $request->all());

            // Create a new instance of Invoices model
            $invoice = new Invoices();
            $invoice->fill($request->all());

            // Log the data being saved
            Log::info('Data to be saved: ', $invoice->toArray());

            // Save the model instance
            $invoice->save();

            // Log the success message
            Log::info('Invoice created successfully: ', $invoice->toArray());

            // Return success response
            return response()->json(['message' => 'Invoice created successfully', 'data' => $invoice], 201);

        } catch (\Exception $e) {
            // Log the error with detailed message
            Log::error('Error creating invoice: ' . $e->getMessage());

            // Return internal server error response with specific error message
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function quickCreateEstimates(Request $request)
    {
        try {
            // Fetch the tabid dynamically for Estimates
            $tabid = (int) DB::table('jo_tabs')
                ->where('name', 'Estimates')
                ->value('tabid');

            if (!$tabid) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Define the specific mandatory fields for quick create
            $mandatoryFields = [
                'estimatenumber' => 'required|integer',
                'contacts' => 'required|string',
                'estimatedate' => 'required|date',
                'duedate' => 'required|date',
                'currency' => 'required|string',
                'terms' => 'required|string',
            ];

            // Update quickcreate column for mandatory fields (if needed)
            $this->updateQuickCreateColumn($tabid, $mandatoryFields);

            // Validate incoming request data
            $validator = Validator::make($request->all(), $mandatoryFields);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Log the validated data
            Log::info('Validated data: ', $request->all());

            // Create a new instance of Estimates model
            $estimate = new Estimate();
            $estimate->fill($request->all());

            // Log the data being saved
            Log::info('Data to be saved: ', $estimate->toArray());

            // Save the model instance
            $estimate->save();

            // Log the success message
            Log::info('Estimate created successfully: ', $estimate->toArray());

            // Return success response
            return response()->json(['message' => 'Estimate created successfully', 'data' => $estimate], 201);

        } catch (\Exception $e) {
            // Log the error with detailed message
            Log::error('Error creating estimate: ' . $e->getMessage());

            // Return internal server error response with specific error message
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
    public function quickCreateEquipments(Request $request)
    {
        try {
            // Fetch the tabid dynamically for Equipments
            $tabid = (int) DB::table('jo_tabs')
                ->where('name', 'Equipments')
                ->value('tabid');

            if (!$tabid) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Define the specific mandatory fields for quick create
            $mandatoryFields = [
                'name' => 'required|string',
                'type' => 'required|string',
                'manufactured_year' => 'required|integer',
                'currency' => 'required|string',
            ];

            // Update quickcreate column for mandatory fields (if needed)
            $this->updateQuickCreateColumn($tabid, $mandatoryFields);

            // Validate incoming request data
            $validator = Validator::make($request->all(), $mandatoryFields);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Log the validated data
            Log::info('Validated data: ', $request->all());

            // Create a new instance of Equipments model
            $equipment = new Equipments();
            $equipment->fill($request->all());

            // Log the data being saved
            Log::info('Data to be saved: ', $equipment->toArray());

            // Save the model instance
            $equipment->save();

            // Log the success message
            Log::info('Equipment created successfully: ', $equipment->toArray());

            // Return success response
            return response()->json(['message' => 'Equipment created successfully', 'data' => $equipment], 201);

        } catch (\Exception $e) {
            // Log the error with detailed message
            Log::error('Error creating equipment: ' . $e->getMessage());

            // Return internal server error response with specific error message
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
    public function quickCreateCustomers(Request $request)
    {
        try {
            // Fetch the tabid dynamically for Customers
            $tabid = (int) DB::table('jo_tabs')
                ->where('name', 'Customers')
                ->value('tabid');

            if (!$tabid) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Define the specific mandatory fields for quick create
            $mandatoryFields = [
                'name' => 'required|string',
                'primary_email' => 'required|string|email',
                'primary_phone' => 'required|string',
                'projects' => 'required|json',
            ];

            // Update quickcreate column for mandatory fields (if needed)
            $this->updateQuickCreateColumn($tabid, $mandatoryFields);

            // Validate incoming request data
            $validator = Validator::make($request->all(), $mandatoryFields);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Log the validated data
            Log::info('Validated data: ', $request->all());

            // Create a new instance of Customers model
            $customer = new Customers();
            $customer->fill($request->all());

            // Log the data being saved
            Log::info('Data to be saved: ', $customer->toArray());

            // Save the model instance
            $customer->save();

            // Log the success message
            Log::info('Customer created successfully: ', $customer->toArray());

            // Return success response
            return response()->json(['message' => 'Customer created successfully', 'data' => $customer], 201);

        } catch (\Exception $e) {
            // Log the error with detailed message
            Log::error('Error creating customer: ' . $e->getMessage());

            // Return internal server error response with specific error message
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
    public function quickCreateExpenses(Request $request)
    {
        try {
            // Fetch the tabid dynamically for Expenses
            $tabid = (int) DB::table('jo_tabs')
                ->where('name', 'Expenses')
                ->value('tabid');

            if (!$tabid) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Define the specific mandatory fields for quick create
            $mandatoryFields = [
                'tax_deductible' => 'required|boolean',
                'billable_to_contact' => 'required|boolean',
                'categories' => 'required|string',
                'amount' => 'required|integer',
                'project' => 'required|json',
                'contact' => 'required|string',
            ];

            // Update quickcreate column for mandatory fields (if needed)
            $this->updateQuickCreateColumn($tabid, $mandatoryFields);

            // Validate incoming request data
            $validator = Validator::make($request->all(), $mandatoryFields);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Log the validated data
            Log::info('Validated data: ', $request->all());

            // Create a new instance of Expenses model
            $expense = new Expense();
            $expense->fill($request->all());

            // Log the data being saved
            Log::info('Data to be saved: ', $expense->toArray());

            // Save the model instance
            $expense->save();

            // Log the success message
            Log::info('Expense created successfully: ', $expense->toArray());

            // Return success response
            return response()->json(['message' => 'Expense created successfully', 'data' => $expense], 201);

        } catch (\Exception $e) {
            // Log the error with detailed message
            Log::error('Error creating expense: ' . $e->getMessage());

            // Return internal server error response with specific error message
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
    public function quickCreateRecuringExpenses(Request $request)
    {
        try {
            // Fetch the tabid dynamically for Recurring Expenses
            $tabid = (int) DB::table('jo_tabs')
                ->where('name', 'Recuring Expenses')
                ->value('tabid');

            if (!$tabid) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Define the specific mandatory fields for quick create
            $mandatoryFields = [
                'category_name' => 'required|string',
                'value' => 'required|numeric',
                'currency' => 'required|string',
            ];

            // Update quickcreate column for mandatory fields (if needed)
            $this->updateQuickCreateColumn($tabid, $mandatoryFields);

            // Validate incoming request data
            $validator = Validator::make($request->all(), $mandatoryFields);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Log the validated data
            Log::info('Validated data: ', $request->all());

            // Create a new instance of RecuringExpense model
            $expense = new RecuringExpenses();
            $expense->fill($request->all());

            // Log the data being saved
            Log::info('Data to be saved: ', $expense->toArray());

            // Save the model instance
            $expense->save();

            // Log the success message
            Log::info('Recurring Expense created successfully: ', $expense->toArray());

            // Return success response
            return response()->json(['message' => 'Recurring Expense created successfully', 'data' => $expense], 201);

        } catch (\Exception $e) {
            // Log the error with detailed message
            Log::error('Error creating recurring expense: ' . $e->getMessage());

            // Return internal server error response with specific error message
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function quickCreatePipelines(Request $request)
    {
        try {
            // Fetch the tabid dynamically for Pipelines
            $tabid = (int) DB::table('jo_tabs')
                ->where('name', 'Pipelines')
                ->value('tabid');

            if (!$tabid) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Define the specific mandatory fields for quick create
            $mandatoryFields = [
                'name' => 'required|string',
                'is_active' => 'required|boolean',
            ];

            // Update quickcreate column for mandatory fields (if needed)
            $this->updateQuickCreateColumn($tabid, $mandatoryFields);

            // Validate incoming request data
            $validator = Validator::make($request->all(), $mandatoryFields);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Log the validated data
            Log::info('Validated data: ', $request->all());

            // Create a new instance of Pipeline model
            $pipeline = new Pipelines();
            $pipeline->fill($request->all());

            // Log the data being saved
            Log::info('Data to be saved: ', $pipeline->toArray());

            // Save the model instance
            $pipeline->save();

            // Log the success message
            Log::info('Pipeline created successfully: ', $pipeline->toArray());

            // Return success response
            return response()->json(['message' => 'Pipeline created successfully', 'data' => $pipeline], 201);

        } catch (\Exception $e) {
            // Log the error with detailed message
            Log::error('Error creating pipeline: ' . $e->getMessage());

            // Return internal server error response with specific error message
            return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function quickCreateProducts(Request $request)
{
    try {
        // Fetch the tabid dynamically for Products
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Products')
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
            'name' => 'required|string',
            'code' => 'required|string',
            'product_type' => 'required|string',
            'product_category' => 'required|string', // Ensure product_category is required
            'list_price' => 'nullable|string',
        ];

        // Update quickcreate column for mandatory fields (if needed)
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);

        // Validate incoming request data
        $validator = Validator::make($request->all(), $mandatoryFields);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of Product model
        $product = new Product();
        $product->fill($request->all());

        // Save the model instance
        $product->save();

        // Return success response
        return response()->json(['message' => 'Product created successfully', 'data' => $product], 201);

    } catch (\Exception $e) {
        // Log the error with detailed message
        Log::error('Error creating product: ' . $e->getMessage());

        // Return internal server error response with specific error message
        return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
    }
}
public function quickCreatePayment(Request $request)
{
    // Fetch the tabid dynamically for Payments
    $tabid = (int) DB::table('jo_tabs')
        ->where('name', 'Payments')
        ->value('tabid');

    if (!$tabid) {
        return response()->json(['error' => 'Module not found'], 404);
    }

    // Define the specific mandatory fields for quick create
    $mandatoryFields = [
        // 'invoice_number' => 'required|integer',
        'contacts' => 'required|string',
        'projects' => 'required|string',
        // 'payment_date' => 'required|date',
        'payment_method' => 'required|string',
        'amount' => 'required|integer'
    ];

    // Update quickcreate column for mandatory fields
    $this->updateQuickCreateColumn($tabid, $mandatoryFields);

    // Fetch the field definitions from the database
    $fields = DB::table('jo_fields')
        ->where('tabid', $tabid)
        ->get();

    // Define optional fields based on database schema
    $optionalFields = [];
    foreach ($fields as $field) {
        if (!isset($mandatoryFields[$field->columnname])) {
            $optionalFields[$field->columnname] = 'nullable';
        }
    }

    // Combine mandatory and optional fields
    $validationRules = array_merge($mandatoryFields, $optionalFields);

    // Validate incoming request data
    $validator = Validator::make($request->all(), $validationRules);

    // If validation fails, return error response
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Create a new instance of Payment model
    $payment = new Payments();

    // Assign validated data to the model
    $payment->fill($request->all());

    // Save the model instance
    $payment->save();

    // Optionally, return a response indicating success
    return response()->json(['message' => 'Payment created successfully', 'data' => $payment], 201);
}
public function quickCreateProposalTemplate(Request $request)
    {
        // Fetch the tabid dynamically for Proposal Template
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Proposal Template')
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
            'select_employee' => 'required|string',
            'name' => 'required|string',
            'content' => 'required|string'
        ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);

        // Fetch the field definitions from the database
        $fields = DB::table('jo_fields')
            ->where('tabid', $tabid)
            ->get();

        // Define optional fields based on database schema
        $optionalFields = [];
        foreach ($fields as $field) {
            if (!isset($mandatoryFields[$field->columnname])) {
                $optionalFields[$field->columnname] = 'nullable';
            }
        }

        // Combine mandatory and optional fields
        $validationRules = array_merge($mandatoryFields, $optionalFields);

        // Validate incoming request data
        $validator = Validator::make($request->all(), $validationRules);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of ProposalTemplate model
        $proposalTemplate = new Proposal_template();

        // Assign validated data to the model
        $proposalTemplate->fill($request->all());

        // Save the model instance
        $proposalTemplate->save();

        // Optionally, return a response indicating success
        return response()->json(['message' => 'ProposalTemplate created successfully', 'data' => $proposalTemplate], 201);
    }
    public function quickCreateOrganization(Request $request)
    {
        // Fetch the tabid dynamically for Organizations
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Organization')
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
            // 'image' => 'required|file',
            'organization_name' => 'required|string',
            'official_name' => 'required|string',
            'location' => 'required|json'
        ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);

        // Fetch the field definitions from the database
        $fields = DB::table('jo_fields')
            ->where('tabid', $tabid)
            ->get();

        // Define optional fields based on database schema
        $optionalFields = [];
        foreach ($fields as $field) {
            if (!isset($mandatoryFields[$field->columnname])) {
                $optionalFields[$field->columnname] = 'nullable';
            }
        }

        // Combine mandatory and optional fields
        $validationRules = array_merge($mandatoryFields, $optionalFields);

        // Validate incoming request data
        $validator = Validator::make($request->all(), $validationRules);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of Organization model
        $organization = new Organization();

        // Assign validated data to the model
        $organization->fill($request->all());

        // Handle file upload for image
        if ($request->hasFile('image')) {
            $organization->image = file_get_contents($request->file('image')->getRealPath());
        }

        // Save the model instance
        $organization->save();

        // Optionally, return a response indicating success
        return response()->json(['message' => 'Organization created successfully', 'data' => $organization], 201);
    }
    public function quickCreateClient(Request $request)
    {
        // Add a debug statement
        Log::info('Entering quickCreateClient method.');

        // Fetch the tabid dynamically for Clients (assuming 'jo_clients' is the table name)
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Clients') // Adjust according to your table name
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
            'name' => 'required|string',
            'primary_email' => 'required|email',
            'primary_phone' => 'required|string',
            'projects' => 'required|json' // Adjust validation as per your projects field
        ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);


        $validator = Validator::make($request->all(), $mandatoryFields);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of Client model
        $client = new Clients();

        // Assign validated data to the model
        $client->fill($request->all());

        // Save the model instance
        $client->save();

        // Optionally, return a response indicating success
        return response()->json(['message' => 'Client created successfully', 'data' => $client], 201);
    }
    public function quickCreateVendor(Request $request)
    {
        // Log the method entry
        Log::info('Entering quickCreateVendor method.');

        // Fetch the tabid dynamically for Vendors
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Vendors')
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
            'name' => 'required|string',
            // 'phone' => 'required|integer',
            'email' => 'required|email'
        ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);

        // Fetch the field definitions from the database
        $fields = DB::table('jo_fields')
            ->where('tabid', $tabid)
            ->get();

        // Define optional fields based on database schema
        $optionalFields = [];
        foreach ($fields as $field) {
            if (!isset($mandatoryFields[$field->columnname])) {
                $optionalFields[$field->columnname] = 'nullable';
            }
        }
        $validationRules = array_merge($mandatoryFields, $optionalFields);
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $vendor = new Vendors();
        $vendor->fill($request->all());
        $vendor->save();
        return response()->json(['message' => 'Vendor created successfully', 'data' => $vendor], 201);
    }
    public function quickCreateTags(Request $request)
    {
        // Add a debug statement
        Log::info('Entering quickCreateTags method.');

        // Fetch the tabid dynamically for Clients (assuming 'jo_clients' is the table name)
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Tags') // Adjust according to your table name
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
            'tags_names' => 'required|string',
            'description'=>'required|string',
            'tag_color'=>'required|string',
            ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);


        $validator = Validator::make($request->all(), $mandatoryFields);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of Client model
        $tags = new Tags();

        // Assign validated data to the model
        $tags->fill($request->all());

        // Save the model instance
        $tags->save();

        // Optionally, return a response indicating success
        return response()->json(['message' => 'tags created successfully', 'data' => $tags], 201);
    }
    public function quickCreateEmployeeTypes(Request $request)
    {
        // Add a debug statement
        Log::info('Entering quickCreateEmployeeTypes method.');

        // Fetch the tabid dynamically for Clients (assuming 'jo_clients' is the table name)
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Employment Types') // Adjust according to your table name
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
        'employment_type_name' => 'required|string',

            ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);


        $validator = Validator::make($request->all(), $mandatoryFields);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of Client model
        $employementtypes = new EmploymentTypes();

        // Assign validated data to the model
        $employementtypes->fill($request->all());

        // Save the model instance
        $employementtypes->save();

        // Optionally, return a response indicating success
        return response()->json(['message' => 'employementtypes created successfully', 'data' => $employementtypes], 201);
    }
    public function quickCreateLeads(Request $request)
    {
        // Add a debug statement
        Log::info('Entering quickCreateEmployeeTypes method.');

        // Fetch the tabid dynamically for Clients (assuming 'jo_clients' is the table name)
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Leads') // Adjust according to your table name
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
        'name' => 'required|string',
        'primary_email'=>'required|string',
        "primary_phone"=>'required|string',
        'projects'=>'required|string',
        ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);


        $validator = Validator::make($request->all(), $mandatoryFields);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of Client model
        $leads = new Leads();

        // Assign validated data to the model
        $leads->fill($request->all());

        // Save the model instance
        $leads->save();

        // Optionally, return a response indicating success
        return response()->json(['message' => 'Leads created successfully', 'data' => $leads], 201);
    }
    public function quickCreateprojects(Request $request)
    {
        // Add a debug statement
        Log::info('Entering quickCreateEmployeeTypes method.');

        // Fetch the tabid dynamically for Clients (assuming 'jo_clients' is the table name)
        $tabid = (int) DB::table('jo_tabs')
            ->where('name', 'Projects') // Adjust according to your table name
            ->value('tabid');

        if (!$tabid) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // Define the specific mandatory fields for quick create
        $mandatoryFields = [
        'name' => 'required|string',
        'code'=>'required|string',
        'project_url'=> 'required|string',
        'clients'=>'required|string',
        'currency'=>'required|string'
        ];

        // Update quickcreate column for mandatory fields
        $this->updateQuickCreateColumn($tabid, $mandatoryFields);


        $validator = Validator::make($request->all(), $mandatoryFields);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create a new instance of Client model
        $projects = new Projects();

        // Assign validated data to the model
        $projects->fill($request->all());

        // Save the model instance
        $projects->save();

        // Optionally, return a response indicating success
        return response()->json(['message' => 'Projects created successfully', 'data' => $projects], 201);
    }


}













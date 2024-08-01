<?php

namespace App\Http\Controllers;

use App\Models\Blocks;
use App\Models\Tab;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormFieldController extends Controller
{
    /**
     * Fetch form fields dynamically based on tab name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFormFields(Request $request)
    {
        try {
            $tabName = $request->input('name');

            // Validate that tabName is present in the request
            if (!$tabName) {
                Log::error('Tab name is missing from the request.');
                return response()->json(['error' => 'Tab name is required'], 400);
            }

            // Log the received tab name for debugging
            Log::info('Tab name received: ' . $tabName);

            // Fetch the tab from the database using the tab name
            $tab = Tab::where('name', $tabName)->first();

            // Handle case where no tab is found
            if (!$tab) {
                Log::error('No tab found for the given tab name', ['name' => $tabName]);
                return response()->json(['error' => 'Tab not found'], 404);
            }

            $tabId = $tab->tabid;

            // Fetch blocks with their associated fields based on tab ID
            $blocks = Blocks::where('tabid', $tabId)->with('fields')->get();

            // Log the SQL queries executed
            Log::info('SQL Query: ', DB::getQueryLog());

            // Handle case where no blocks are found
            if ($blocks->isEmpty()) {
                Log::error('No blocks found for the given tab ID', ['tabid' => $tabId]);
                return response()->json([]);
            }

            // Prepare an array to store form fields data dynamically
            $formFields = [];

            foreach ($blocks as $block) {
                // Log the processing of each block for debugging
                Log::info('Processing block: ' . $block->blocklabel);

                $fields = [];
                foreach ($block->fields as $field) {
                    // Fetch additional details about the field using the Fields model
                    $fieldDetails = Field::where('fieldid', $field->fieldid)->first();

                    // Prepare basic field data
                    $fieldData = [
                        'name' => $field->fieldname,
                        'type' => $field->uitype,
                        'label' => $field->fieldlabel,
                            ];

                    // Example: Fetch options for specific field types
                    if (in_array($field->uitype, [33, 16])) {
                        $options = $this->getFieldOptions($field->fieldname);
                        if ($options) {
                            $fieldData['options'] = $options;
                        }
                    }

                    // Example: Add validation rules if specified in typeofdata
                    if (str_contains($field->typeofdata, 'V~M') || str_contains($field->typeofdata, 'D~M')) {
                        $fieldData['rules'][] = [
                            'required' => true,
                            'message' => 'Enter your ' . strtolower($field->fieldlabel),
                        ];
                    }

                    // Add the field data to the fields array
                    $fields[] = $fieldData;
                }

                // Add the block data with its fields to the formFields array
                $formFields[] = [
                    'blockid' => $block->blockid,
                    'blockname' => $block->blocklabel,
                    'fields' => $fields,
                ];

                // Log the form fields added for each block
                Log::info('Form fields for block: ', ['blockname' => $block->blocklabel, 'fields' => $fields]);
            }

            // Return the dynamically fetched form fields as JSON response
            return response()->json($formFields);
        } catch (\Exception $e) {
            // Log the error encountered during form fields fetching
            Log::error('Error fetching form fields: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching form fields'], 500);
        }
    }

    /**
     * Fetch options for a specific field dynamically.
     *
     * @param string $fieldName
     * @return array
     */
  
private function getFieldOptions($fieldName)
{
    // Initialize an empty options array
    $options = [];

    // Define options fetching logic based on field name
    $optionsMap = [
        'tags' => function () {
            $options = DB::table('jo_tags')->select('id', 'tags_name as value', 'tags_name as label', 'tag_color as color')->get()->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a tag...', 'color' => '', 'id' => null]);
            return $options;
        },
        
        'projects' => function () {
            $options = DB::table('jo_projects')->pluck('project_name', 'id')->map(function ($project, $id) {
                return ['value' => $project, 'label' => $project, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a project...', 'id' => null]);
            return $options;
        },
        
        'teams' => function () {
            $options = DB::table('jo_teams')->pluck('team_name', 'id')->map(function ($teams, $id) {
                return ['value' => $teams, 'label' => $teams, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a team...', 'id' => null]);
            return $options;
        },
        
        'contacts' => function () {
            $customers = DB::table('jo_customers')->pluck('name', 'id')->map(function ($customer, $id) {
                return ['value' => $customer, 'label' => $customer, 'id' => $id];
            })->toArray();

            $leads = DB::table('jo_leads')->pluck('name', 'id')->map(function ($lead, $id) {
                return ['value' => $lead, 'label' => $lead, 'id' => $id];
            })->toArray();

            $clients = DB::table('jo_clients')->pluck('name', 'id')->map(function ($client, $id) {
                return ['value' => $client, 'label' => $client, 'id' => $id];
            })->toArray();

            $options = array_merge($customers, $leads, $clients);
            array_unshift($options, ['value' => '', 'label' => 'Select a contact...', 'id' => null]);
            return $options;
        },
        
        'Employee that generate income' => function () {
            $options = DB::table('jo_manage_employees')->pluck('first_name', 'id')->map(function ($employee, $id) {
                return ['value' => $employee, 'label' => $employee, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an employee...', 'id' => null]);
            return $options;
        },
        
        'invoice_number' => function () {
            $options = DB::table('jo_invoices')->pluck('invoicenumber', 'id')->map(function ($invoice, $id) {
                return ['value' => $invoice, 'label' => $invoice, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an invoice number...', 'id' => null]);
            return $options;
        },
        
        'product_type' => function () {
            $options = DB::table('jo_product_types')->pluck('name', 'id')->map(function ($product_type, $id) {
                return ['value' => $product_type, 'label' => $product_type, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a product type...', 'id' => null]);
            return $options;
        },
        
        'product_category' => function () {
            $options = DB::table('jo_product_categories')->pluck('name', 'id')->map(function ($product_category, $id) {
                return ['value' => $product_category, 'label' => $product_category, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a product category...', 'id' => null]);
            return $options;
        },
        
        'employees_that_generate' => function () {
            $options = DB::table('jo_manage_employees')->pluck('first_name', 'id')->map(function ($employee, $id) {
                return ['value' => $employee, 'label' => $employee, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an employee...', 'id' => null]);
            return $options;
        },
        
        'categories' => function () {
            $options = DB::table('jo_manage_categories')->pluck('expense_name', 'id')->map(function ($category, $id) {
                return ['value' => $category, 'label' => $category, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a category...', 'id' => null]);
            return $options;
        },
        
        'category_name' => function () {
            $options = DB::table('jo_manage_categories')->pluck('expense_name', 'id')->map(function ($category, $id) {
                return ['value' => $category, 'label' => $category, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a category...', 'id' => null]);
            return $options;
        },
        
        'vendor' => function () {
            $options = DB::table('jo_vendors')->pluck('name', 'id')->map(function ($vendor, $id) {
                return ['value' => $vendor, 'label' => $vendor, 'id' => $id];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a vendor...', 'id' => null]);
            return $options;
        },
        
        // Add more mappings as needed
    ];
    // Default options if fieldName doesn't match specific cases
    $defaultOptions = [
        'status' => [
            ['value' => '', 'label' => 'Select status...'],
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'open', 'label' => 'Open'],
            ['value' => 'inprogress', 'label' => 'In Progress'],
            ['value' => 'inreview', 'label' => 'In Review'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'closed', 'label' => 'Closed'],
        ],
        'priority' => [
            ['value' => '', 'label' => 'Select priority...'],
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'low', 'label' => 'Low'],
            ['value' => 'medium', 'label' => 'Medium'],
            ['value' => 'high', 'label' => 'High'],
            ['value' => 'urgent', 'label' => 'Urgent'],
        ],
        'size' => [
            ['value' => '', 'label' => 'Select size...'],
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'large', 'label' => 'Large'],
            ['value' => 'medium', 'label' => 'Medium'],
            ['value' => 'small', 'label' => 'Small'],
            ['value' => 'tiny', 'label' => 'Tiny'],
        ],
        'currency' => [
            ['value' => '', 'label' => 'Select currency...'],
            ['value' => 'india(INR)', 'label' => 'India(INR)'],
            ['value' => 'canadian dollar(CAD)', 'label' => 'Canadian Dollar(CAD)'],
            ['value' => 'israeli pound(ILP)', 'label' => 'Israeli Pound(ILP)'],
        ],
        'Currency' => [
            ['value' => '', 'label' => 'Select currency...'],
            ['value' => 'india(INR)', 'label' => 'India(INR)'],
            ['value' => 'canadian dollar(CAD)', 'label' => 'Canadian Dollar(CAD)'],
            ['value' => 'israeli pound(ILP)', 'label' => 'Israeli Pound(ILP)'],
        ],
        'employee_bonus_type' => [
            ['value' => '', 'label' => 'Select bonus type...'],
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'profit bonus type', 'label' => 'Profit Bonus Type'],
            ['value' => 'revenue based bonus', 'label' => 'Revenue Based Bonus'],
        ],
        'start_week_on' => [
            ['value' => '', 'label' => 'Select start day...'],
            ['value' => 'monday', 'label' => 'Monday'],
            ['value' => 'tuesday', 'label' => 'Tuesday'],
            ['value' => 'wednesday', 'label' => 'Wednesday'],
            ['value' => 'thursday', 'label' => 'Thursday'],
            ['value' => 'friday', 'label' => 'Friday'],
            ['value' => 'saturday', 'label' => 'Saturday'],
            ['value' => 'sunday', 'label' => 'Sunday'],
        ],
        'default_date_type' => [
            ['value' => '', 'label' => 'Select date type...'],
            ['value' => 'today', 'label' => 'Today'],
            ['value' => 'end of the month', 'label' => 'End Of The Month'],
            ['value' => 'start of the month', 'label' => 'Start Of The Month'],
        ],
        'type' => [
            ['value' => '', 'label' => 'Select type...'],
            ['value' => 'cost', 'label' => 'Cost'],
            ['value' => 'hours', 'label' => 'Hours'],
    ],
    'contact_type' => [
        ['value' => '', 'label' => 'Select contact type...'],
        ['value' => 'client', 'label' => 'Client'],
        ['value' => 'customer', 'label' => 'Customer'],
        ['value' => 'lead', 'label' => 'Lead'],
    ],
    'estimate' => [
        ['value' => '', 'label' => 'Select estimate...'],
        ['value' => 'days', 'label' => 'Day'],
        ['value' => 'hours', 'label' => 'Hours'],
        ['value' => 'minutes', 'label' => 'Minutes'],
    ],
    'invoice_status' => [
        ['value' => '', 'label' => 'Select invoice status...'],
        ['value' => 'none', 'label' => 'None'],
        ['value' => 'open', 'label' => 'Open'],
        ['value' => 'inprogress', 'label' => 'In Progress'],
        ['value' => 'inreview', 'label' => 'In Review'],
        ['value' => 'completed', 'label' => 'Completed'],
        ['value' => 'closed', 'label' => 'Closed'],
    ],
    'payment_method' => [
        ['value' => '', 'label' => 'Select payment method...'],
        ['value' => 'bank transfer', 'label' => 'Bank Transfer'],
        ['value' => 'cash', 'label' => 'Cash'],
        ['value' => 'cheque', 'label' => 'Cheque'],
        ['value' => 'credit card', 'label' => 'Credit Card'],
        ['value' => 'debit', 'label' => 'Debit'],
        ['value' => 'online', 'label' => 'Online'],
    ],
    'select_status' => [
        ['value' => '', 'label' => 'Select status...'],
        ['value' => 'not billable', 'label' => 'Not Billable'],
    ],
    'discount_suffix' => [
        ['value' => '', 'label' => 'Select discount suffix...'],
        ['value' => '%', 'label' => '%'],
        ['value' => 'flat', 'label' => 'Flat'],
    ],
    'taxtype' => [
        ['value' => '', 'label' => 'Select tax type...'],
        ['value' => 'individual', 'label' => 'Individual'],
        ['value' => 'group', 'label' => 'Group'],
    ],
    'estimate_status' => [
        ['value' => '', 'label' => 'Select estimate status...'],
        ['value' => 'created', 'label' => 'Created'],
        ['value' => 'delivered', 'label' => 'Delivered'],
        ['value' => 'reviewed', 'label' => 'Reviewed'],
        ['value' => 'accepted', 'label' => 'Accepted'],
        ['value' => 'rejected', 'label' => 'Rejected'],
    ],
    'select_invoice_type' => [
        ['value' => '', 'label' => 'Select invoice type...'],
        ['value' => 'employees', 'label' => 'Employee'],
        ['value' => 'projects', 'label' => 'Projects'],
        ['value' => 'tasks', 'label' => 'Tasks'],
        ['value' => 'products', 'label' => 'Products'],
        ['value' => 'expenses', 'label' => 'Expenses'],
    ],

    // Add more options arrays as needed
];

// Execute the appropriate function if fieldName matches a specific case
$optionsFunction = $optionsMap[$fieldName] ?? null;
if ($optionsFunction && is_callable($optionsFunction)) {
    return $optionsFunction();
}

// Log a warning if no options function is defined for the fieldName
Log::warning('No options defined for fieldName: ' . $fieldName);

// Return default options if fieldName doesn't match specific cases
return $defaultOptions[$fieldName] ?? [];
}
}
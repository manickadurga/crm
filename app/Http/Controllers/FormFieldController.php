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
            $options = DB::table('jo_tags')->select('tags_name as value', 'tags_name as label', 'tag_color as color')->get()->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a tag...', 'color' => '']);
            return $options;
        },
        
        'projects' => function () {
            $options = DB::table('jo_projects')->pluck('project_name')->map(function ($project) {
                return ['value' => $project, 'label' => $project];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a project...']);
            return $options;
        },
        
        'teams' => function () {
            $options = DB::table('jo_teams')->pluck('team_name')->map(function ($teams) {
                return ['value' => $teams, 'label' => $teams];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a team...']);
            return $options;
        },
        
        'contacts' => function () {
            $customers = DB::table('jo_customers')->pluck('name')->map(function ($customer) {
                return ['value' => $customer, 'label' => $customer];
            })->toArray();

            $leads = DB::table('jo_leads')->pluck('name')->map(function ($lead) {
                return ['value' => $lead, 'label' => $lead];
            })->toArray();

            $clients = DB::table('jo_clients')->pluck('name')->map(function ($client) {
                return ['value' => $client, 'label' => $client];
            })->toArray();

            $options = array_merge($customers, $leads, $clients);
            array_unshift($options, ['value' => '', 'label' => 'Select a contact...']);
            return $options;
        },
        
        'Employee that generate income' => function () {
            $options = DB::table('jo_manage_employees')->pluck('first_name as value', 'first_name as label')->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an employee...']);
            return $options;
        },
        
        'invoice_number' => function () {
            $options = DB::table('jo_invoices')->pluck('invoicenumber')->map(function ($invoice) {
                return ['value' => $invoice, 'label' => $invoice];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an invoice number...']);
            return $options;
        },
        
        'product_type' => function () {
            $options = DB::table('jo_product_types')->pluck('name')->map(function ($product_types) {
                return ['value' => $product_types, 'label' => $product_types];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a product type...']);
            return $options;
        },
        
        'product_category' => function () {
            $options = DB::table('jo_product_categories')->pluck('name')->map(function ($product_categories) {
                return ['value' => $product_categories, 'label' => $product_categories];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a product category...']);
            return $options;
        },
        
        'employees_that_generate' => function () {
            $options = DB::table('jo_manage_employees')->pluck('first_name')->map(function ($employees) {
                return ['value' => $employees, 'label' => $employees];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select an employee...']);
            return $options;
        },
        
        'categories' => function () {
            $options = DB::table('jo_manage_categories')->pluck('expense_name')->map(function ($categories) {
                return ['value' => $categories, 'label' => $categories];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a category...']);
            return $options;
        },
        
        'category_name' => function () {
            $options = DB::table('jo_manage_categories')->pluck('expense_name')->map(function ($categories) {
                return ['value' => $categories, 'label' => $categories];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a category...']);
            return $options;
        },
        
        'vendor' => function () {
            $options = DB::table('jo_vendors')->pluck('name')->map(function ($vendors) {
                return ['value' => $vendors, 'label' => $vendors];
            })->toArray();
            array_unshift($options, ['value' => '', 'label' => 'Select a vendor...']);
            return $options;
        },
        
        // Add more mappings as needed
    ];

        // Default options if fieldName doesn't match specific cases
        $defaultOptions = [
            'status' => [
                ['value' => 'none', 'label' => 'None'],
                ['value' => 'open', 'label' => 'Open'],
                ['value' => 'inprogress', 'label' => 'In Progress'],
                ['value' => 'inreview', 'label' => 'In Review'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'closed', 'label' => 'Closed'],
            ],
            'priority' => [
                ['value' => 'none', 'label' => 'None'],
                ['value' => 'low', 'label' => 'Low'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'high', 'label' => 'High'],
                ['value' => 'urgent', 'label' => 'Urgent'],
            ],
            'size' => [
                ['value' => 'none', 'label' => 'None'],
                ['value' => 'large', 'label' => 'Large'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'small', 'label' => 'Small'],
                ['value' => 'tiny', 'label' => 'Tiny'],
            ],
            'currency' => [
                ['value' => 'india(INR)', 'label' => 'India(INR)'],
                ['value' => 'canadian dollar(CAD)', 'label' => 'Canadian Dollar(CAD)'],
                ['value' => 'israeli pound(ILP)', 'label' => 'Israeli Pound(ILP)'],
            ],
            'Currency' => [
                ['value' => 'india(INR)', 'label' => 'India(INR)'],
                ['value' => 'canadian dollar(CAD)', 'label' => 'Canadian Dollar(CAD)'],
                ['value' => 'israeli pound(ILP)', 'label' => 'Israeli Pound(ILP)'],
            ],

            
            'employee_bonus_type'=>[
                ['value'=>'none','label'=>'None'],
                ['value'=>'profit bonus type','label'=>'Profit Bonus Type'],
                ['value'=>'revenue based bonus','label'=>'Revenue Based Bonus'],
            ],
            'start_week_on'=>[
                ['value'=>'monday','label'=>'Monday'],
                ['value'=>'tuesday','label'=>'Tuesday'],
                ['value'=>'wednesday','label'=>'Wednesday'],
                ['value'=>'thursday','label'=>'Thursday'],
                ['value'=>'friday','label'=>'Friday'],
                ['value'=>'saturday','label'=>'Saturday'],
                ['value'=>'sunday','label'=>'Sunday'],
            
            ],
            'default_date_type'=>[
                ['value'=>'today','label'=>'Today'],
                ['value'=>'end of the month','label'=>'end Of The MOnth'],
                ['value'=>'start of the month','label'=>'Start Of The Month'],

>>>>>>> 68e4740 (Issue -#35)
            ],
            'type'=>[
                ['value'=>'cost','label'=>'Cost'],
                ['value'=>'hours','label'=>'Hours'],
<<<<<<< HEAD
            ]
        ];

        return $options[$fieldName] ?? [];
    }
}
=======
            ],
            'contact_type'=>[
                ['value'=>'client','label'=>'Client'],
                ['value'=>'customer','label'=>'Customer'],
                ['value'=>'lead','label'=>'Lead'],
            ],
            'estimate'=>[
                ['value'=>'days','label'=>'Day'],
                ['value'=>'hours','label'=>'Hours'],
                ['value'=>'minutes','label'=>'Minutes'],
            ],

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
>>>>>>> 68e4740 (Issue -#35)

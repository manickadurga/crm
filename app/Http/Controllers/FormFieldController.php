<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Field;
use App\Models\Tab;
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
            // Validate the request to ensure 'name' parameter is present
            $request->validate([
                'name' => 'required|string'
            ]);

            $tabName = $request->input('name');

            // Log the tab name received for debugging
            Log::info('Tab name received: ' . $tabName);

            // Fetch the tab from the database using tab name
            $tab = Tab::where('name', $tabName)->first();

            if (!$tab) {
                Log::info('No tab found for the given name: ' . $tabName);
                return response()->json(['error' => 'Tab not found'], 404);
            }

            $tabId = $tab->tabid;

            // Fetch blocks with their fields based on tab ID
            $blocks = Block::where('tabid', $tabId)->with('fields')->get();

            // Prepare an array to store form fields data
            $formFields = [];

            foreach ($blocks as $block) {
                $fields = [];

                foreach ($block->fields as $field) {
                    // Initialize field data array
                    $fieldData = [
                        'name' => $field->fieldname,
                        'type' => $field->uitype,
                        'label' => $field->fieldlabel,
                    ];

                    // Fetch options for specific fields if defined
                    $fieldOptions = $this->getFieldOptions($field->fieldname);
                    if (!empty($fieldOptions)) {
                        $fieldData['options'] = $fieldOptions;
                    }

                    // Set rules object based on typeofdata
                    $rules = [];

                    if (strpos($field->typeofdata, 'V~M') !== false) {
                        $rules = [
                            'required' => true,
                            'message' => 'Enter your ' . strtolower($field->fieldlabel),
                        ];
                    }

                    // Assign rules object to field data if rules are defined
                    if (!empty($rules)) {
                        $fieldData['rules'] = $rules;
                    }

                    // Push field data to fields array
                    $fields[] = $fieldData;
                }

                // Push block data to formFields array
                $formFields[] = [
                    'blockid' => $block->blockid,
                    'blockname' => $block->blocklabel,
                    'fields' => $fields,
                ];
            }

            // Log the fetched form fields and return as JSON response
            Log::info('Fetched form fields: ', $formFields);
            return response()->json($formFields);
        } catch (\Exception $e) {
            // Log and return error response in case of exception
            Log::error('Error fetching form fields: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching form fields'], 500);
        }
    }

    // Method to fetch options for specific fields (e.g., tags, projects)
    private function getFieldOptions($fieldName)
    {
        // Define field options in an associative array
        $optionsMap = [
            'tags' => function () {
                return DB::table('jo_tags')->select('tags_name as value', 'tags_name as label', 'tag_color as color')->get()->toArray();
            },
            'projects' => function () {
                return DB::table('jo_projects')->pluck('project_name')->map(function ($project) {
                    return ['value' => $project, 'label' => $project];
                })->toArray();
            },
            'project' => function () {
                return DB::table('jo_projects')->pluck('project_name')->map(function ($project) {
                    return ['value' => $project, 'label' => $project];
                })->toArray();
            },
            'contacts' => function () {
                $customers = DB::table('jo_customers')->pluck('name')->map(function ($customer) {
                    return ['value' => $customer, 'label' => $customer];
                })->toArray();
    
                $leads = DB::table('jo_leads')->pluck('name')->map(function ($lead) {
                    return ['value' => $lead, 'label' => $lead];
                })->toArray();
    
                $clients = DB::table('jo_clients')->pluck('clientsname')->map(function ($client) {
                    return ['value' => $client, 'label' => $client];
                })->toArray();
    
                // Combine all contacts into a single array
                return array_merge($customers, $leads, $clients);
            },
            'contact' => function () {
                $customers = DB::table('jo_customers')->pluck('name')->map(function ($customer) {
                    return ['value' => $customer, 'label' => $customer];
                })->toArray();
    
                $leads = DB::table('jo_leads')->pluck('name')->map(function ($lead) {
                    return ['value' => $lead, 'label' => $lead];
                })->toArray();
    
                $clients = DB::table('jo_clients')->pluck('clientsname')->map(function ($client) {
                    return ['value' => $client, 'label' => $client];
                })->toArray();
    
                // Combine all contacts into a single array
                return array_merge($customers, $leads, $clients);
            },
            'invoice_number' => function () {
                return DB::table('jo_invoices')->pluck('invoicenumber')->map(function ($invoice) {
                    return ['value' => $invoice, 'label' => $invoice];
                })->toArray();
            },
            'product_type' => function () {
                return DB::table('jo_product_types')->pluck('name')->map(function ($product_types) {
                    return ['value' => $product_types, 'label' => $product_types];
                })->toArray();
            },
            'product_category' => function () {
                return DB::table('jo_product_categories')->pluck('name')->map(function ($product_categories) {
                    return ['value' => $product_categories, 'label' => $product_categories];
                })->toArray();
            },
            'employees_that_generate' => function () {
                return DB::table('jo_employees')->pluck('first_name')->map(function ($employees) {
                    return ['value' => $employees, 'label' => $employees];
                })->toArray();
            },
            'categories' => function () {
                return DB::table('jo_manage_categories')->pluck('expense_name')->map(function ($categories) {
                    return ['value' => $categories, 'label' => $categories];
                })->toArray();
            },
            'category_name' => function () {
                return DB::table('jo_manage_categories')->pluck('expense_name')->map(function ($categories) {
                    return ['value' => $categories, 'label' => $categories];
                })->toArray();
            },
            'vendor' => function () {
                return DB::table('jo_vendors')->pluck('vendor_name')->map(function ($vendors) {
                    return ['value' => $vendors, 'label' => $vendors];
                })->toArray();
            },
            
        ];

        // Default options if fieldName doesn't match specific cases
        $defaultOptions = [
            'contact_type'=>[
              ['value'=>'customer','label'=>'CUSTOMER'],
              ['value'=>'lead','label'=>'LEAD'],
              ['value'=>'client','label'=>'CLIENT']
            ],
            'type'=>[
                ['value'=>'cost','label'=>'Cost'],
                ['value'=>'hours','label'=>'Hours']
            ],
            'invoice_status' => [
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
            'payment_method'=>[
                ['value'=>'bank transfer','label'=>'Bank Transfer'],
                ['value'=>'cash','label'=>'Cash'],
                ['value'=>'cheque','label'=>'Cheque'],
                ['value'=>'credit card','label'=>'Credit Card'],
                ['value'=>'debit','label'=>'Debit'],
                ['value'=>'online','label'=>'Online'],
            ],
            'currency' => [
                ['value' => 'india(INR)', 'label' => 'India(INR)'],
                ['value' => 'canadian dollar(CAD)', 'label' => 'Canadian Dollar(CAD)'],
                ['value' => 'israeli pound(ILP)', 'label' => 'Israeli Pound(ILP)'],
            ],
            'select_status'=>[
                ['value'=>'not billable','label'=>'Not Billable'],
            ]
        ];

        // Check if the field name exists in the options map, execute and return options if found
        if (isset($optionsMap[$fieldName])) {
            return $optionsMap[$fieldName]();
        }

        // Return default options if fieldName does not match specific cases
        return $defaultOptions[$fieldName] ?? [];
    }
}

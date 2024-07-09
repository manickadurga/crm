<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Tab;
use App\Models\Tasks;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormFieldController extends Controller
{
    public function getFormFields(Request $request)
    {
        try {
            $tabname = $request->input('name');

            if (!$tabname) {
                Log::info('Tab name is missing from the request.');
                return response()->json(['error' => 'Tab name is required'], 400);
            }

            // Log the tabname for debugging
            Log::info('Tab name received: ', ['name' => $tabname]);

            // Enable query logging
            DB::connection()->enableQueryLog();

            // Fetch the tabid from the jo_tabs table using tabname
            $tab = Tab::where('name', $tabname)->first();

            if (!$tab) {
                Log::info('No tab found for the given tabname', ['name' => $tabname]);
                return response()->json(['error' => 'Tab not found'], 404);
            }

            $tabid = $tab->tabid;

            // Fetch blocks with the given tabid, including their fields
            $blocks = Block::where('tabid', $tabid)->with('fields')->get();

            // Log the SQL queries
            Log::info('SQL Query: ', DB::getQueryLog());

            // Log the fetched blocks for debugging
            Log::info('Fetched blocks: ', $blocks->toArray());

            // Handle case where no blocks are found
            if ($blocks->isEmpty()) {
                Log::info('No blocks found for the given tabid', ['tabid' => $tabid]);
                return response()->json([]);
            }

            // Prepare the form fields array dynamically
            $formFields = [];

            foreach ($blocks as $block) {
                // Log each block being processed
                Log::info('Processing block: ', ['blockname' => $block->blocklabel]);

                $fields = [];
                foreach ($block->fields as $field) {
                    $fieldData = [
                        'name' => $field->fieldname,
                        'type' => $field->uitype,
                        'label' => $field->fieldlabel,
                    ];

                    // Add options for types 33 and 16
                    if (in_array($field->uitype, [33, 16])) {
                        $fieldData['options'] = $this->getFieldOptions($field->fieldname);
                    }

                    // Add validation rules if required
                    if (str_contains($field->typeofdata, 'V~M') || str_contains($field->typeofdata, 'D~M')) {
                        $fieldData['rules'][] = [
                            'required' => true,
                            'message' => 'Enter your ' . strtolower($field->fieldlabel),
                        ];
                    }

                    $fields[] = $fieldData;
                }

                $formFields[] = [
                    'blockid' => $block->blockid,
                    'blockname' => $block->blocklabel,
                    'fields' => $fields,
                ];

                // Log the form fields being added for each block
                Log::info('Form fields for block: ', ['blockname' => $block->blocklabel, 'fields' => $fields]);
            }

            return response()->json($formFields);
        } catch (\Exception $e) {
            // Log the error message with the exception details
            Log::error('Error fetching form fields: ', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error fetching form fields'], 500);
        }
    }

    private function getFieldOptions($fieldName)
    {
        $modelMapping = [
            'tasks' => ['model' => Tasks::class, 'select' => ['id', 'title'], 'label' => 'title'],
            'products' => ['model' => Product::class, 'select' => ['id', 'name'], 'label' => 'name'],
            'employees' => ['model' => Employee::class, 'select' => ['id', 'firstname', 'lastname'], 'label' => 'full_name'],
            'projects' => ['model' => Project::class, 'select' => ['id', 'projects'], 'label' => 'projects'],
            'expenses' => ['model' => Expense::class, 'select' => ['id', 'expense'], 'label' => 'expense'],
        ];

        if (array_key_exists($fieldName, $modelMapping)) {
            $modelClass = $modelMapping[$fieldName]['model'];
            $selectFields = $modelMapping[$fieldName]['select'];
            $labelField = $modelMapping[$fieldName]['label'];

            $items = $modelClass::select($selectFields)->get();

            return $items->map(function ($item) use ($labelField) {
                return [
                    'value' => $item->id,
                    'label' => $labelField === 'full_name' ? trim($item->firstname . ' ' . $item->lastname) : $item->$labelField,
                ];
            });
        }

        // If no specific field name matches, provide static options or handle accordingly
        return $this->getDefaultOptions($fieldName);
    }

    private function getDefaultOptions($fieldName)
    {
        $options = [
            'contacts' => DB::table('jo_customers')->pluck('name')->map(function ($name) {
                return ['value' => $name, 'label' => $name];
            })->toArray(),
            'tags' => DB::table('jo_tags')->select('tag_color', 'tags_names')->get()->map(function ($tag) {
                return ['value' => $tag->tag_color, 'label' => $tag->tags_names];
            })->toArray(),
            'projects' => DB::table('jo_projects')->pluck('projects')->map(function ($projects) {
                return ['value' => $projects, 'label' => $projects];
            })->toArray(),
            // Add more default options as needed
        ];
        $options = array_merge($options, [
            'contact_type' => [
                ['value' => 'customers', 'label' => 'CUSTOMERS'],
                ['value' => 'clients', 'label' => 'CLIENTS'],
                ['value' => 'leads', 'label' => 'LEADS'],
            ],
            'type' => [
                ['value' => 'cost', 'label' => 'Cost'],
                ['value' => 'hours', 'label' => 'Hours'],
            ],
            'currency' => [
                ['value' => 'india(INR)', 'label' => 'India(INR)'],
                ['value' => 'canadian dollar(CAD)', 'label' => 'Canadian Dollar(CAD)'],
                ['value' => 'israeli pound(ILP)', 'label' => 'Israeli Pound(ILP)'],
            ],
            'discount_suffix' => [
                ['value' => '%', 'label' => '%'],
                ['value' => 'flat', 'label' => 'Flat'],
            ],
            'taxtype' => [
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'group', 'label' => 'Group'],
            ],
            'estimate_status' => [
                ['value' => 'created', 'label' => 'Created'],
                ['value' => 'delivered', 'label' => 'Delivered'],
                ['value' => 'reviewed', 'label' => 'Reviewed'],
                ['value' => 'accepted', 'label' => 'Accepted'],
                ['value' => 'rejected', 'label' => 'Rejected'],
            ],
            'invoice_status' => [
                ['value' => 'created', 'label' => 'Created'],
                ['value' => 'auto created', 'label' => 'Auto Created'],
                ['value' => 'cancel', 'label' => 'Cancel'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'sent', 'label' => 'Sent'],
                ['value' => 'credit invoice', 'label' => 'Credit Invoice'],
                ['value' => 'paid', 'label' => 'Paid'],
            ],
            'select_invoice_type' => [
                ['value' => 'employees', 'label' => 'Employee'],
                ['value' => 'projects', 'label' => 'Projects'],
                ['value' => 'tasks', 'label' => 'Tasks'],
                ['value' => 'products', 'label' => 'Products'],
                ['value' => 'expenses', 'label' => 'Expenses'],
            ],
        ]);

        return $options[$fieldName] ?? [];
    }
}

    


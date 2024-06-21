<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Block;
use App\Models\Tab;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FormFieldController extends Controller
{
    public function getFormFields(Request $request)
    {
        try {
            $tabname = $request->input('name');

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
                        $fieldData['rules'] = [
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
            // Log the error message
            Log::error('Error fetching form fields: ', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Error fetching form fields'], 500);
        }
    }

    private function getFieldOptions($fieldName)
    {
        // This method should fetch the options for the field from your database or other source
        // Here is an example with static data, you should replace it with your actual logic
        $options = [
            'projects' => [
                ['value' => 'project1', 'label' => 'Project 1'],
                ['value' => 'project2', 'label' => 'Project 2'],
                ['value' => 'project3', 'label' => 'Project 3'],
            ],
            'contact_type' => [
                ['value' => 'customers', 'label' => 'CUSTOMERS'],
                ['value' => 'clients', 'label' => 'CLIENTS'],
                ['value' => 'leads', 'label' => 'LEADS'],
            ],
            'tags'=>[
                ['value' => 'tag1', 'label' => 'Tag 1'],
                ['value' => 'tag2', 'label' => 'Tag 2'],
                ['value' => 'tag3', 'label' => 'Tag 3'],  
            ],
            'type'=>[
                ['value'=>'cost','label'=>'Cost'],
                ['value'=>'hours','label'=>'Hours'],
            ]
        ];

        return $options[$fieldName] ?? [];
    }
}

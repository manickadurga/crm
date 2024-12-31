<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Blocks;
use App\Models\FormField;
use App\Models\Tab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class JoFieldController extends Controller
{
    /**
     * Fetch all form fields for triggers and actions dynamically.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function getFormFields(Request $request)
    // {
    //     try {
    //         $tabName = $request->input('name');

    //         // Validate that tabName is present in the request
    //         if (!$tabName) {
    //             Log::error('Tab name is missing from the request.');
    //             return response()->json(['error' => 'Tab name is required'], 400);
    //         }

    //         // Log the received tab name for debugging
    //         Log::info('Tab name received: ' . $tabName);

    //         // Fetch the tab from the database using the tab name
    //         $tab = Tab::where('name', $tabName)->first();

    //         // Handle case where no tab is found
    //         if (!$tab) {
    //             Log::error('No tab found for the given tab name', ['name' => $tabName]);
    //             return response()->json(['error' => 'Tab not found'], 404);
    //         }

    //         $tabId = $tab->tabid;
    //         DB::enableQueryLog();

    //         // Fetch blocks with their associated fields based on tab ID
    //         $blocks = Blocks::where('tabid', $tabId)->with('formfields')->get();
    //         Log::info('Blocks with formfields:', $blocks->toArray());
    //         // Log the SQL queries executed
    //         Log::info('SQL Query: ', DB::getQueryLog());

    //         // Handle case where no blocks are found
    //         if ($blocks->isEmpty()) {
    //             Log::error('No blocks found for the given tab ID', ['tabid' => $tabId]);
    //             return response()->json([]);
    //         }

    //         // Prepare an array to store form fields data dynamically
    //         $formFields = [];

    //         foreach ($blocks as $block) {
    //             // Log the processing of each block for debugging
    //             Log::info('Processing block: ' . $block->blocklabel);
    //             Log::info('FormFields for this block: ', $block->formfields->toArray());
    

    //             $fields = [];
    //             foreach ($block->formfields as $field) {
    //                 // Fetch additional details about the field using the Fields model
    //                 $fieldDetails = FormField::where('fieldid', $field->fieldid)->first();

    //                 // Prepare basic field data
    //                 $fieldData = [
    //                     'name' => $field->fieldname,
    //                     'type' => $field->uitype,
    //                     'label' => $field->fieldlabel,
    //                 ];

    //                 // Example: Fetch options for specific field types
    //                 if (in_array($field->uitype, [33, 16, 56])) {
    //                     $options = $this->getFieldOptions($field->fieldname);
    //                     if ($options) {
    //                         $fieldData['options'] = $options;
    //                     }
    //                 }

    //                 // Example: Add validation rules if specified in typeofdata
    //                 if (
    //                     str_contains($field->typeofdata, 'V~M') || 
    //                     str_contains($field->typeofdata, 'D~M') || 
    //                     str_contains($field->typeofdata, 'N~M')
    //                 ) {
    //                     $fieldData['rules'][] = [
    //                         'required' => true,
    //                         'message' => 'Enter your ' . strtolower($field->fieldlabel),
    //                     ];
    //                 }
                    
    //                 // Add the field data to the fields array
    //                 $fields[] = $fieldData;
    //             }

    //             // Add the block data with its fields to the formFields array
    //             $formFields[] = [
    //                 'blockid' => $block->blockid,
    //                 'blockname' => $block->blocklabel,
    //                 'fields' => $fields,
    //             ];

    //             // Log the form fields added for each block
    //             Log::info('Form fields for block: ', ['blockname' => $block->blocklabel, 'fields' => $formFields]);
    //         }

    //         // Return the dynamically fetched form fields as JSON response
    //         return response()->json($formFields);
    //     } catch (\Exception $e) {
    //         // Log the error encountered during form fields fetching
    //         Log::error('Error fetching form fields: ' . $e->getMessage());
    //         return response()->json(['error' => 'Error fetching form fields'], 500);
    //     }
    // }
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

        // Fetch blocks with their associated form fields based on tab ID
        $blocks = Blocks::with('formfields')->where('tabid', $tabId)->get();


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
            foreach ($block->formfields as $formField) {
                // Fetch additional details about the form field using the FormField model
                $fieldData = [
                    'name' => $formField->fieldname,
                    'type' => $formField->uitype,
                    'label' => $formField->fieldlabel,
                ];

                // Example: Fetch options for specific field types
                if (in_array($formField->uitype, [33, 16, 56])) {
                    $options = $this->getFieldOptions($formField->fieldname);
                    if ($options) {
                        $fieldData['options'] = $options;
                    }
                }

                // Example: Add validation rules if specified in typeofdata
                if (
                    str_contains($formField->typeofdata, 'V~M') || 
                    str_contains($formField->typeofdata, 'D~M') || 
                    str_contains($formField->typeofdata, 'N~M')
                ) {
                    $fieldData['rules'][] = [
                        'required' => true,
                        'message' => 'Enter your ' . strtolower($formField->fieldlabel),
                    ];
                }

                // Add the form field data to the fields array
                $fields[] = $fieldData;
            }

            // Add the block data with its form fields to the formFields array
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
     * Fetch field options dynamically (structure only; logic to be added later).
     *
     * @param string $fieldName
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFieldOptions($fieldName)
    {
        // Placeholder structure for options
        $options = []; // You can populate this based on your custom logic later

        return response()->json([
            'fieldname' => $fieldName,
            'options' => $options,
        ]);
    }
}


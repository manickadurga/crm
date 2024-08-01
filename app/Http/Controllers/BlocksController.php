<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Field;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Exception;

class BlocksController extends Controller
{
    public function getBlocksByModule($moduleName)
    {
        $tab = DB::table('jo_tabs')->where('name', $moduleName)->first();

        if (!$tab) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $blocks = Block::where('tabid', $tab->tabid)->get();

        return response()->json($blocks);
    }

    public function addBlock(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'module_name' => 'required|string',
                'block_label' => 'required|string',
            ]);

            // Retrieve tabid from jo_tabs based on module_name
            $tab = DB::table('jo_tabs')->where('name', $validatedData['module_name'])->value('tabid');

            if (!$tab) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Determine the next sequence number for the given tabid
            $nextSequence = Block::where('tabid', $tab)->max('sequence') + 1;

            // Create a new Block instance
            $block = new Block;
            $block->tabid = $tab;
            $block->blocklabel = $validatedData['block_label'];
            $block->sequence = $nextSequence; // Set the calculated sequence number
            // Optionally set default values for other fields
            $block->show_title = 0;
            $block->visible = 0;
            $block->create_view = 0;
            $block->edit_view = 0;
            $block->detail_view = 0;
            $block->display_status = 1;
            $block->iscustom = 0;
            $block->created_at = now();
            $block->updated_at = now();
            $block->orgid = null; // Adjust as necessary

            // Save the block
            $block->save();

            return response()->json(['message' => 'Block added successfully', 'block' => $block]);
        } catch (QueryException $e) {
            Log::error('Database error during block creation: ' . $e->getMessage());
            return response()->json(['error' => 'Database error occurred', 'message' => $e->getMessage()], 500);
        } catch (ValidationException $e) {
            Log::error('Validation error during block creation: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation error', 'message' => $e->errors()], 400);
        } catch (Exception $e) {
            Log::error('Unexpected error during block creation: ' . $e->getMessage());
            return response()->json(['error' => 'Unexpected error occurred'], 500);
        }
    }

    public function deleteBlock($blockId)
    {
        try {
            // Find the block by ID in the jo_blocks table
            $block = Block::find($blockId);

            if (!$block) {
                return response()->json(['error' => 'Block not found'], 404);
            }

            // Delete the block
            $block->delete();

            // Update the sequence of existing blocks
            $this->updateBlockSequence($block->tabid);

            return response()->json(['message' => 'Block deleted successfully']);
        } catch (QueryException $e) {
            Log::error('Database error during block deletion: ' . $e->getMessage());
            return response()->json(['error' => 'Database error occurred', 'message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            Log::error('Unexpected error during block deletion: ' . $e->getMessage());
            return response()->json(['error' => 'Unexpected error occurred'], 500);
        }
    }

    public function updateBlockSequence($tabid)
    {
        $blocks = Block::where('tabid', $tabid)->orderBy('sequence')->get();
        $sequence = 1;
        foreach ($blocks as $block) {
            if ($block->sequence !== $sequence) {
                $block->sequence = $sequence;
                $block->save();
            }
            $sequence++;
        }
    }

    public function addField(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'module_name' => 'required|string',
                'field_name' => 'required|string',
                'field_label' => 'required|string',
                'mandatory' => 'boolean', // Add validation for the mandatory field
            ]);

            // Retrieve tabid from jo_tabs based on module_name
            $tab = DB::table('jo_tabs')->where('name', $validatedData['module_name'])->first();

            if (!$tab) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Check if fieldname already exists for the given tabid
            $existingField = Field::where('tabid', $tab->tabid)
                ->where('fieldname', $validatedData['field_name'])
                ->exists();

            if ($existingField) {
                return response()->json(['error' => 'Field name already exists for this module'], 400);
            }

            // Determine the next sequence number for the given tabid
            $nextSequence = Field::where('tabid', $tab->tabid)->max('sequence') + 1;

            // Determine the next fieldid
            $nextFieldId = Field::max('fieldid') + 1;

            // Create a new Field instance
            $field = new Field;
            $field->fieldid = $nextFieldId;
            $field->tabid = $tab->tabid;
            $field->columnname = strtolower(str_replace(' ', '_', $validatedData['field_name']));
            $field->tablename = 'jo_' . strtolower(str_replace(' ', '_', $validatedData['module_name']));
            $field->generatedtype = 0; // Example default value
            $field->uitype = ''; // Example default value for UI type (adjust as necessary)
            $field->fieldname = $validatedData['field_name'];
            $field->fieldlabel = $validatedData['field_label'];
            $field->sequence = $nextSequence; // Set the calculated sequence number
            // Optionally set default values for other fields
            $field->readonly = 0;
            $field->presence = 1; // Example default value, adjust as necessary
            $field->maximumlength = 100;
            $field->block = 9; // Example block ID, adjust as necessary
            $field->displaytype = 1;
            $field->typeofdata = $request->has('mandatory') && $request->mandatory ? 'V~M' : 'V~O'; // Set typeofdata based on the mandatory field
            $field->quickcreate = 1;
            $field->quickcreatesequence = 0;
            $field->info_type = 'BAS'; // Example info_type, adjust as necessary
            $field->masseditable = 0;
            $field->helpinfo = null; // Adjust as necessary
            $field->summaryfield = 0;
            $field->headerfield = 0;
            $field->created_at = now();
            $field->updated_at = now();
            $field->orgid = null; // Adjust as necessary

            // Save the field
            $field->save();

            return response()->json(['message' => 'Field added successfully', 'field' => $field], 201);
        } catch (QueryException $e) {
            Log::error('Database error during field creation: ' . $e->getMessage(), ['errorInfo' => $e->errorInfo]);
            return response()->json(['error' => 'Failed to add field. Database error occurred.', 'details' => $e->getMessage()], 500);
        } catch (ValidationException $e) {
            Log::error('Validation error during field creation: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation error', 'message' => $e->errors()], 400);
        } catch (Exception $e) {
            Log::error('Unexpected error during field creation: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add field. Unexpected error occurred.'], 500);
        }
    }

    public function deleteField($fieldId)
    {
        try {
            // Find the field by fieldid in jo_fields and delete it
            $deletedField = Field::find($fieldId);

            if (!$deletedField) {
                return response()->json(['error' => 'Field not found'], 404);
            }

            $tabId = $deletedField->tabid;

            $deletedField->delete();

            // Update the sequence of existing fields
            $this->updateFieldSequence($tabId);

            return response()->json(['message' => 'Field deleted successfully']);
        } catch (QueryException $e) {
            Log::error('Database error during field deletion: ' . $e->getMessage(), ['errorInfo' => $e->errorInfo]);
            return response()->json(['error' => 'Failed to delete field. Database error occurred.'], 500);
        } catch (Exception $e) {
            Log::error('Unexpected error during field deletion: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete field. Unexpected error occurred.'], 500);
        }
    }

    public function updateFieldSequence($tabid)
    {
        $fields = Field::where('tabid', $tabid)->orderBy('sequence')->get();
        $sequence = 1;
        foreach ($fields as $field) {
            if ($field->sequence !== $sequence) {
                $field->sequence = $sequence;
                $field->save();
            }
            $sequence++;
        }
    }

    public function getFieldsByModule($moduleName)
    {
        try {
            // Retrieve tabid from jo_tabs based on module_name
            $tab = DB::table('jo_tabs')->where('name', $moduleName)->first();

            if (!$tab) {
                return response()->json(['error' => 'Module not found'], 404);
            }

            // Retrieve fields from jo_fields based on tabid
            $fields = Field::where('tabid', $tab->tabid)->get();

            return response()->json(['fields' => $fields]);
        } catch (\Exception $e) {
            Log::error('Error fetching fields: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch fields. Unexpected error occurred.'], 500);
        }
    }

    public function listModulesBlocksFields()
    {
        try {
            // Fetch all modules with related blocks and fields
            $modules = Module::with(['blocks', 'fields'])->get();

            return response()->json(['modules' => $modules]);
        } catch (Exception $e) {
            Log::error('Error listing modules, blocks, and fields: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Failed to list modules, blocks, and fields. Unexpected error occurred.'], 500);
        }
    }
}

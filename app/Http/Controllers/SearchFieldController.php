<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Field;
use App\Models\Tab;
use Illuminate\Support\Facades\Schema;


class SearchFieldController extends Controller
{
    public function search(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'tab' => 'required|string',
            'name' => 'required|string',
            'value' => 'required|string'
        ]);

        $tabName = $validatedData['tab'];
        $fieldName = $validatedData['name'];
        $value = $validatedData['value'];

        // Fetch tabid from the Tab model based on the tab name
        $tab = Tab::where('name', $tabName)->first(); // Adjust field name if needed

        if (!$tab) {
            return response()->json(['error' => 'Tab name not found'], 404);
        }

        $tabId = $tab->tabid;

        // Fetch the table name from the Field model based on tabid
        $field = Field::where('tabid', $tabId)->first();

        if (!$field) {
            return response()->json(['error' => 'Table name not found for given tab'], 404);
        }

        $tableName = $field->tablename;

        // Check if the table exists
        if (!Schema::hasTable($tableName)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        // Check if the field exists in the table
        if (!Schema::hasColumn($tableName, $fieldName)) {
            return response()->json(['error' => 'Field not found'], 404);
        }

        // Perform the query
        $results = DB::table($tableName)
            ->where($fieldName, 'like', "%$value%")
            ->get();

        return response()->json($results);
    }
}


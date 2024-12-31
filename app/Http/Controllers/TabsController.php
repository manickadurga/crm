<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Tab;

class TabsController extends Controller
{
    public function showFieldsByTabId(Request $request, $tabid)
    {
        // Validate the tabid parameter
        $request->validate([
            'tabid' => 'required|integer',
        ]);

        // Find the tab by tabid
        $tab = Tab::find($tabid);

        if (!$tab) {
            return response()->json(['error' => 'Tab not found'], 404);
        }

        // Find fields associated with the tabid
        $fields = Field::where('tabid', $tabid)->get();

        // Prepare the response structure
        $response = [
            'tabid' => $tabid,
            'fields' => []
        ];

        // Iterate through fields and add them to the response
        foreach ($fields as $field) {
            $response['fields'][] = [
                'fieldid' => $field->fieldid,
                'tabid' => $field->tabid, // Assuming you want to include tabid in each field object
                // Add other fields as needed
            ];
        }

        return response()->json($response);
    }
}

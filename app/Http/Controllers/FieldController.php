<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Customers;
use App\Models\ReportFilter; // Assuming this is your ReportFilter model

class FieldController extends Controller
{
    public function getFieldById($id)
    {
        // Fetch the field with the given id
        $field = Field::find($id);

        // Check if the field exists
        if ($field) {
            // Return the field data
            return response()->json([
                'success' => true,
                'data' => $field
            ]);
        } else {
            // Return a not found response
            return response()->json([
                'success' => false,
                'message' => 'Field not found.'
            ], 404);
        }
    }

    public function getCustomersByFieldIdAndValue($id, Request $request)
    {
        // Fetch the field with the given id
        $field = Field::find($id);

        // Check if the field exists
        if (!$field) {
            return response()->json([
                'success' => false,
                'message' => 'Field not found.'
            ], 404);
        }

        // Validate the request input
        $request->validate([
            'value' => 'required|string',
        ]);

        // Retrieve the column name from the field data
        $columnname = $field->columnname;
        $value = $request->value;

        // Retrieve the customers based on the column name and value
        $customers = Customers::where($columnname, $value)
            ->select('name', 'primary_email', 'primary_phone')
            ->get();

        // Check if any customers are found
        if ($customers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No customers found with the given criteria.'
            ], 404);
        }

        // Return the customers data
        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }


    public function show($fieldId)
    {
        // Fetch the field from the database
        $field = Field::where('fieldid', $fieldId)->first();

        if (!$field) {
            return response()->json(['error' => 'Field not found.'], 404);
        }

        // Extract the column name
        $columnName = $field->columnname;

        // You can return the column name or any other data as needed
        return response()->json(['column_name' => $columnName]);
    }
}

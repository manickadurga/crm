<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JoField;
use Illuminate\Database\QueryException;

class FormFieldController extends Controller
{
    public function getFormFields()
    {
        try {
            // Retrieve the form fields where tabid is equal to 1
            $formFields = JoField::where('tabid', 1)->get()->toArray();
            return response()->json($formFields);
        } catch (QueryException $e) {
            // Log the error for debugging purposes
            // You can also return a custom error response
            return response()->json(['error' => 'Failed to fetch form fields', 'message' => $e->getMessage()], 500);
        }
    }
}

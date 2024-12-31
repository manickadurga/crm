<?php 

namespace App\Http\Controllers;

use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActionController extends Controller
{
    // Display a listing of the actions
    public function index()
    {
        $actions = Action::all();
        return response()->json($actions);
    }

    // Store a newly created action
    public function store(Request $request)
    {
        Log::info('Request Data:', $request->all());
    
        $request->validate([
            'action_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'action_data' => 'nullable|array',
        ]);
    
        try {
            Log::info('Creating Action with Data:', $request->all());
            $action = Action::create($request->all());
            return response()->json($action, 201);
        } catch (\Exception $e) {
            Log::error('Error creating action:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Unable to create action.'], 500);
        }
    }
    

    // Display the specified action
    public function show($id)
    {
        $action = Action::find($id);

        if (!$action) {
            return response()->json(['message' => 'Action not found'], 404);
        }

        return response()->json($action);
    }

    // Update the specified action
    public function update(Request $request, $id)
    {
        $action = Action::find($id);

        if (!$action) {
            return response()->json(['message' => 'Action not found'], 404);
        }

        $request->validate([
            'action_name' => 'string|max:255',
            'type' => 'string|max:255',
            'action_data' => 'array',
        ]);

        $action->update($request->all());
        return response()->json($action);
    }

    // Remove the specified action
    public function destroy($id)
    {
        $action = Action::find($id);

        if (!$action) {
            return response()->json(['message' => 'Action not found'], 404);
        }

        $action->delete();
        return response()->json(['message' => 'Action deleted successfully']);
    }
}

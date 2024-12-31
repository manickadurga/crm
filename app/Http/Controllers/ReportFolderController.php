<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ReportFolder;

class ReportFolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $folders = ReportFolder::all();
        return response()->json($folders);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        // Validate incoming request data
        $validatedData = $request->validate([
           // 'folderid' => 'required|integer',
            'foldername' => 'required|string|max:255',
            'description' => 'required|string',
            'state' => 'required|string|max:255',
        ]);

        // Create a new report folder with validated data
        $folder = ReportFolder::create($validatedData);

        // Return a JSON response with the created folder and status code 201 (Created)
        return response()->json($folder, 201);
    } catch (\Exception $e) {
        // Log the error for debugging purposes
        Log::error('Failed to store report folder: ' . $e->getMessage());

        // Return a JSON response with an error message and status code 500 (Internal Server Error)
        return response()->json(['error' => 'Failed to store report folder. Please check your input data.', 'details' => $e->getMessage()], 500);
    }
}


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $folder = ReportFolder::findOrFail($id);
        return response()->json($folder);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            //'folderid' => 'required|integer',
            'foldername' => 'required|string|max:255',
            'description' => 'required|string',
            'state' => 'required|string|max:255',
        ]);

        $folder = ReportFolder::findOrFail($id);
        $folder->update($validatedData);

        return response()->json($folder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $folder = ReportFolder::findOrFail($id);
        $folder->delete();

        return response()->json(null, 204);
    }
}

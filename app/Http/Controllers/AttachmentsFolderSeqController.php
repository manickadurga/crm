<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttachmentsFolderSeq;

class AttachmentsFolderSeqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $folders = AttachmentsFolderSeq::all();
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
        $validatedData = $request->validate([
           'id' => 'integer',
        ]);

        $folder = AttachmentsFolderSeq::create($validatedData);

        return response()->json($folder, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $folder = AttachmentsFolderSeq::findOrFail($id);
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
            'id' => 'integer',
        ]);

        $folder = AttachmentsFolderSeq::findOrFail($id);
        $folder->update($validatedData);

        return response()->json($folder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $folder = AttachmentsFolderSeq::findOrFail($id);
        $folder->delete();

        return response()->json(null, 204);
    }
}

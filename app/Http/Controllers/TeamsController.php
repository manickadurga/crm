<?php

namespace App\Http\Controllers;

use App\Models\Tags;
use App\Models\Teams;

use Illuminate\Http\Request;

class TeamsController extends Controller
{
    public function index()
    {
        $teams = Teams::all();
        return response()->json($teams);
    }

    public function show($id)
    {
        $teams = Teams::find($id);
        if (!$teams) {
            return response()->json(['message' => 'teams not found'], 404);
        }
        return response()->json($teams);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'image' => 'nullable|image',
            'team_name' => 'nullable|string|max:255',
            'add_or_remove_projects' => 'nullable|string',
            'add_or_remove_managers' => 'required|string',
            'add_or_remove_members' => 'required|string|max:255',
            'tags' => 'nullable|array',
            'tags.*.tags_name' => 'exists:jo_tags,tags_name',
            'tags.*.tag_color' => 'exists:jo_tags,tag_color',
            'orgid'=>'nullable|integer',
        ]);
        $tags = $validatedData['tags'] ?? []; // Retrieve tags from validated data

        $team = Teams::create($validatedData);

        // Associate tags with the team if provided
        if (!empty($tags)) {
            foreach ($tags as $tagName) {
                $tag = Tags::firstOrCreate(['tags_name' => $tagName]);
                $team->tags()->attach($tag->id);
            }
        }
        

        //$teams = Teams::create($validatedData);
        return response()->json($team, 201);
    }

    public function update(Request $request, $id)
    {
        $teams = Teams::find($id);
        if (!$teams) {
            return response()->json(['message' => 'teams not found'], 404);
        }

        $validatedData = $request->validate([
            'image' => 'nullable|image',
            'team_name' => 'nullable|string|max:255',
            'add_or_remove_projects' => 'nullable|string',
            'add_or_remove_managers' => 'required|string',
            'add_or_remove_members' => 'required|string',
            'tags' => 'nullable|array|max:255',
            'orgid'=>'nullable|integer',
        ]);

        $teams->update($validatedData);
        return response()->json($teams);
    }

    public function destroy($id)
    {
        $teams = Teams::find($id);
        if (!$teams) {
            return response()->json(['message' => 'teams not found'], 404);
        }

        $teams->delete();
        return response()->json(['message' => 'teams deleted successfully']);
    }
}

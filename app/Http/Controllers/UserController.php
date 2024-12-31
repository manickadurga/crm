<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        $roles = Role::all();
        $roles = $roles->pluck('name')->toArray();
        // $roles = ['SUPER_ADMIN', 'ADMIN', 'DATA_ENTRY', 'EMPLOYEE', 'CANDIDATE', 'MANAGER', 'VIEWER', 'INTERVIEWER'];
        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        $roles = $roles->pluck('name')->toArray();
        // $roles = ['SUPER_ADMIN', 'ADMIN', 'DATA_ENTRY', 'EMPLOYEE', 'CANDIDATE', 'MANAGER', 'VIEWER', 'INTERVIEWER'];
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // Log the incoming request
        Log::info('Received request to create user:', $request->all());
    
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|string',
        ]);
    
        Log::info('Validation successful.');
    
        try {
            // Create and save the user
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
                'first_name' => $request->input('first_name', null),
                'last_name' => $request->input('last_name', null),
                'imageurl' => $request->input('imageurl', null),
                'applied_date' => $request->input('applied_date', null),
                'rejection_date' => $request->input('rejection_date', null),
            ]);
    
            Log::info('User created successfully.', ['user_id' => $user->id]);
    
            return response()->json(['success' => 'User created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error with details
            Log::error('Error creating user:', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return response()->json(['error' => 'Failed to create user.'], 500);
        }
    }
    



    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $roles = $roles->pluck('name')->toArray();
        // $roles = ['SUPER_ADMIN', 'ADMIN', 'DATA_ENTRY', 'EMPLOYEE', 'CANDIDATE', 'MANAGER', 'VIEWER', 'INTERVIEWER'];
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|unique:users,name,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->role;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->imageurl = $request->imageurl;
        $user->applied_date = $request->applied_date;
        $user->rejection_date = $request->rejection_date;
        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}

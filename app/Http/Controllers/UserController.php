<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

use Illuminate\Support\Facades\Hash;


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
        $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'role' => 'required'
        ]);

        $user = new User;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->imageurl = $request->imageurl;
        $user->applied_date = $request->applied_date;
        $user->rejection_date = $request->rejection_date;
        $user->save();

        return redirect()->route('users.index')->with('success', 'User created successfully.');
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
            'username' => 'required|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required'
        ]);

        $user->username = $request->username;
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

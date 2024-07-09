<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
public function index(): View
{
    return view('auth.login');
}

public function registration(): View
{
    return view('auth.registration');
}

public function postLogin(Request $request): RedirectResponse
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6',
    ], [
        'email.required' => 'Email is required',
        'email.email' => 'Email must be a valid email address',
        'password.required' => 'Password is required',
        'password.min' => 'Password must be at least 6 characters',
    ]);

    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        return redirect()->intended('/')
            ->withSuccess('You have successfully logged in');
    }
    return redirect("login")->withErrors('Oops! You have entered invalid credentials');
}

public function postRegistration(Request $request): RedirectResponse
{
    $request->validate([
        'username' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $data = $request->all();
    $user = $this->create($data);
    Auth::login($user);
    return redirect("dashboard")->withSuccess('Great! You have successfully registered and logged in');
}

public function dashboard(): View
{
    if (Auth::check()) {
        return view('dashboard');
    }
    return redirect("login")->withErrors('Oops! You do not have access');
}

public function create(array $data): User
{
    return User::create([
        'username' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
    ]);
}

public function logout(): RedirectResponse
{
    Session::flush();
    Auth::logout();
    return redirect('login')->withSuccess('You have successfully logged out');
}
}
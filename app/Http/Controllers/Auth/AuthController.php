<?php
/**
 * AuthController.php
 * AuthController file for handling the login, registration and logout functionalities
 * Author: @Smackcoders
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
<<<<<<< HEAD
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
            'email' => 'required',
            'password' => 'required',
        ]);
=======

class AuthController extends Controller {

    /**
     * Function to return the login view
     * @return View
     */
    public function index(): View {
        return view('auth.login');
    }

    /**
     * Function to return the registration view
     * @return View
     */
    public function registration(): View {
        return view('auth.registration');
    }

    /**
     * Function to validate the login credentials
     * @param Request $request
     * @return RedirectResponse
     */
    public function postLogin(Request $request): RedirectResponse {
        $request->validate(['email' => 'required', 'password' => 'required',]);
>>>>>>> 549012b0dd6cf19c70d5a54c6fbe23d9102c6948
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended('/')->withSuccess('You have Successfully loggedin');
        }
<<<<<<< HEAD
        return redirect("login")->withSuccess('Oppes! You have entered invalid credentials');
    }

    public function postRegistration(Request $request): RedirectResponse
    { 
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
=======
        return redirect("login")->withSuccess('Opps! You have entered invalid credentials');
    }

    /**
     * Function to validate the registration credentials
     * @param Request $request
     * @return RedirectResponse
     */
    public function postRegistration(Request $request): RedirectResponse {
        $request->validate(['name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required|min:6',]);
>>>>>>> 549012b0dd6cf19c70d5a54c6fbe23d9102c6948
        $data = $request->all();
        $check = $this->create($data);
        return redirect("dashboard")->withSuccess('Great! You have Successfully loggedin');
    }

<<<<<<< HEAD
    public function dashboard(): View
    {
        if(Auth::check()){
=======
    /**
     * Function to return the dashboard view
     * @return View
     */
    public function dashboard(): View {
        if (Auth::check()) {
>>>>>>> 549012b0dd6cf19c70d5a54c6fbe23d9102c6948
            return view('dashboard');
        }
        return redirect("login")->withSuccess('Opps! You do not have access');
    }
<<<<<<< HEAD
    
    public function create(array $data)
    {
        return User::create([
        'username' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password'])]);
    }

    public function logout(): RedirectRespons
    {
=======

//TODO: Change the function name, as it is not clear. Add a comment to explain the function
    public function create(array $data) {
        return User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
    }

    /**
     * Function to logout the user
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse {
>>>>>>> 549012b0dd6cf19c70d5a54c6fbe23d9102c6948
        Session::flush();
        Auth::logout();
        return Redirect('login');
    }
}

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
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended('/')->withSuccess('You have Successfully loggedin');
        }
        return redirect("login")->withSuccess('Opps! You have entered invalid credentials');
    }

    /**
     * Function to validate the registration credentials
     * @param Request $request
     * @return RedirectResponse
     */
    public function postRegistration(Request $request): RedirectResponse {
        $request->validate(['name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required|min:6',]);
        $data = $request->all();
        $check = $this->create($data);
        return redirect("dashboard")->withSuccess('Great! You have Successfully loggedin');
    }

    /**
     * Function to return the dashboard view
     * @return View
     */
    public function dashboard(): View {
        if (Auth::check()) {
            return view('dashboard');
        }
        return redirect("login")->withSuccess('Opps! You do not have access');
    }

//TODO: Change the function name, as it is not clear. Add a comment to explain the function
    public function create(array $data) {
        return User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
    }

    /**
     * Function to logout the user
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse {
        Session::flush();
        Auth::logout();
        return Redirect('login');
    }
}

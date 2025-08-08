<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class authController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        // if (Auth::check()) {
        //     // The user is logged in
        //     return 1;
        // } else {
        //     // The user is not logged in
        //     return 0;
        // }
        return view('auth.login');
    }

    // Handle the login request
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $data = User::where('email', $request->email)->where('status', 1)->first();
        if ($data) {
            if ($request->user == 'admin') {
                if ($data->role_id != 3) {
                    if (Auth::attempt($credentials)) {
                        $request->session()->regenerate();
                        // return redirect()->route('site.index');
                        $route = $this->redirectDash();
                        return redirect()->route($route);
                    } else {
                        return back()->withErrors([
                            'email' => 'Invalid Credentials!',
                        ]);
                    }
                } else {
                    return back()->withErrors([
                        'email' => 'Invalid Credentials!',
                    ]);
                }
            }
            if ($request->user == 'dashboard') {
                if ($data->role_id == 3) {
                    if (Auth::attempt($credentials)) {
                        $request->session()->regenerate();
                        // return redirect()->route('site.index');
                        $route = $this->redirectDash();
                        return redirect()->route($route);
                    } else {
                        return back()->withErrors([
                            'email' => 'Invalid Credentials!',
                        ]);
                    }
                } else {
                    return back()->withErrors([
                        'email' => 'Invalid Credentials!',
                    ]);
                }
            }
        } else {
            return back()->withErrors([
                'email' => 'Invalid Credentials!',
            ]);
        }
    }

    public function redirectDash()
    {
        $redirect = '';

        if (Auth::user() && Auth::user()->role_id == 1) {
            $redirect = 'site.index';
        } else if (Auth::user() && Auth::user()->role_id == 2) {
            $redirect = 'site.index';
        } else if (Auth::user() && Auth::user()->role_id == 3) {
            $redirect = 'dashboard/home';
        } else {
            $redirect = '/noaccess';
        }

        return $redirect;
    }

    // Show the registration form
    public function showRegistrationForm()
    {
        // echo Auth::id();
        return view('auth.register');
    }

    // Handle the registration request
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'role_id' => 1,
            // 'can_edit' => 1,
            // 'status' => 1,
        ]);

        Auth::login($user);

        // return redirect()->intended('dashboard');
        return 'user created';
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function noaccess()
    {
        return view('noaccess');
    }
}

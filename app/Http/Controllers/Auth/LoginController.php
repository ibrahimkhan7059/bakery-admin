<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => [
                    'required',
                    'string',
                    'email:rfc,dns',
                    'max:255',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                    'exists:users,email'
                ],
                'password' => ['required', 'min:8'],
            ], [
                'email.required' => 'Please enter your email address.',
                'email.string' => 'Email must be a valid string.',
                'email.email' => 'Please enter a valid email address format.',
                'email.max' => 'Email address cannot exceed 255 characters.',
                'email.regex' => 'Please enter a properly formatted email address.',
                'email.exists' => 'This email is not registered with us.',
                'password.required' => 'Please enter your password.',
                'password.min' => 'Password must be at least 8 characters.',
            ]);

            $credentials = $request->only('email', 'password');
            $remember = $request->filled('remember');

            if (Auth::attempt($credentials, $remember)) {
                $request->session()->regenerate();
                session(['last_activity' => Carbon::now()]);

                // Show welcome back message using SweetAlert
                return redirect()->intended('dashboard')->with('login_success', 'Welcome back!');
            }

            throw ValidationException::withMessages([
                'password' => ['The provided credentials are incorrect.'],
            ]);

        } catch (ValidationException $e) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors($e->errors());
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('message', 'You have been logged out successfully.');
    }
} 
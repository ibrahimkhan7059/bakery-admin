<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Notifications\WelcomeCustomerNotification;

class AuthController extends Controller
{
    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:users',
            'phone'   => ['required', 'regex:/^(03[0-9]{9}|\+923[0-9]{9})$/', 'unique:users'],
            'address' => 'required|string|max:500',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'address'  => $request->address,
            'role'     => 'customer',
            'password' => Hash::make($request->password),
        ]);

        // Send welcome email in background (non-blocking)
        // Temporarily disabled for faster registration during development
        // $user->notify(new WelcomeCustomerNotification($user->name));

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please login.',
            'user'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
            ]
        ], 201);
    }

    /**
     * Login user with email and password
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Verify OTP and login
     */
    // public function verifyOtp(Request $request)
    // {
    //     $request->validate([
    //         'phone' => ['required', 'regex:/^(03[0-9]{9}|\+923[0-9]{9})$/'],
    //         'otp'   => 'required|digits:4'
    //     ]);

    //     $user = User::where('phone', $request->phone)
    //                 ->where('otp', $request->otp)
    //                 ->first();

    //     if (!$user) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid OTP or phone number'
    //         ], 401);
    //     }

    //     $user->otp = null;
    //     $user->save();

    //     $token = $user->createToken('mobile-app-token')->plainTextToken;

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Login successful',
    //         'user' => [
    //             'id'      => $user->id,
    //             'name'    => $user->name,
    //             'email'   => $user->email,
    //             'phone'   => $user->phone,
    //             'address' => $user->address,
    //         ],
    //         'token' => $token
    //     ]);
    // }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}

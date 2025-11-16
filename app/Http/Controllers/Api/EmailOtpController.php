<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EmailOtpController extends Controller
{
    /**
     * Send OTP to email for signup
     */
    public function sendSignupOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate 6-digit OTP
        $otp = random_int(100000, 999999);

        // Store OTP and user data in cache for 10 minutes
        $cacheKey = 'email_otp_signup_' . $request->email;
        Cache::put($cacheKey, [
            'otp' => $otp,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => $request->password,
            'attempts' => 0,
        ], now()->addMinutes(10));

        // Send OTP via email
        try {
            Mail::raw(
                "🎂 Welcome to BakeHub!\n\n" .
                "Your verification code is: $otp\n\n" .
                "This code will expire in 10 minutes.\n\n" .
                "If you didn't request this code, please ignore this email.\n\n" .
                "Happy Baking! 🍰",
                function ($message) use ($request) {
                    $message->to($request->email)
                        ->subject('BakeHub - Email Verification Code');
                }
            );

            \Log::info('📧 Email OTP sent', [
                'email' => $request->email,
                'otp' => $otp // Remove in production
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email',
                'email' => $request->email,
                'expires_in' => 600, // 10 minutes in seconds
                'debug_otp' => $otp // REMOVE IN PRODUCTION!
            ]);

        } catch (\Exception $e) {
            \Log::error('Email sending failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP email',
                'error' => $e->getMessage(),
                'debug_otp' => $otp // For testing without email
            ], 500);
        }
    }

    /**
     * Verify OTP and create user account
     */
    public function verifySignupOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cacheKey = 'email_otp_signup_' . $request->email;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired or not found. Please request a new one.'
            ], 400);
        }

        // Check attempts
        if ($cachedData['attempts'] >= 5) {
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new OTP.'
            ], 429);
        }

        // Verify OTP
        if ($cachedData['otp'] != $request->otp) {
            // Increment attempts
            $cachedData['attempts']++;
            Cache::put($cacheKey, $cachedData, now()->addMinutes(10));

            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
                'attempts_left' => 5 - $cachedData['attempts']
            ], 400);
        }

        // OTP is correct - Create user
        try {
            $user = User::create([
                'name' => $cachedData['name'],
                'email' => $cachedData['email'],
                'phone' => $cachedData['phone'],
                'address' => $cachedData['address'],
                'password' => Hash::make($cachedData['password']),
                'email_verified_at' => now(),
                'role' => 'customer',
            ]);

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Clear OTP cache
            Cache::forget($cacheKey);

            \Log::info('✅ User registered via Email OTP', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'role' => $user->role,
                ],
                'token' => $token
            ]);

        } catch (\Exception $e) {
            \Log::error('User creation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cacheKey = 'email_otp_signup_' . $request->email;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return response()->json([
                'success' => false,
                'message' => 'No pending signup found. Please start signup again.'
            ], 400);
        }

        // Generate new OTP
        $otp = random_int(100000, 999999);
        $cachedData['otp'] = $otp;
        $cachedData['attempts'] = 0;

        // Update cache
        Cache::put($cacheKey, $cachedData, now()->addMinutes(10));

        // Send new OTP via email
        try {
            Mail::raw(
                "🎂 BakeHub Verification\n\n" .
                "Your NEW verification code is: $otp\n\n" .
                "This code will expire in 10 minutes.\n\n" .
                "Happy Baking! 🍰",
                function ($message) use ($request) {
                    $message->to($request->email)
                        ->subject('BakeHub - New Verification Code');
                }
            );

            \Log::info('📧 Email OTP resent', [
                'email' => $request->email,
                'otp' => $otp
            ]);

            return response()->json([
                'success' => true,
                'message' => 'New OTP sent to your email',
                'expires_in' => 600,
                'debug_otp' => $otp // REMOVE IN PRODUCTION!
            ]);

        } catch (\Exception $e) {
            \Log::error('Email resend failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP',
                'error' => $e->getMessage(),
                'debug_otp' => $otp // For testing
            ], 500);
        }
    }
}

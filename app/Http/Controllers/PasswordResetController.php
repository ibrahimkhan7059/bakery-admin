<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class PasswordResetController extends Controller
{
    /**
     * Send password reset OTP to email
     */
    public function sendResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;

        // Check if user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address'
            ], 404);
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Store OTP in cache with 10-minute expiry
        $cacheKey = "password_reset_otp_{$email}";
        Cache::put($cacheKey, [
            'otp' => $otp,
            'attempts' => 0,
        ], now()->addMinutes(10));

        // Send email with OTP
        try {
            Mail::raw(
                "Your BakeHub password reset code is: {$otp}\n\nThis code will expire in 10 minutes.\n\nIf you didn't request this, please ignore this email.",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('🔐 BakeHub Password Reset Code');
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Password reset code sent to your email',
                'debug_otp' => $otp, // Remove in production
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP and reset password
     */
    public function verifyOtpAndReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $otp = $request->otp;
        $password = $request->password;

        // Get cached OTP data
        $cacheKey = "password_reset_otp_{$email}";
        $otpData = Cache::get($cacheKey);

        if (!$otpData) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired or invalid. Please request a new one.'
            ], 400);
        }

        // Check attempts
        if ($otpData['attempts'] >= 5) {
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new OTP.'
            ], 429);
        }

        // Verify OTP
        if ($otpData['otp'] != $otp) {
            $otpData['attempts']++;
            Cache::put($cacheKey, $otpData, now()->addMinutes(10));

            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
                'attempts_left' => 5 - $otpData['attempts']
            ], 400);
        }

        // OTP is correct, update password
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->password = bcrypt($password);
        $user->save();

        // Clear OTP cache
        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully! You can now sign in with your new password.',
        ]);
    }
}

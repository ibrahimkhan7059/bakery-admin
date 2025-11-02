<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'sometimes|string|in:android,ios',
            'user_id' => 'sometimes|integer|exists:users,id',
            'user_email' => 'sometimes|email|exists:users,email'
        ]);

        // Try to get user ID from auth first, then from request, then from email
        $userId = Auth::id() ?? $request->user_id;
        
        // If user_email is provided, get user_id from email
        if (!$userId && $request->user_email) {
            $user = \App\Models\User::where('email', $request->user_email)->first();
            if ($user) {
                $userId = $user->id;
            }
        }
        
        \Log::info("FCM Token registration attempt", [
            'auth_user_id' => Auth::id(),
            'request_user_id' => $request->user_id,
            'request_user_email' => $request->user_email,
            'final_user_id' => $userId,
            'token_preview' => substr($request->token, 0, 20) . '...',
            'has_auth_header' => $request->hasHeader('Authorization')
        ]);
        
        if (!$userId) {
            // For unauthenticated users, store token with user_id = null
            // This token will be linked when user logs in
            \Log::info("Storing FCM token for guest user: " . substr($request->token, 0, 20) . "...");
            
            try {
                FcmToken::updateOrCreate(
                    ['token' => $request->token],
                    [
                        'user_id' => null,
                        'platform' => $request->platform ?? 'android',
                        'is_active' => true
                    ]
                );
                
                return response()->json(['message' => 'FCM token stored for guest user'], 201);
            } catch (\Exception $e) {
                \Log::error("Error storing guest FCM token: " . $e->getMessage());
                return response()->json(['error' => 'Failed to store FCM token'], 500);
            }
        }

        try {
            // First, check if this token exists as a guest token (user_id = null)
            $guestToken = FcmToken::where('token', $request->token)
                                 ->where('user_id', null)
                                 ->first();
            
            if ($guestToken) {
                // Link the guest token to this authenticated user
                $guestToken->update([
                    'user_id' => $userId,
                    'platform' => $request->platform ?? 'android',
                    'is_active' => true
                ]);
                
                \Log::info("Guest FCM token linked to user {$userId}");
                return response()->json(['message' => 'FCM token linked to user successfully']);
            }

            // Check if user already has this specific token
            $existingToken = FcmToken::where('user_id', $userId)
                                   ->where('token', $request->token)
                                   ->first();

            if ($existingToken) {
                // Token already exists for this user, just update activity
                $existingToken->update([
                    'platform' => $request->platform ?? 'android',
                    'is_active' => true
                ]);
                
                \Log::info("FCM token already exists for user {$userId}, updated activity");
                return response()->json(['message' => 'FCM token already registered']);
            }

            // Check if this token is already used by another user
            $tokenUsedByOther = FcmToken::where('token', $request->token)
                                      ->where('user_id', '!=', $userId)
                                      ->first();

            if ($tokenUsedByOther) {
                // Same device, different user - create new entry instead of replacing
                \Log::info("Token already used by user {$tokenUsedByOther->user_id}, creating new entry for user {$userId}");
                
                FcmToken::create([
                    'user_id' => $userId,
                    'token' => $request->token,
                    'platform' => $request->platform ?? 'android',
                    'is_active' => true
                ]);
                
                return response()->json(['message' => 'FCM token registered for new user on shared device']);
            }

            // Create new token entry
            FcmToken::create([
                'user_id' => $userId,
                'token' => $request->token,
                'platform' => $request->platform ?? 'android',
                'is_active' => true
            ]);
            
            \Log::info("FCM token registered for user {$userId}");
            return response()->json(['message' => 'FCM token registered successfully'], 201);
            
        } catch (\Exception $e) {
            \Log::error("FCM token registration error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to register FCM token'], 500);
        }
    }

    /**
     * Link FCM tokens by email - useful for linking guest tokens to users
     */
    public function linkByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'sometimes|string'
        ]);

        try {
            $user = \App\Models\User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if ($request->token) {
                // Link specific token to user
                $fcmToken = FcmToken::where('token', $request->token)
                                  ->where('user_id', null)
                                  ->first();
                
                if ($fcmToken) {
                    $fcmToken->update(['user_id' => $user->id]);
                    \Log::info("FCM token linked to user via email: {$user->email}");
                    return response()->json(['message' => 'FCM token linked successfully']);
                } else {
                    return response()->json(['error' => 'Token not found or already linked'], 404);
                }
            } else {
                // Link all guest tokens to this user (useful for bulk linking)
                $guestTokens = FcmToken::where('user_id', null)->get();
                $linkedCount = 0;
                
                foreach ($guestTokens as $token) {
                    $token->update(['user_id' => $user->id]);
                    $linkedCount++;
                }
                
                \Log::info("Linked {$linkedCount} guest FCM tokens to user: {$user->email}");
                return response()->json(['message' => "Linked {$linkedCount} FCM tokens successfully"]);
            }
        } catch (\Exception $e) {
            \Log::error("FCM token linking error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to link FCM token'], 500);
        }
    }

    public function delete(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        try {
            FcmToken::where('user_id', $userId)->delete();
            \Log::info("FCM token deleted for user {$userId}");
            return response()->json(['message' => 'FCM token deleted successfully']);
        } catch (\Exception $e) {
            \Log::error("FCM token deletion error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to delete FCM token'], 500);
        }
    }
}

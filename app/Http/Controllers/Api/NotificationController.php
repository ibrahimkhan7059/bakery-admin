<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Register FCM token for a user
     */
    public function registerToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'platform' => 'sometimes|in:android,ios',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $fcmToken = FcmToken::registerToken(
                $request->user_id,
                $request->token,
                $request->platform ?? 'android'
            );

            return response()->json([
                'success' => true,
                'message' => 'Token registered successfully',
                'data' => $fcmToken
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register token',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete FCM token
     */
    public function deleteToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $deleted = FcmToken::where('token', $request->token)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Token deleted successfully',
                'deleted' => $deleted > 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete token',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send notification to specific user
     */
    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'type' => 'sometimes|string',
            'data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->firebaseService->sendToUser(
                $request->user_id,
                $request->title,
                $request->body,
                $request->data ?? []
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Notification sent successfully' : 'Failed to send notification'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send bulk notification to topic
     */
    public function sendBulkNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->firebaseService->sendToTopic(
                $request->topic,
                $request->title,
                $request->body,
                $request->data ?? []
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Bulk notification sent successfully' : 'Failed to send bulk notification'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk notification',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send order update notification
     */
    public function sendOrderUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'order_id' => 'required|string',
            'status' => 'required|string',
            'message' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->firebaseService->sendOrderUpdate(
                $request->user_id,
                $request->order_id,
                $request->status,
                $request->message ?? null
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Order update sent successfully' : 'Failed to send order update'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send order update',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send custom cake update notification
     */
    public function sendCustomCakeUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'custom_id' => 'required|string',
            'status' => 'required|string',
            'message' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->firebaseService->sendCustomCakeUpdate(
                $request->user_id,
                $request->custom_id,
                $request->status,
                $request->message ?? null
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Custom cake update sent successfully' : 'Failed to send custom cake update'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send custom cake update',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send bulk order update notification
     */
    public function sendBulkOrderUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'bulk_id' => 'required|string',
            'status' => 'required|string',
            'message' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->firebaseService->sendBulkOrderUpdate(
                $request->user_id,
                $request->bulk_id,
                $request->status,
                $request->message ?? null
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Bulk order update sent successfully' : 'Failed to send bulk order update'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk order update',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send promotional notification
     */
    public function sendPromotionalNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'image_url' => 'sometimes|url',
            'action_url' => 'sometimes|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->firebaseService->sendPromotionalNotification(
                $request->title,
                $request->body,
                $request->image_url ?? null,
                $request->action_url ?? null
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Promotional notification sent successfully' : 'Failed to send promotional notification'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send promotional notification',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get notification preferences for a user
     */
    public function getNotificationPreferences($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Default preferences if not set
        $defaultPreferences = [
            'orders' => true,
            'custom_cakes' => true,
            'bulk_orders' => true,
            'promotions' => true,
            'general' => true,
        ];

        $preferences = $user->notification_preferences ?? $defaultPreferences;

        return response()->json([
            'success' => true,
            'preferences' => $preferences
        ]);
    }

    /**
     * Update notification preferences for a user
     */
    public function updateNotificationPreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'preferences' => 'required|array',
            'preferences.orders' => 'boolean',
            'preferences.custom_cakes' => 'boolean',
            'preferences.bulk_orders' => 'boolean',
            'preferences.promotions' => 'boolean',
            'preferences.general' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::find($request->user_id);
            $user->notification_preferences = $request->preferences;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated successfully',
                'preferences' => $user->notification_preferences
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification preferences',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

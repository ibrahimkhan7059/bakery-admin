<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\FcmToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class FirebaseNotificationService
{
    private $projectId;
    private $fcmUrl;
    private $serviceAccountPath;

    public function __construct()
    {
        $this->projectId = 'bakehub-474807';
        $this->fcmUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $this->serviceAccountPath = storage_path('app/firebase/service-account.json');
    }

    /**
     * Send notification to a specific user
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        $user = User::find($userId);
        if (!$user) {
            Log::error("User not found: {$userId}");
            return false;
        }

        $tokens = FcmToken::where('user_id', $userId)->pluck('token')->toArray();
        
        if (empty($tokens)) {
            Log::error("No FCM tokens found for user: {$userId}");
            return false;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send notification to multiple tokens
     */
    public function sendToTokens($tokens, $title, $body, $data = [])
    {
        $successCount = 0;
        
        // V1 API requires individual requests for each token
        foreach ($tokens as $token) {
            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]),
                'priority' => 'high',
                'content_available' => true,
            ];

            if ($this->sendRequest($payload)) {
                $successCount++;
            }
        }
        
        Log::info("FCM batch notification result", [
            'total_tokens' => count($tokens),
            'successful' => $successCount
        ]);

        return $successCount > 0;
    }

    /**
     * Send notification to a topic
     */
    public function sendToTopic($topic, $title, $body, $data = [])
    {
        $payload = [
            'to' => "/topics/{$topic}",
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => array_merge($data, [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]),
            'priority' => 'high',
            'content_available' => true,
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Send data-only notification (for background processing)
     */
    public function sendDataNotification($tokens, $data)
    {
        $payload = [
            'registration_ids' => is_array($tokens) ? $tokens : [$tokens],
            'data' => $data,
            'priority' => 'high',
            'content_available' => true,
        ];

        return $this->sendRequest($payload);
    }

    /**
     * Get access token for Firebase V1 API
     */
    private function getAccessToken()
    {
        try {
            if (!file_exists($this->serviceAccountPath)) {
                throw new \Exception('Service account file not found');
            }

            $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);
            
            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                $serviceAccount
            );
            
            $token = $credentials->fetchAuthToken();
            return $token['access_token'];
            
        } catch (\Exception $e) {
            Log::error('Failed to get Firebase access token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send HTTP request to FCM V1 API
     */
    private function sendRequest($payload)
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return false;
            }

            // Convert payload to V1 format
            $v1Payload = $this->convertToV1Format($payload);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $v1Payload);

            $responseData = $response->json();
            
            if ($response->successful()) {
                Log::info('FCM V1 notification sent successfully', $responseData);
                return true;
            } else {
                Log::error('FCM V1 notification failed', [
                    'status' => $response->status(),
                    'response' => $responseData,
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('FCM V1 notification exception', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    /**
     * Convert legacy payload to V1 format
     */
    private function convertToV1Format($payload)
    {
        $message = [];

        // Handle token or topic
        if (isset($payload['to'])) {
            if (strpos($payload['to'], '/topics/') === 0) {
                $message['topic'] = str_replace('/topics/', '', $payload['to']);
            } else {
                $message['token'] = $payload['to'];
            }
        }

        // Handle notification - V1 API format
        if (isset($payload['notification'])) {
            $message['notification'] = [
                'title' => $payload['notification']['title'],
                'body' => $payload['notification']['body'],
                // Remove unsupported fields like 'sound' and 'badge' for V1 API
            ];
        }

        // Handle data
        if (isset($payload['data'])) {
            $message['data'] = array_map('strval', $payload['data']);
        }

        // Handle Android specific config
        $message['android'] = [
            'priority' => 'high',
            'notification' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'channel_id' => 'order_channel'
            ]
        ];

        return ['message' => $message];
    }

    /**
     * Handle invalid tokens by removing them from database
     */
    private function handleInvalidTokens($tokens, $results)
    {
        foreach ($results as $index => $result) {
            if (isset($result['error'])) {
                $error = $result['error'];
                if (in_array($error, ['NotRegistered', 'InvalidRegistration'])) {
                    $token = $tokens[$index] ?? null;
                    if ($token) {
                        FcmToken::where('token', $token)->delete();
                        Log::info("Removed invalid FCM token: {$token}");
                    }
                }
            }
        }
    }

    /**
     * Send order update notification
     */
    public function sendOrderUpdate($userId, $orderId, $status, $message = null)
    {
        $title = 'Order Update';
        $body = $message ?: "Your order #{$orderId} is now {$status}";
        
        $data = [
            'type' => 'order',
            'order_id' => (string)$orderId,
            'status' => $status,
            'action' => 'order_details',
        ];

        return $this->sendToUser($userId, $title, $body, $data);
    }

    /**
     * Send custom cake update notification
     */
    public function sendCustomCakeUpdate($userId, $customId, $status, $message = null)
    {
        $title = 'Custom Cake Update';
        $body = $message ?: "Your custom cake order #{$customId} is {$status}";
        
        $data = [
            'type' => 'custom',
            'custom_id' => (string)$customId,
            'status' => $status,
            'action' => 'custom_cake_details',
        ];

        return $this->sendToUser($userId, $title, $body, $data);
    }

    /**
     * Send bulk order update notification
     */
    public function sendBulkOrderUpdate($userId, $bulkId, $status, $message = null)
    {
        $title = 'Bulk Order Update';
        $body = $message ?: "Your bulk order #{$bulkId} is {$status}";
        
        $data = [
            'type' => 'bulk',
            'bulk_id' => (string)$bulkId,
            'status' => $status,
            'action' => 'bulk_order_details',
        ];

        return $this->sendToUser($userId, $title, $body, $data);
    }

    /**
     * Send promotional notification
     */
    public function sendPromotionalNotification($title, $body, $imageUrl = null, $actionUrl = null)
    {
        $data = [
            'type' => 'promo',
            'action' => 'promotion_details',
        ];

        if ($imageUrl) {
            $data['image_url'] = $imageUrl;
        }

        if ($actionUrl) {
            $data['action_url'] = $actionUrl;
        }

        return $this->sendToTopic('promotions', $title, $body, $data);
    }

    /**
     * Send welcome notification to new user
     */
    public function sendWelcomeNotification($userId)
    {
        $title = 'Welcome to BakeHub!';
        $body = 'Thank you for joining BakeHub. Start exploring our delicious cakes and pastries!';
        
        $data = [
            'type' => 'general',
            'action' => 'home',
        ];

        return $this->sendToUser($userId, $title, $body, $data);
    }

    /**
     * Send reminder notification
     */
    public function sendReminderNotification($userId, $title, $body, $data = [])
    {
        $defaultData = [
            'type' => 'reminder',
            'action' => 'home',
        ];

        return $this->sendToUser($userId, $title, $body, array_merge($defaultData, $data));
    }
}

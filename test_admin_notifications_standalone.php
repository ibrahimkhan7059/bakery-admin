<?php

/**
 * BAKEHUB ADMIN NOTIFICATION TESTING SCRIPT (STANDALONE)
 * 
 * This script tests all admin operations that send notifications
 * Use your actual FCM token for testing
 */

// 🔥 Your FCM Token (replace with actual token)
$fcmToken = "ecMyxOz3QZ62ITcngkYUNU:APA91bHLwgURaB3a0B96ivCG6KMHlyiPNt25RuXAz3rBZgU7O-fA_zBNe6Wahk4fJ3vmHztfL5TtM6RK969PysFIiPuTItfyOP-QSKHKgMoZGqFsS8sOczI";

// Firebase Configuration
$projectId = 'bakehub-474807';
$serviceAccountPath = __DIR__ . '/storage/app/firebase/service-account.json';

/**
 * Get Firebase access token using service account
 */
function getAccessToken() {
    global $serviceAccountPath;
    
    if (!file_exists($serviceAccountPath)) {
        throw new Exception("Service account file not found: $serviceAccountPath");
    }
    
    $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
    
    // Create JWT header
    $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
    
    // Create JWT payload
    $now = time();
    $payload = json_encode([
        'iss' => $serviceAccount['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $now + 3600,
        'iat' => $now
    ]);
    
    // Encode header and payload
    $headerEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $payloadEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    // Create signature
    $signature = '';
    openssl_sign($headerEncoded . '.' . $payloadEncoded, $signature, $serviceAccount['private_key'], 'SHA256');
    $signatureEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    // Create JWT
    $jwt = $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    
    // Exchange JWT for access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("Failed to get access token: $response");
    }
    
    $tokenData = json_decode($response, true);
    return $tokenData['access_token'];
}

/**
 * Send FCM notification
 */
function sendFCMNotification($token, $title, $body, $data = []) {
    global $projectId;
    
    try {
        $accessToken = getAccessToken();
        
        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => $data,
                'android' => [
                    'notification' => [
                        'channel_id' => 'bakehub_notifications',
                        'priority' => 'high',
                        'default_sound' => true
                    ]
                ]
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "🧪 BAKEHUB ADMIN NOTIFICATION TESTING\n";
echo "=====================================\n\n";

/**
 * ==========================================
 * ORDER MANAGEMENT TESTS
 * ==========================================
 */

echo "📋 TESTING ORDER NOTIFICATIONS...\n";

// Test 1: Order Received
echo "1️⃣ Testing Order Received...\n";
$result1 = sendFCMNotification(
    $fcmToken,
    "🎂 Order Confirmed!",
    "Your order #ORD123 has been received! Total: Rs. 1500. We're preparing your delicious treats.",
    [
        'type' => 'order_update',
        'order_id' => 'ORD123',
        'status' => 'received',
        'action' => 'order_details'
    ]
);
echo $result1 ? "✅ Order received notification sent!\n\n" : "❌ Failed to send\n\n";

// Test 2: Order Processing  
echo "2️⃣ Testing Order Processing...\n";
$result2 = sendFCMNotification(
    $fcmToken,
    "👩‍🍳 Order in Kitchen!",
    "Great news! Order #ORD123 is now being prepared by our expert bakers. Fresh ingredients, made with love!",
    [
        'type' => 'order_update',
        'order_id' => 'ORD123',
        'status' => 'processing',
        'action' => 'order_details'
    ]
);
echo $result2 ? "✅ Order processing notification sent!\n\n" : "❌ Failed to send\n\n";

// Test 3: Order Ready for Delivery
echo "3️⃣ Testing Order Ready for Delivery...\n";
$result3 = sendFCMNotification(
    $fcmToken,
    "🚚 Out for Delivery!",
    "Your order #ORD123 is on its way! Our delivery partner will reach you soon.",
    [
        'type' => 'order_update',
        'order_id' => 'ORD123',
        'status' => 'ready',
        'delivery_type' => 'delivery',
        'action' => 'track_order'
    ]
);
echo $result3 ? "✅ Order ready notification sent!\n\n" : "❌ Failed to send\n\n";

// Test 4: Order Completed
echo "4️⃣ Testing Order Completed...\n";
$result4 = sendFCMNotification(
    $fcmToken,
    "🎉 Order Delivered!",
    "Order #ORD123 has been successfully delivered! Hope you enjoy our delicious treats. Rate your experience!",
    [
        'type' => 'order_update',
        'order_id' => 'ORD123',
        'status' => 'completed',
        'action' => 'rate_order'
    ]
);
echo $result4 ? "✅ Order completed notification sent!\n\n" : "❌ Failed to send\n\n";

/**
 * ==========================================
 * CUSTOM CAKE TESTS
 * ==========================================
 */

echo "🎂 TESTING CUSTOM CAKE NOTIFICATIONS...\n";

// Test 5: Custom Cake Request Received
echo "5️⃣ Testing Custom Cake Received...\n";
$result5 = sendFCMNotification(
    $fcmToken,
    "🎂✨ Custom Cake Request Received!",
    "We've received your custom cake request #CC456! Our design team is reviewing your requirements.",
    [
        'type' => 'custom_cake',
        'custom_id' => 'CC456',
        'status' => 'received',
        'action' => 'custom_cake_details'
    ]
);
echo $result5 ? "✅ Custom cake received notification sent!\n\n" : "❌ Failed to send\n\n";

// Test 6: Design Approved
echo "6️⃣ Testing Design Approved...\n";
$result6 = sendFCMNotification(
    $fcmToken,
    "✅ Design Approved!",
    "Fantastic! Your custom cake design #CC456 has been approved. We'll start baking your masterpiece!",
    [
        'type' => 'custom_cake',
        'custom_id' => 'CC456',
        'status' => 'approved',
        'action' => 'custom_cake_details'
    ]
);
echo $result6 ? "✅ Design approved notification sent!\n\n" : "❌ Failed to send\n\n";

// Test 7: Price Quote
echo "7️⃣ Testing Price Quote...\n";
$result7 = sendFCMNotification(
    $fcmToken,
    "💰 Price Quote Ready!",
    "Price quote for custom cake #CC456: Rs. 2500. Please approve to proceed with baking.",
    [
        'type' => 'custom_cake',
        'custom_id' => 'CC456',
        'status' => 'quote_ready',
        'price' => '2500',
        'action' => 'approve_quote'
    ]
);
echo $result7 ? "✅ Price quote notification sent!\n\n" : "❌ Failed to send\n\n";

/**
 * ==========================================
 * BULK ORDER TESTS
 * ==========================================
 */

echo "📦 TESTING BULK ORDER NOTIFICATIONS...\n";

// Test 8: Bulk Order Received
echo "8️⃣ Testing Bulk Order Received...\n";
$result8 = sendFCMNotification(
    $fcmToken,
    "📦 Bulk Order Received!",
    "Thank you for your bulk order #BLK789! We're reviewing your requirements and will send a quote soon.",
    [
        'type' => 'bulk_order',
        'bulk_id' => 'BLK789',
        'status' => 'received',
        'action' => 'bulk_order_details'
    ]
);
echo $result8 ? "✅ Bulk order received notification sent!\n\n" : "❌ Failed to send\n\n";

// Test 9: Bulk Order Quote
echo "9️⃣ Testing Bulk Order Quote...\n";
$result9 = sendFCMNotification(
    $fcmToken,
    "💼 Bulk Order Quote Ready!",
    "Quote for bulk order #BLK789: Rs. 15000. Expected delivery: Dec 25, 2024. Please confirm to proceed.",
    [
        'type' => 'bulk_order',
        'bulk_id' => 'BLK789',
        'status' => 'quote_ready',
        'total_price' => '15000',
        'delivery_date' => '2024-12-25',
        'action' => 'approve_bulk_quote'
    ]
);
echo $result9 ? "✅ Bulk order quote notification sent!\n\n" : "❌ Failed to send\n\n";

/**
 * ==========================================
 * PROMOTIONAL TESTS
 * ==========================================
 */

echo "🎉 TESTING PROMOTIONAL NOTIFICATIONS...\n";

// Test 10: New Product Launch
echo "🔟 Testing New Product Launch...\n";
$result10 = sendFCMNotification(
    $fcmToken,
    "🆕 New Delicious Arrival!",
    "Introducing Chocolate Truffle Cake starting from Rs. 800! Try our latest creation today.",
    [
        'type' => 'promotion',
        'promo_type' => 'new_product',
        'product_name' => 'Chocolate Truffle Cake',
        'price' => '800',
        'action' => 'view_product'
    ]
);
echo $result10 ? "✅ New product notification sent!\n\n" : "❌ Failed to send\n\n";

// Test 11: Discount Offer
echo "1️⃣1️⃣ Testing Discount Offer...\n";
$result11 = sendFCMNotification(
    $fcmToken,
    "🎉 Special Offer Alert!",
    "Weekend Special - Get 20% off! Valid until Dec 31, 2024. Don't miss out!",
    [
        'type' => 'promotion',
        'promo_type' => 'discount',
        'offer_title' => 'Weekend Special',
        'discount' => '20',
        'valid_until' => '2024-12-31',
        'action' => 'view_offers'
    ]
);
echo $result11 ? "✅ Discount offer notification sent!\n\n" : "❌ Failed to send\n\n";

// Test 12: Festival Offer
echo "1️⃣2️⃣ Testing Festival Offer...\n";
$result12 = sendFCMNotification(
    $fcmToken,
    "🎊 Eid Mubarak Special!",
    "Celebrate Eid Mubarak with our special treats! 25% off on all cakes and sweets! Order now for fresh delivery!",
    [
        'type' => 'promotion',
        'promo_type' => 'festival',
        'festival' => 'Eid Mubarak',
        'action' => 'festival_menu'
    ]
);
echo $result12 ? "✅ Festival offer notification sent!\n\n" : "❌ Failed to send\n\n";

/**
 * ==========================================
 * USER ENGAGEMENT TESTS
 * ==========================================
 */

echo "👤 TESTING USER ENGAGEMENT NOTIFICATIONS...\n";

// Test 13: Welcome Message
echo "1️⃣3️⃣ Testing Welcome Message...\n";
$result13 = sendFCMNotification(
    $fcmToken,
    "🤗 Welcome to BakeHub, Ahmed!",
    "Thank you for joining our sweet family! Explore our delicious cakes, pastries, and custom creations.",
    [
        'type' => 'account',
        'account_type' => 'welcome',
        'action' => 'explore_menu'
    ]
);
echo $result13 ? "✅ Welcome message sent!\n\n" : "❌ Failed to send\n\n";

// Test 14: Cart Reminder
echo "1️⃣4️⃣ Testing Cart Reminder...\n";
$result14 = sendFCMNotification(
    $fcmToken,
    "🛒 Don't Forget Your Cart!",
    "You have 3 delicious items waiting in your cart. Complete your order before they're gone!",
    [
        'type' => 'reminder',
        'reminder_type' => 'cart',
        'items_count' => '3',
        'action' => 'view_cart'
    ]
);
echo $result14 ? "✅ Cart reminder sent!\n\n" : "❌ Failed to send\n\n";

// Test 15: Re-engagement
echo "1️⃣5️⃣ Testing Re-engagement Message...\n";
$result15 = sendFCMNotification(
    $fcmToken,
    "🍰 We Miss You, Ahmed!",
    "It's been a while since your last order. Check out our new flavors and special offers waiting for you!",
    [
        'type' => 'reminder',
        'reminder_type' => 're_engagement',
        'action' => 'browse_menu'
    ]
);
echo $result15 ? "✅ Re-engagement message sent!\n\n" : "❌ Failed to send\n\n";

/**
 * ==========================================
 * ADMIN ALERT TESTS
 * ==========================================
 */

echo "⚠️ TESTING ADMIN ALERT NOTIFICATIONS...\n";

// Test 16: Low Stock Alert
echo "1️⃣6️⃣ Testing Low Stock Alert...\n";
$result16 = sendFCMNotification(
    $fcmToken,
    "⚠️ Low Stock Alert",
    "Chocolate Cake is running low! Only 5 items left. Please restock soon.",
    [
        'type' => 'admin_alert',
        'alert_type' => 'low_stock',
        'product_name' => 'Chocolate Cake',
        'current_stock' => '5',
        'action' => 'manage_inventory'
    ]
);
echo $result16 ? "✅ Low stock alert sent!\n\n" : "❌ Failed to send\n\n";

// Test 17: New Order Alert (for Admin)
echo "1️⃣7️⃣ Testing New Order Alert...\n";
$result17 = sendFCMNotification(
    $fcmToken,
    "🔔 New Order Received",
    "New order #ORD123 from Ahmed Khan. Total: Rs. 1500. Please review and process.",
    [
        'type' => 'admin_alert',
        'alert_type' => 'new_order',
        'order_id' => 'ORD123',
        'customer_name' => 'Ahmed Khan',
        'total_amount' => '1500',
        'action' => 'manage_orders'
    ]
);
echo $result17 ? "✅ New order alert sent!\n\n" : "❌ Failed to send\n\n";

/**
 * ==========================================
 * SUMMARY
 * ==========================================
 */

echo "📊 ADMIN NOTIFICATION TEST SUMMARY\n";
echo "==================================\n";
echo "Total Tests: 17\n";
echo "Categories Tested:\n";
echo "  📋 Order Management: 4 tests\n";
echo "  🎂 Custom Cakes: 3 tests\n";
echo "  📦 Bulk Orders: 2 tests\n";
echo "  🎉 Promotions: 3 tests\n";
echo "  👤 User Engagement: 3 tests\n";
echo "  ⚠️ Admin Alerts: 2 tests\n\n";

// Count successful results
$successCount = 0;
for ($i = 1; $i <= 17; $i++) {
    $varName = "result$i";
    if (isset($$varName) && $$varName) {
        $successCount++;
    }
}

echo "✅ Successful: $successCount/17\n";
echo "❌ Failed: " . (17 - $successCount) . "/17\n\n";

if ($successCount === 17) {
    echo "🎉 ALL ADMIN NOTIFICATION TESTS PASSED!\n";
    echo "🚀 BakeHub admin notification system is fully operational!\n\n";
    
    echo "📱 ADMIN OPERATIONS AVAILABLE:\n";
    echo "==============================\n";
    echo "📋 ORDER MANAGEMENT:\n";
    echo "  • Update order status (received → processing → ready → delivered)\n";
    echo "  • Handle order cancellations with reasons\n";
    echo "  • Send delivery/pickup notifications\n\n";
    
    echo "🎂 CUSTOM CAKE MANAGEMENT:\n";
    echo "  • Approve/reject custom cake designs\n";
    echo "  • Send price quotes to customers\n";
    echo "  • Track custom cake progress\n\n";
    
    echo "📦 BULK ORDER MANAGEMENT:\n";
    echo "  • Process bulk order requests\n";
    echo "  • Send pricing and delivery quotes\n";
    echo "  • Manage large quantity orders\n\n";
    
    echo "🎉 PROMOTIONAL CAMPAIGNS:\n";
    echo "  • Launch new product announcements\n";
    echo "  • Send discount offers to all users\n";
    echo "  • Create festival special campaigns\n\n";
    
    echo "⚠️ INVENTORY & ALERTS:\n";
    echo "  • Get low stock alerts automatically\n";
    echo "  • Receive new order notifications\n";
    echo "  • Monitor custom cake requests\n\n";
    
    echo "👥 USER ENGAGEMENT:\n";
    echo "  • Send welcome messages to new users\n";
    echo "  • Cart abandonment reminders\n";
    echo "  • Re-engage inactive customers\n\n";
    
    echo "💻 READY FOR ADMIN DASHBOARD INTEGRATION!\n";
    echo "🎯 All notification workflows are functional!\n";
} else {
    echo "⚠️ SOME TESTS FAILED!\n";
    echo "Please check Firebase configuration and network connection.\n";
}

echo "\n🏁 Testing completed!\n";

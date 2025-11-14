<?php

namespace App\Services;

use App\Services\FirebaseNotificationService;
use App\Models\User;
use App\Models\Order;
use App\Models\CustomCakeOrder;
use App\Models\BulkOrder;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class NotificationMessageService
{
    private $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * ==========================================
     * ORDER MANAGEMENT NOTIFICATIONS
     * ==========================================
     */

    // Order Received - When customer places an order
    public function sendOrderReceived($userId, $orderId, $totalAmount)
    {
        $title = '🎂 Order Confirmed!';
        $body = "Your order #$orderId has been received! Total: Rs. $totalAmount. We're preparing your delicious treats.";
        
        $data = [
            'type' => 'order_update',
            'order_id' => (string)$orderId,
            'status' => 'received',
            'action' => 'order_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Order Processing - When admin starts preparing order
    public function sendOrderProcessing($userId, $orderId)
    {
        $title = '👩‍🍳 Order in Kitchen!';
        $body = "Great news! Order #$orderId is now being prepared by our expert bakers. Fresh ingredients, made with love!";
        
        $data = [
            'type' => 'order_update',
            'order_id' => (string)$orderId,
            'status' => 'processing',
            'action' => 'order_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Order Ready for Pickup/Delivery
    public function sendOrderReady($userId, $orderId, $deliveryType)
    {
        if ($deliveryType === 'pickup') {
            $title = '✅ Order Ready for Pickup!';
            $body = "Your order #$orderId is ready! Please visit our bakery to collect your fresh baked goods.";
        } else {
            $title = '🚚 Out for Delivery!';
            $body = "Your order #$orderId is on its way! Our delivery partner will reach you soon.";
        }
        
        $data = [
            'type' => 'order_update',
            'order_id' => (string)$orderId,
            'status' => 'ready',
            'delivery_type' => $deliveryType,
            'action' => 'order_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Order Delivered/Completed
    public function sendOrderCompleted($userId, $orderId)
    {
        $title = '🎉 Order Delivered!';
        $body = "Order #$orderId has been successfully delivered! Hope you enjoy our delicious treats. Rate your experience!";
        
        $data = [
            'type' => 'order_update',
            'order_id' => (string)$orderId,
            'status' => 'completed',
            'action' => 'rate_order'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Order Cancelled
    public function sendOrderCancelled($userId, $orderId, $reason)
    {
        $title = '❌ Order Cancelled';
        $body = "We're sorry! Order #$orderId has been cancelled. Reason: $reason. Refund will be processed within 24-48 hours.";
        
        $data = [
            'type' => 'order_update',
            'order_id' => (string)$orderId,
            'status' => 'cancelled',
            'reason' => $reason,
            'action' => 'order_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    /**
     * ==========================================
     * CUSTOM CAKE NOTIFICATIONS
     * ==========================================
     */

    // Custom Cake Request Received
    public function sendCustomCakeReceived($userId, $customId)
    {
        $title = '🎂✨ Custom Cake Request Received!';
        $body = "We've received your custom cake request #$customId! Our design team is reviewing your requirements.";
        
        $data = [
            'type' => 'custom_cake',
            'custom_id' => (string)$customId,
            'status' => 'received',
            'action' => 'custom_cake_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Custom Cake Design Approved/Rejected
    public function sendCustomCakeDesignUpdate($userId, $customId, $status, $message = null)
    {
        if ($status === 'approved') {
            $title = '✅ Design Approved!';
            $body = "Fantastic! Your custom cake design #$customId has been approved. " . ($message ?: 'We\'ll start baking your masterpiece!');
        } else {
            $title = '📝 Design Revision Needed';
            $body = "Custom cake #$customId needs some adjustments. " . ($message ?: 'Please check the feedback and resubmit.');
        }
        
        $data = [
            'type' => 'custom_cake',
            'custom_id' => (string)$customId,
            'status' => $status,
            'action' => 'custom_cake_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Custom Cake Price Quote
    public function sendCustomCakeQuote($userId, $customId, $price)
    {
        $title = '💰 Price Quote Ready!';
        $body = "Price quote for custom cake #$customId: Rs. $price. Please approve to proceed with baking.";
        
        $data = [
            'type' => 'custom_cake',
            'custom_id' => (string)$customId,
            'status' => 'quote_ready',
            'price' => (string)$price,
            'action' => 'approve_quote'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Custom Cake In Progress
    public function sendCustomCakeInProgress($userId, $customId)
    {
        $title = '👨‍🍳 Baking Started!';
        $body = "Great news! We've started working on your custom cake #$customId. Our master bakers are creating your masterpiece!";
        
        $data = [
            'type' => 'custom_cake',
            'custom_id' => (string)$customId,
            'status' => 'in_progress',
            'action' => 'custom_cake_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Custom Cake Completed
    public function sendCustomCakeCompleted($userId, $customId)
    {
        $title = '🎉 Custom Cake Ready!';
        $body = "Fantastic! Your custom cake #$customId is completed and ready for pickup. Thank you for choosing BakeHub!";
        
        $data = [
            'type' => 'custom_cake',
            'custom_id' => (string)$customId,
            'status' => 'completed',
            'action' => 'custom_cake_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Custom Cake Cancelled
    public function sendCustomCakeCancelled($userId, $customId, $reason = null)
    {
        $title = '❌ Order Cancelled';
        $body = "Your custom cake order #$customId has been cancelled. " . ($reason ?: 'Contact us for more details.');
        
        $data = [
            'type' => 'custom_cake',
            'custom_id' => (string)$customId,
            'status' => 'cancelled',
            'action' => 'custom_cake_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Custom Cake Confirmed
    public function sendCustomCakeConfirmed($userId, $customId)
    {
        $title = '✅ Order Confirmed!';
        $body = "Your custom cake order #$customId has been confirmed! We'll review your design and get back to you soon.";
        
        $data = [
            'type' => 'custom_cake',
            'custom_id' => (string)$customId,
            'status' => 'confirmed',
            'action' => 'custom_cake_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    /**
     * ==========================================
     * BULK ORDER NOTIFICATIONS
     * ==========================================
     */

    // Bulk Order Request Received
    public function sendBulkOrderReceived($userId, $bulkId)
    {
        $title = '📦 Bulk Order Received!';
        $body = "Thank you for your bulk order #$bulkId! We're reviewing your requirements and will send a quote soon.";
        
        $data = [
            'type' => 'bulk_order',
            'bulk_id' => (string)$bulkId,
            'status' => 'received',
            'action' => 'bulk_order_details'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Bulk Order Quote
    public function sendBulkOrderQuote($userId, $bulkId, $totalPrice, $deliveryDate)
    {
        $title = '💼 Bulk Order Quote Ready!';
        $body = "Quote for bulk order #$bulkId: Rs. $totalPrice. Expected delivery: $deliveryDate. Please confirm to proceed.";
        
        $data = [
            'type' => 'bulk_order',
            'bulk_id' => (string)$bulkId,
            'status' => 'quote_ready',
            'total_price' => (string)$totalPrice,
            'delivery_date' => $deliveryDate,
            'action' => 'approve_bulk_quote'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Bulk Order Update (Generic)
    public function sendBulkOrderUpdate($userId, $bulkId, $status, $message = null)
    {
        $title = 'Bulk Order Update';
        $body = $message ?: "Your bulk order #{$bulkId} status has been updated to {$status}";
        
        $data = [
            'type' => 'bulk_order',
            'bulk_id' => (string)$bulkId,
            'status' => $status,
            'action' => 'bulk_order_details',
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    /**
     * ==========================================
     * PROMOTIONAL NOTIFICATIONS
     * ==========================================
     */

    // New Product Launch
    public function sendNewProductLaunch($productName, $price, $imageUrl = null)
    {
        $title = '🆕 New Delicious Arrival!';
        $body = "Introducing $productName starting from Rs. $price! Try our latest creation today.";
        
        $data = [
            'type' => 'promotion',
            'promo_type' => 'new_product',
            'product_name' => $productName,
            'price' => (string)$price,
            'action' => 'view_product'
        ];

        if ($imageUrl) {
            $data['image_url'] = $imageUrl;
        }

        return $this->firebaseService->sendToTopic('promotions', $title, $body, $data);
    }

    // Discount/Offer Notifications
    public function sendDiscountOffer($offerTitle, $discountPercent, $validUntil)
    {
        $title = '🎉 Special Offer Alert!';
        $body = "$offerTitle - Get $discountPercent% off! Valid until $validUntil. Don't miss out!";
        
        $data = [
            'type' => 'promotion',
            'promo_type' => 'discount',
            'offer_title' => $offerTitle,
            'discount' => (string)$discountPercent,
            'valid_until' => $validUntil,
            'action' => 'view_offers'
        ];

        return $this->firebaseService->sendToTopic('promotions', $title, $body, $data);
    }

    // Festival/Seasonal Offers
    public function sendFestivalOffer($festival, $offerDetails)
    {
        $title = "🎊 $festival Special!";
        $body = "Celebrate $festival with our special treats! $offerDetails Order now for fresh delivery!";
        
        $data = [
            'type' => 'promotion',
            'promo_type' => 'festival',
            'festival' => $festival,
            'action' => 'festival_menu'
        ];

        return $this->firebaseService->sendToTopic('promotions', $title, $body, $data);
    }

    /**
     * ==========================================
     * USER ACCOUNT NOTIFICATIONS
     * ==========================================
     */

    // Welcome New User
    public function sendWelcomeMessage($userId, $userName)
    {
        $title = "🤗 Welcome to BakeHub, $userName!";
        $body = "Thank you for joining our sweet family! Explore our delicious cakes, pastries, and custom creations.";
        
        $data = [
            'type' => 'account',
            'account_type' => 'welcome',
            'action' => 'explore_menu'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Password Changed
    public function sendPasswordChanged($userId)
    {
        $title = '🔐 Password Updated';
        $body = "Your BakeHub account password has been successfully updated. If this wasn't you, please contact support.";
        
        $data = [
            'type' => 'account',
            'account_type' => 'security',
            'action' => 'account_security'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Profile Updated
    public function sendProfileUpdated($userId)
    {
        $title = '✅ Profile Updated';
        $body = "Your profile information has been successfully updated. Thank you for keeping your details current!";
        
        $data = [
            'type' => 'account',
            'account_type' => 'profile',
            'action' => 'view_profile'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    /**
     * ==========================================
     * REMINDER NOTIFICATIONS
     * ==========================================
     */

    // Cart Abandonment Reminder
    public function sendCartReminder($userId, $itemsCount)
    {
        $title = '🛒 Don\'t Forget Your Cart!';
        $body = "You have $itemsCount delicious items waiting in your cart. Complete your order before they're gone!";
        
        $data = [
            'type' => 'reminder',
            'reminder_type' => 'cart',
            'items_count' => (string)$itemsCount,
            'action' => 'view_cart'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    // Re-engagement Notification
    public function sendReEngagementMessage($userId, $userName)
    {
        $title = "🍰 We Miss You, $userName!";
        $body = "It's been a while since your last order. Check out our new flavors and special offers waiting for you!";
        
        $data = [
            'type' => 'reminder',
            'reminder_type' => 're_engagement',
            'action' => 'browse_menu'
        ];

        return $this->firebaseService->sendToUser($userId, $title, $body, $data);
    }

    /**
     * ==========================================
     * ADMIN ALERT NOTIFICATIONS
     * ==========================================
     */

    // Low Stock Alert (For Admin)
    public function sendLowStockAlert($adminUserId, $productName, $currentStock)
    {
        $title = '⚠️ Low Stock Alert';
        $body = "$productName is running low! Only $currentStock items left. Please restock soon.";
        
        $data = [
            'type' => 'admin_alert',
            'alert_type' => 'low_stock',
            'product_name' => $productName,
            'current_stock' => (string)$currentStock,
            'action' => 'manage_inventory'
        ];

        return $this->firebaseService->sendToUser($adminUserId, $title, $body, $data);
    }

    // New Order Alert (For Admin)
    public function sendNewOrderAlert($adminUserId, $orderId, $customerName, $totalAmount)
    {
        $title = '🔔 New Order Received';
        $body = "New order #$orderId from $customerName. Total: Rs. $totalAmount. Please review and process.";
        
        $data = [
            'type' => 'admin_alert',
            'alert_type' => 'new_order',
            'order_id' => (string)$orderId,
            'customer_name' => $customerName,
            'total_amount' => (string)$totalAmount,
            'action' => 'manage_orders'
        ];

        return $this->firebaseService->sendToUser($adminUserId, $title, $body, $data);
    }

    // Custom Cake Request Alert (For Admin)
    public function sendCustomCakeAlert($adminUserId, $customId, $customerName)
    {
        $title = '🎨 New Custom Cake Request';
        $body = "New custom cake request #$customId from $customerName. Please review the design requirements.";
        
        $data = [
            'type' => 'admin_alert',
            'alert_type' => 'custom_cake_request',
            'custom_id' => (string)$customId,
            'customer_name' => $customerName,
            'action' => 'manage_custom_cakes'
        ];

        return $this->firebaseService->sendToUser($adminUserId, $title, $body, $data);
    }
}

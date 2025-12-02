<?php

namespace App\Http\Controllers;

use App\Services\PayFastService;
use App\Models\Order;
use App\Models\BulkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $payFastService;

    public function __construct(PayFastService $payFastService)
    {
        $this->payFastService = $payFastService;
    }

    public function initiatePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_mobile' => 'required|string',
            'order_id' => 'required|string',
            'description' => 'required|string'
        ]);

        // Generate basket ID with proper prefix (ORDER or BULK)
        $basketId = $request->order_id . '-' . time();
        
        Log::info('Initiating payment', [
            'order_id' => $request->order_id,
            'basket_id' => $basketId,
            'amount' => $request->amount
        ]);
        
        // Get Access Token
        $tokenResponse = $this->payFastService->getAccessToken($basketId, $request->amount);
        
        if (!$tokenResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => $tokenResponse['message']
            ], 400);
        }

        // Generate Payment Form Data
        $paymentData = [
            'basket_id' => $basketId,
            'amount' => $request->amount,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_mobile' => $request->customer_mobile,
            'description' => $request->description
        ];

        $formData = $this->payFastService->generatePaymentForm(
            $tokenResponse['access_token'], 
            $paymentData
        );

        // Store pending payment in database - check if it's a bulk order or regular order
        try {
            // Check if order_id has BULK- prefix
            if (preg_match('/^BULK-(\d+)$/', $request->order_id, $matches)) {
                $orderId = $matches[1];
                $order = BulkOrder::find($orderId);
                if ($order) {
                    $order->update([
                        'basket_id' => $basketId,
                        'payment_status' => 'pending'
                    ]);
                    Log::info('Basket ID saved to bulk order', [
                        'bulk_order_id' => $orderId,
                        'basket_id' => $basketId
                    ]);
                } else {
                    Log::warning('Bulk order not found when trying to save basket_id', [
                        'bulk_order_id' => $orderId,
                        'basket_id' => $basketId
                    ]);
                }
            } else {
                // Regular order (extract order_id if it has ORDER- prefix)
                $orderId = preg_match('/^ORDER-(\d+)$/', $request->order_id, $matches) ? $matches[1] : $request->order_id;
                $order = Order::find($orderId);
                if ($order) {
                    $order->update([
                        'basket_id' => $basketId,
                        'payment_status_payfast' => 'pending',
                        'payment_method_type' => 'payfast'
                    ]);
                    Log::info('Basket ID saved to order', [
                        'order_id' => $orderId,
                        'basket_id' => $basketId
                    ]);
                } else {
                    Log::warning('Order not found when trying to save basket_id', [
                        'order_id' => $orderId,
                        'basket_id' => $basketId
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Order update failed in initiatePayment: ' . $e->getMessage(), [
                'order_id' => $request->order_id,
                'basket_id' => $basketId,
                'error' => $e->getTraceAsString()
            ]);
        }

        return response()->json([
            'success' => true,
            'payment_url' => env('PAYFAST_BASE_URL') . 'Ecommerce/api/Transaction/PostTransaction',
            'form_data' => $formData,
            'basket_id' => $basketId
        ]);
    }

    public function paymentSuccess(Request $request)
    {
        Log::info('Payment Success Callback - Full Request', [
            'all' => $request->all(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'query' => $request->query->all(),
        ]);

        $basketId = $request->basket_id ?? $request->input('basket_id') ?? $request->query('basket_id');
        $errorCode = $request->err_code ?? $request->input('err_code') ?? $request->query('err_code');
        $validationHash = $request->validation_hash ?? $request->input('validation_hash') ?? $request->query('validation_hash');
        $transactionId = $request->transaction_id ?? $request->input('transaction_id') ?? $request->query('transaction_id');

        Log::info('Payment Success - Extracted Parameters', [
            'basket_id' => $basketId,
            'error_code' => $errorCode,
            'has_validation_hash' => !empty($validationHash),
            'transaction_id' => $transactionId
        ]);

        if (!$basketId) {
            Log::error('Payment Success Callback: Missing basket_id', $request->all());
            return response()->json([
                'success' => false,
                'message' => 'Basket ID is required'
            ], 400);
        }

        // Determine if this is a bulk order or regular order based on basket_id prefix
        $isBulkOrder = false;
        $order = null;
        
        // Check if basket_id has BULK- prefix
        if (preg_match('/^BULK-(\d+)-/', $basketId, $matches)) {
            $isBulkOrder = true;
            $orderId = $matches[1];
            Log::info('Processing bulk order payment', [
                'basket_id' => $basketId,
                'bulk_order_id' => $orderId
            ]);
            
            // Find bulk order by basket_id first
            $order = BulkOrder::where('basket_id', $basketId)->first();
            
            // If not found, try by order ID
            if (!$order) {
                Log::info('Bulk order not found by basket_id, trying order_id', [
                    'basket_id' => $basketId,
                    'extracted_order_id' => $orderId
                ]);
                $order = BulkOrder::find($orderId);
                
                // If found by order_id, update the basket_id
                if ($order) {
                    $order->basket_id = $basketId;
                    $order->save();
                    Log::info('Bulk order found by order_id, updated basket_id', [
                        'order_id' => $orderId,
                        'basket_id' => $basketId
                    ]);
                }
            }
        } else {
            // Regular order processing
            // Find order by basket_id (exact match first)
            $order = Order::where('basket_id', $basketId)->first();
            
            // If not found by exact basket_id, try to extract order_id from basket_id format: ORDER-{order_id}-{timestamp}
            if (!$order && preg_match('/ORDER-(\d+)-/', $basketId, $matches)) {
                $orderId = $matches[1];
                Log::info('Order not found by basket_id, trying order_id', [
                    'basket_id' => $basketId,
                    'extracted_order_id' => $orderId
                ]);
                $order = Order::where('id', $orderId)->first();
                
                // If found by order_id, update the basket_id for future reference
                if ($order) {
                    $order->basket_id = $basketId;
                    $order->save();
                    Log::info('Order found by order_id, updated basket_id', [
                        'order_id' => $orderId,
                        'basket_id' => $basketId
                    ]);
                }
            }
        }
        
        if (!$order) {
            // Get recent orders for debugging
            if ($isBulkOrder) {
                $recentOrders = BulkOrder::orderBy('id', 'desc')->limit(5)->get(['id', 'basket_id', 'payment_status']);
                Log::error('Payment Success: Bulk order not found', [
                    'basket_id' => $basketId,
                    'recent_bulk_orders' => $recentOrders->toArray()
                ]);
            } else {
                $recentOrders = Order::orderBy('id', 'desc')->limit(5)->get(['id', 'basket_id', 'payment_status_payfast']);
                Log::error('Payment Success: Order not found', [
                    'basket_id' => $basketId,
                    'recent_orders' => $recentOrders->toArray()
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Order not found with this basket ID'
            ], 404);
        }

        Log::info('Payment Success - Order Found', [
            'order_id' => $order->id,
            'order_type' => $isBulkOrder ? 'bulk' : 'regular',
            'current_payment_status' => $isBulkOrder ? $order->payment_status : $order->payment_status_payfast,
            'basket_id' => $basketId
        ]);

        // Check if payment already verified
        $alreadyPaid = $isBulkOrder 
            ? ($order->payment_status === 'paid')
            : ($order->payment_status_payfast === 'paid');
            
        if ($alreadyPaid) {
            Log::info('Payment already verified', ['basket_id' => $basketId, 'order_id' => $order->id]);
            return response()->json([
                'success' => true,
                'message' => 'Payment already verified',
                'transaction_id' => $order->transaction_id ?? $transactionId
            ]);
        }

        // Validate payment response if validation hash is provided
        $isValid = true;
        if ($errorCode && $validationHash) {
            $isValid = $this->payFastService->validatePaymentResponse($basketId, $errorCode, $validationHash);
        } else {
            Log::warning('Payment Success: Missing validation parameters, proceeding with verification', [
                'basket_id' => $basketId,
                'has_error_code' => !empty($errorCode),
                'has_validation_hash' => !empty($validationHash)
            ]);
        }

        if ($isValid) {
            // Update order status in database
            try {
                if ($isBulkOrder) {
                    // Update bulk order
                    $order->payment_status = 'paid';
                    $order->transaction_id = $transactionId;
                    $order->payment_date = now();
                    // Ensure basket_id is set
                    if (!$order->basket_id) {
                        $order->basket_id = $basketId;
                    }
                    $order->save();
                    
                    Log::info('Bulk order payment status updated', [
                        'basket_id' => $basketId,
                        'bulk_order_id' => $order->id,
                        'payment_status' => $order->payment_status,
                        'transaction_id' => $order->transaction_id
                    ]);
                } else {
                    // Update regular order - update both payment_status_payfast and payment_status
                    $order->payment_status_payfast = 'paid';
                    $order->payment_status = 'paid';
                    $order->transaction_id = $transactionId;
                    $order->payment_date = now();
                    // Ensure basket_id is set
                    if (!$order->basket_id) {
                        $order->basket_id = $basketId;
                    }
                    $order->save();
                    
                    Log::info('Order payment status updated', [
                        'basket_id' => $basketId,
                        'order_id' => $order->id,
                        'payment_status_payfast' => $order->payment_status_payfast,
                        'payment_status' => $order->payment_status,
                        'transaction_id' => $order->transaction_id
                    ]);
                }

                // Verify the update by refreshing from database
                $order->refresh();
                Log::info('Payment status verified after update', [
                    'basket_id' => $basketId,
                    'order_id' => $order->id,
                    'order_type' => $isBulkOrder ? 'bulk' : 'regular',
                    'payment_status' => $isBulkOrder ? $order->payment_status : $order->payment_status_payfast,
                    'transaction_id' => $order->transaction_id
                ]);
                
            } catch (\Exception $e) {
                Log::error('Order update failed', [
                    'basket_id' => $basketId,
                    'order_id' => $order->id ?? 'unknown',
                    'order_type' => $isBulkOrder ? 'bulk' : 'regular',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order status: ' . $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'transaction_id' => $transactionId,
                'order_id' => $order->id
            ]);
        }

        Log::warning('Payment validation failed', [
            'basket_id' => $basketId,
            'error_code' => $errorCode,
            'has_validation_hash' => !empty($validationHash)
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment validation failed'
        ], 400);
    }

    public function paymentFailure(Request $request)
    {
        Log::info('Payment Failure Callback', $request->all());

        $basketId = $request->basket_id ?? $request->input('basket_id') ?? $request->query('basket_id');
        
        $isBulkOrder = false;
        $order = null;
        
        // Check if it's a bulk order
        if (preg_match('/^BULK-(\d+)-/', $basketId, $matches)) {
            $isBulkOrder = true;
            $orderId = $matches[1];
            $order = BulkOrder::where('basket_id', $basketId)->first();
            if (!$order) {
                $order = BulkOrder::find($orderId);
            }
        } else {
            // Regular order
            $order = Order::where('basket_id', $basketId)->first();
            if (!$order && preg_match('/ORDER-(\d+)-/', $basketId, $matches)) {
                $orderId = $matches[1];
                $order = Order::where('id', $orderId)->first();
            }
        }
        
        // Update order status to failed/cancelled
        if ($order) {
            try {
                if ($isBulkOrder) {
                    $order->payment_status = 'failed';
                    $order->status = 'cancelled';
                    $order->payment_error = $request->err_msg ?? $request->input('err_msg') ?? 'Payment failed';
                    $order->save();
                    
                    Log::info('Bulk order cancelled due to payment failure', [
                        'bulk_order_id' => $order->id,
                        'basket_id' => $basketId,
                        'payment_status' => $order->payment_status
                    ]);
                } else {
                    $order->payment_status_payfast = 'failed';
                    $order->payment_status = 'failed';
                    $order->status = 'cancelled';
                    $order->payment_error = $request->err_msg ?? $request->input('err_msg') ?? 'Payment failed';
                    $order->save();
                    
                    Log::info('Order cancelled due to payment failure', [
                        'order_id' => $order->id,
                        'basket_id' => $basketId,
                        'payment_status' => $order->payment_status
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Order update failed in paymentFailure: ' . $e->getMessage());
            }
        } else {
            Log::warning('Order not found for payment failure', ['basket_id' => $basketId]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment failed',
            'error' => $request->err_msg ?? $request->input('err_msg') ?? 'Payment failed'
        ]);
    }

    public function checkPaymentStatus(Request $request)
    {
        $basketId = $request->basket_id;
        
        try {
            $isBulkOrder = false;
            $order = null;
            
            // Check if it's a bulk order
            if (preg_match('/^BULK-(\d+)-/', $basketId, $matches)) {
                $isBulkOrder = true;
                $order = BulkOrder::where('basket_id', $basketId)->first();
            } else {
                $order = Order::where('basket_id', $basketId)->first();
            }
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'payment_status' => $isBulkOrder ? $order->payment_status : $order->payment_status_payfast,
                'transaction_id' => $order->transaction_id,
                'order' => $order
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status'
            ], 500);
        }
    }
}

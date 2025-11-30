<?php

namespace App\Http\Controllers;

use App\Services\PayFastService;
use App\Models\Order;
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

        $basketId = 'ORDER-' . $request->order_id . '-' . time();
        
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

        // Store pending payment in database (optional)
        try {
            Order::where('id', $request->order_id)->update([
                'basket_id' => $basketId,
                'payment_status_payfast' => 'pending',
                'payment_method_type' => 'payfast'
            ]);
        } catch (\Exception $e) {
            Log::info('Order update failed: ' . $e->getMessage());
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

        // Check if payment is already verified
        // Try to find order by basket_id first
        $order = Order::where('basket_id', $basketId)->first();
        
        // If not found by basket_id, try to extract order_id from basket_id format: ORDER-{order_id}-{timestamp}
        if (!$order && preg_match('/ORDER-(\d+)-/', $basketId, $matches)) {
            $orderId = $matches[1];
            Log::info('Order not found by basket_id, trying order_id', [
                'basket_id' => $basketId,
                'extracted_order_id' => $orderId
            ]);
            $order = Order::where('id', $orderId)->first();
            
            // If found by order_id, update the basket_id for future reference
            if ($order) {
                $order->update(['basket_id' => $basketId]);
                Log::info('Order found by order_id, updated basket_id', [
                    'order_id' => $orderId,
                    'basket_id' => $basketId
                ]);
            }
        }
        
        if (!$order) {
            Log::error('Payment Success: Order not found', [
                'basket_id' => $basketId,
                'all_orders_with_basket' => Order::whereNotNull('basket_id')->pluck('basket_id', 'id')->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Order not found with this basket ID'
            ], 404);
        }

        Log::info('Payment Success - Order Found', [
            'order_id' => $order->id,
            'current_payment_status_payfast' => $order->payment_status_payfast,
            'current_payment_status' => $order->payment_status
        ]);

        if ($order && $order->payment_status_payfast === 'paid') {
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
            // Update order status in database - update both payment_status_payfast and payment_status
            try {
                $updateData = [
                    'payment_status_payfast' => 'paid',
                    'payment_status' => 'paid', // Also update the main payment_status field
                    'transaction_id' => $transactionId,
                    'payment_date' => now()
                ];

                $updated = Order::where('basket_id', $basketId)->update($updateData);
                
                Log::info('Payment status update attempt', [
                    'basket_id' => $basketId,
                    'order_id' => $order->id,
                    'rows_updated' => $updated,
                    'update_data' => $updateData
                ]);

                // Verify the update
                $updatedOrder = Order::where('basket_id', $basketId)->first();
                Log::info('Payment status after update', [
                    'basket_id' => $basketId,
                    'order_id' => $updatedOrder->id,
                    'payment_status_payfast' => $updatedOrder->payment_status_payfast,
                    'payment_status' => $updatedOrder->payment_status,
                    'transaction_id' => $updatedOrder->transaction_id
                ]);
                
            } catch (\Exception $e) {
                Log::error('Order update failed', [
                    'basket_id' => $basketId,
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

        $basketId = $request->basket_id;
        
        // Update order status to failed
        try {
            Order::where('basket_id', $basketId)->update([
                'payment_status_payfast' => 'failed',
                'payment_error' => $request->err_msg
            ]);
        } catch (\Exception $e) {
            Log::error('Order update failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment failed',
            'error' => $request->err_msg
        ]);
    }

    public function checkPaymentStatus(Request $request)
    {
        $basketId = $request->basket_id;
        
        try {
            $order = Order::where('basket_id', $basketId)->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'payment_status' => $order->payment_status_payfast,
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

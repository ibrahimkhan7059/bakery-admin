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
        Log::info('Payment Success Callback', $request->all());

        $basketId = $request->basket_id;
        $errorCode = $request->err_code;
        $validationHash = $request->validation_hash;
        $transactionId = $request->transaction_id;

        // Validate payment response
        if ($this->payFastService->validatePaymentResponse($basketId, $errorCode, $validationHash)) {
            // Update order status in database
            try {
                Order::where('basket_id', $basketId)->update([
                    'payment_status_payfast' => 'paid',
                    'transaction_id' => $transactionId,
                    'payment_date' => now()
                ]);
            } catch (\Exception $e) {
                Log::error('Order update failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'transaction_id' => $transactionId
            ]);
        }

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

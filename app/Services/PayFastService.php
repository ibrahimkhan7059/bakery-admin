<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayFastService
{
    private $merchantId;
    private $securedKey;
    private $baseUrl;

    public function __construct()
    {
        $this->merchantId = env('PAYFAST_MERCHANT_ID');
        $this->securedKey = env('PAYFAST_SECURED_KEY');
        $this->baseUrl = env('PAYFAST_BASE_URL');
    }

    public function getAccessToken($basketId, $amount)
    {
        try {
            $response = Http::asForm()->post($this->baseUrl . 'Ecommerce/api/Transaction/GetAccessToken', [
                'MERCHANT_ID' => $this->merchantId,
                'SECURED_KEY' => $this->securedKey,
                'BASKET_ID' => $basketId,
                'TXNAMT' => $amount,
                'CURRENCY_CODE' => 'PKR'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'access_token' => $data['ACCESS_TOKEN'] ?? null,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];

        } catch (\Exception $e) {
            Log::error('PayFast Access Token Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function generatePaymentForm($accessToken, $paymentData)
    {
        return [
            'MERCHANT_ID' => $this->merchantId,
            'MERCHANT_NAME' => env('PAYFAST_MERCHANT_NAME'),
            'TOKEN' => $accessToken,
            'PROCCODE' => '00',
            'TXNAMT' => $paymentData['amount'],
            'CUSTOMER_MOBILE_NO' => $paymentData['customer_mobile'],
            'CUSTOMER_EMAIL_ADDRESS' => $paymentData['customer_email'],
            'SIGNATURE' => $this->generateSignature(),
            'VERSION' => 'LARAVEL-API-1.0',
            'TXNDESC' => $paymentData['description'],
            'SUCCESS_URL' => env('PAYFAST_SUCCESS_URL'),
            'FAILURE_URL' => env('PAYFAST_FAILURE_URL'),
            'BASKET_ID' => $paymentData['basket_id'],
            'ORDER_DATE' => date('Y-m-d'),
            'CHECKOUT_URL' => env('PAYFAST_CHECKOUT_URL'),
            'CURRENCY_CODE' => 'PKR',
            'CUSTOMER_NAME' => $paymentData['customer_name'],
            'TRAN_TYPE' => 'ECOMM_PURCHASE'
        ];
    }

    public function validatePaymentResponse($basketId, $errorCode, $validationHash)
    {
        $stringToHash = $basketId . '|' . $this->securedKey . '|' . $this->merchantId . '|' . $errorCode;
        $calculatedHash = hash('sha256', $stringToHash);
        return $calculatedHash === $validationHash;
    }

    private function generateSignature()
    {
        return uniqid();
    }
}

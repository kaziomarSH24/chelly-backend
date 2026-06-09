<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FiservController extends Controller
{
    /**
     * Process a test payment charge request to Fiserv (Commerce Hub)
     */
    public function processPayment(Request $request)
    {
        // Using the exact credentials you provided
        $apiKey = 'PpJqTNxoBeXskIrAKav5tqvidB7tR46pSRBu7q3YSBWYQgyE';
        $apiSecret = '7b6JRqc5xUYNrvhAwDWLMlgE3bBQPn2rNQxz65GvbHEApiHd0eDYWn0Qpa0FFiP2';

        // Commerce Hub Host URL for Charges
        $endpoint = 'https://connect-cert.fiservapis.com/ch/payments/v1/charges';

        // Generate required header parameters
        $clientRequestId = Str::uuid()->toString();
        $timestamp = (string) round(microtime(true) * 1000);

        // PAYLOAD FOR CHARGE / PAYMENT
        $payloadObj = [
            "amount" => [
                "total" => 12.04,
                "currency" => "USD"
            ],
            "source" => [
                "sourceType" => "PaymentCard",
                "card" => [
                    "cardData" => "4111111111111111", // Standard Sandbox Visa Card
                    "expirationMonth" => "12",
                    "expirationYear" => "2026",
                    "securityCode" => "123"
                ]
            ],
            "transactionDetails" => [
                "captureFlag" => true
            ],
            "merchantDetails" => [
                "merchantId" => "100008000003683",
                "terminalId" => "10000001"
            ]
        ];

        $payloadString = json_encode($payloadObj, JSON_UNESCAPED_SLASHES);

        // Generate HMAC Signature
        $messageToSign = $apiKey . $clientRequestId . $timestamp . $payloadString;
        $hmac = hash_hmac('sha256', $messageToSign, $apiSecret, true);
        $signatureBase64 = base64_encode($hmac);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Api-Key' => $apiKey,
                'Client-Request-Id' => $clientRequestId,
                'Timestamp' => $timestamp,
                'Auth-Token-Type' => 'HMAC',
                'Authorization' => $signatureBase64
            ])->withBody($payloadString, 'application/json')
                ->post($endpoint);

            return response()->json([
                'status' => $response->status(),
                'response' => $response->json(),
                'debug' => [
                    'payload_sent' => $payloadString,
                    'endpoint' => $endpoint
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a balance inquiry request to Fiserv (Commerce Hub VAS)
     */
    public function checkBalance(Request $request)
    {
        // Using the exact credentials you provided
        $apiKey = 'PpJqTNxoBeXskIrAKav5tqvidB7tR46pSRBu7q3YSBWYQgyE';
        $apiSecret = '7b6JRqc5xUYNrvhAwDWLMlgE3bBQPn2rNQxz65GvbHEApiHd0eDYWn0Qpa0FFiP2';

        // Commerce Hub Host URL for Balance Inquiry
        $endpoint = 'https://connect-cert.fiservapis.com/ch/payments-vas/v1/accounts/balance-inquiry';

        // Generate required header parameters
        $clientRequestId = Str::uuid()->toString();
        $timestamp = (string) round(microtime(true) * 1000);

        // PAYLOAD FOR BALANCE INQUIRY (Gift Card Example)
        $payloadObj = [
            "source" => [
                "sourceType" => "PaymentCard",
                "card" => [
                    "cardData" => "9998955500000000190", // Test Gift Card
                    "expirationMonth" => "02",
                    "expirationYear" => "2035",
                    "securityCode" => "1234",
                    "category" => "GIFT",
                    "subCategory" => "GIFT_SOLUTIONS"
                ]
            ],
            "merchantDetails" => [
                "merchantId" => "100008000003683",
                "terminalId" => "10000001"
            ]
        ];

        $payloadString = json_encode($payloadObj, JSON_UNESCAPED_SLASHES);

        // Generate HMAC Signature
        $messageToSign = $apiKey . $clientRequestId . $timestamp . $payloadString;
        $hmac = hash_hmac('sha256', $messageToSign, $apiSecret, true);
        $signatureBase64 = base64_encode($hmac);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Api-Key' => $apiKey,
                'Client-Request-Id' => $clientRequestId,
                'Timestamp' => $timestamp,
                'Auth-Token-Type' => 'HMAC',
                'Authorization' => $signatureBase64
            ])->withBody($payloadString, 'application/json')
                ->post($endpoint);

            return response()->json([
                'status' => $response->status(),
                'response' => $response->json(),
                'debug' => [
                    'payload_sent' => $payloadString,
                    'endpoint' => $endpoint
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Process a refund request to Fiserv (Commerce Hub)
     */
    public function processRefund(Request $request)
    {
        // Using the exact credentials provided
        $apiKey = 'PpJqTNxoBeXskIrAKav5tqvidB7tR46pSRBu7q3YSBWYQgyE';
        $apiSecret = '7b6JRqc5xUYNrvhAwDWLMlgE3bBQPn2rNQxz65GvbHEApiHd0eDYWn0Qpa0FFiP2';

        // Commerce Hub Host URL for Refunds
        $endpoint = 'https://connect-cert.fiservapis.com/ch/payments/v1/refunds';

        // Generate required header parameters
        $clientRequestId = Str::uuid()->toString();
        $timestamp = (string) round(microtime(true) * 1000);

        // Extract the original transaction ID and amount from the incoming request.
        // For testing purposes, fallback values are provided (using your last successful transaction).
        $originalTransactionId = $request->input('transaction_id', '020073155f3093214b8abfc94268e00fdfbe');
        $refundAmount = $request->input('amount', 12.04);

        // PAYLOAD FOR REFUND
        // Updated based on Fiserv documentation:
        // 1. Removed 'source' object completely.
        // 2. Used 'referenceTransactionDetails' instead of 'transactionDetails'
        $payloadObj = [
            "amount" => [
                "total" => (float) $refundAmount,
                "currency" => "USD"
            ],
            "referenceTransactionDetails" => [
                "referenceTransactionId" => $originalTransactionId
            ],
            "merchantDetails" => [
                "merchantId" => "100008000003683",
                "terminalId" => "10000001"
            ]
        ];

        $payloadString = json_encode($payloadObj, JSON_UNESCAPED_SLASHES);

        // Generate HMAC Signature
        $messageToSign = $apiKey . $clientRequestId . $timestamp . $payloadString;
        $hmac = hash_hmac('sha256', $messageToSign, $apiSecret, true);
        $signatureBase64 = base64_encode($hmac);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Api-Key' => $apiKey,
                'Client-Request-Id' => $clientRequestId,
                'Timestamp' => $timestamp,
                'Auth-Token-Type' => 'HMAC',
                'Authorization' => $signatureBase64
            ])->withBody($payloadString, 'application/json')
                ->post($endpoint);

            return response()->json([
                'status' => $response->status(),
                'response' => $response->json(),
                'debug' => [
                    'payload_sent' => $payloadString,
                    'endpoint' => $endpoint
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

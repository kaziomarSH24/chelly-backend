<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class FiservPaymentService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $storeId; // Merchant ID
    protected string $terminalId; // Standard terminal ID

    public function __construct()
    {
        // Using config instead of hardcoded values for security
        $this->baseUrl = rtrim(config('services.fiserv.base_url', 'https://connect-cert.fiservapis.com/ch'), '/');
        $this->apiKey = config('services.fiserv.api_key');
        $this->apiSecret = config('services.fiserv.api_secret');
        $this->storeId = config('services.fiserv.store_id', '100008000003683');
        $this->terminalId = config('services.fiserv.terminal_id', '10000001');
    }

    /**
     * Process a direct payment charge
     */
    public function processCharge(float $amount, array $cardDetails, string $orderNumber)
    {
        $endpoint = $this->baseUrl . '/payments/v1/charges';

        $payload = [
            "amount" => [
                "total" => round($amount, 2),
                "currency" => "USD"
            ],
            "source" => [
                "sourceType" => "PaymentCard",
                "card" => [
                    "cardData" => $cardDetails['card_number'],
                    "expirationMonth" => $cardDetails['exp_month'],
                    "expirationYear" => $cardDetails['exp_year'],
                    "securityCode" => $cardDetails['cvv']
                ]
            ],
            "transactionDetails" => [
                "captureFlag" => true,
                "merchantTransactionId" => $orderNumber
            ],
            "merchantDetails" => [
                "merchantId" => $this->storeId,
                "terminalId" => $this->terminalId
            ]
        ];

        return $this->sendRequest($endpoint, $payload);
    }

    /**
     * Process a refund
     */
    public function processRefund(float $amount, string $originalTransactionId)
    {
        $endpoint = $this->baseUrl . '/payments/v1/refunds';

        $payload = [
            "amount" => [
                "total" => round($amount, 2),
                "currency" => "USD"
            ],
            "referenceTransactionDetails" => [
                "referenceTransactionId" => $originalTransactionId
            ],
            "merchantDetails" => [
                "merchantId" => $this->storeId,
                "terminalId" => $this->terminalId
            ]
        ];

        return $this->sendRequest($endpoint, $payload);
    }

    /**
     * Common method to generate headers and send the request
     */
    private function sendRequest(string $endpoint, array $payloadObj)
    {
        $clientRequestId = (string) Str::uuid();
        $timestamp = (string) round(microtime(true) * 1000);

        $payloadString = json_encode($payloadObj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Generate HMAC Signature
        $messageToSign = $this->apiKey . $clientRequestId . $timestamp . $payloadString;
        $hmacHex = hash_hmac('sha256', $messageToSign, $this->apiSecret, true);
        $signatureBase64 = base64_encode($hmacHex);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Api-Key' => $this->apiKey,
            'Client-Request-Id' => $clientRequestId,
            'Timestamp' => $timestamp,
            'Auth-Token-Type' => 'HMAC',
            'Authorization' => $signatureBase64
        ];

        $response = Http::withHeaders($headers)
            ->withBody($payloadString, 'application/json')
            ->post($endpoint);

        if ($response->failed()) {
            throw new Exception('Fiserv Payment Error: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get transaction history and status directly from Fiserv Commerce Hub.
     */
    public function transactionInquiry(string $transactionId)
    {
        $payload = [
            'referenceTransactionDetails' => [
                'referenceTransactionId' => $transactionId
            ],
            'merchantDetails' => [
                'merchantId' => config('services.fiserv.merchant_id')
            ]
        ];
        return $this->sendRequest('/payments/v1/transaction-inquiry', $payload);
    }
}

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
    protected string $storeId; // This acts as the merchantId

    public function __construct()
    {
        $this->baseUrl = config('services.fiserv.base_url');
        $this->apiKey = config('services.fiserv.api_key');
        $this->apiSecret = config('services.fiserv.api_secret');
        $this->storeId = config('services.fiserv.store_id');
    }

    public function createPaymentLink(string $orderNumber, float $amount)
    {
        $endpoint = '/payments/v1/payment-urls';
        $url = rtrim($this->baseUrl, '/') . $endpoint;

        // Updated Payload matching the documentation exactly
        $payload = [
            'amount' => [
                'total' => round($amount, 2),
                'currency' => 'USD',
            ],
            'transactionType' => 'SALE',
            'merchantDetails' => [
                'merchantId' => $this->storeId, // Your MID: 100008000003683
                'terminalId' => '10000001'      // Standard test terminal ID
            ],
            'transactionDetails' => [
                'merchantTransactionId' => $orderNumber
            ]
        ];

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $headers = $this->generateHeaders($payloadJson);

        $response = Http::withHeaders($headers)
            ->withBody($payloadJson, 'application/json')
            ->post($url);

        if ($response->failed()) {
            throw new Exception('Fiserv Payment Error: ' . $response->body());
        }

        return $response->json();
    }

    private function generateHeaders(string $payloadJson): array
    {
        $clientRequestId = (string) Str::uuid();
        $timestamp = (string) intval(microtime(true) * 1000);

        $rawSignature = $this->apiKey . $clientRequestId . $timestamp . $payloadJson;

        // Use 'false' to output lower-case hex, then base64 encode it (As per the documentation example)
        $hmacHex = hash_hmac('sha256', $rawSignature, $this->apiSecret, false);
        $base64Signature = base64_encode($hmacHex);

        return [
            'Content-Type' => 'application/json',
            'Api-Key' => $this->apiKey,
            'Timestamp' => $timestamp,
            'Client-Request-Id' => $clientRequestId,
            'Auth-Token-Type' => 'HMAC',                 // Added exactly as per docs
            'Authorization' => $base64Signature,         // Replaced Message-Signature
        ];
    }
}

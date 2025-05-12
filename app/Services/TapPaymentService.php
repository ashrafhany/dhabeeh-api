<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TapPaymentService
{
    protected $baseUrl;
    protected $secretKey;
    protected $headers;

    public function __construct()
    {
        $this->baseUrl = config('services.tap.base_url', env('TAP_BASE_URL'));
        $this->secretKey = config('services.tap.secret_key', env('TAP_SECRET_KEY'));
        $this->headers = [
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Create a new payment charge
     *
     * @param array $data Payment data
     * @return array|null Response data or null on failure
     */
    public function createCharge(array $data): ?array
    {
        return $this->makeRequest('post', 'charges', $data);
    }

    /**
     * Retrieve charge details by ID
     *
     * @param string $tapId Tap charge ID
     * @return array|null Charge data or null on failure
     */
    public function retrieveCharge(string $tapId): ?array
    {
        return $this->makeRequest('get', "charges/{$tapId}");
    }

    /**
     * Create a payment method to be used for recurring payments
     *
     * @param array $data Payment method data
     * @return array|null Response data or null on failure
     */
    public function createPaymentMethod(array $data): ?array
    {
        return $this->makeRequest('post', 'payment/methods', $data);
    }

    /**
     * Test the connection to TAP API
     *
     * @return array|null Response data or null on failure
     */
    public function testConnection(): ?array
    {
        try {
            // We'll just make a simple GET request to retrieve token info
            Log::info('Testing TAP API connection');

            $response = Http::withToken($this->secretKey)
                ->withHeaders($this->headers)
                ->get("{$this->baseUrl}/token");

            Log::info('TAP API test response', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'baseUrl' => $this->baseUrl,
                'hasSecretKey' => !empty($this->secretKey)
            ]);

            if ($response->failed()) {
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'message' => 'Connection test failed'
                ];
            }

            return [
                'success' => true,
                'status' => $response->status(),
                'message' => 'Connection successful'
            ];
        } catch (\Throwable $e) {
            Log::error('TAP API connection test failed', [
                'error' => $e->getMessage(),
                'baseUrl' => $this->baseUrl,
                'hasSecretKey' => !empty($this->secretKey)
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Make an HTTP request to the TAP API
     *
     * @param string $method HTTP method (get, post, etc)
     * @param string $endpoint API endpoint
     * @param array $data Request data for POST/PUT requests
     * @return array|null Response data or null on failure
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): ?array
    {
        $url = "{$this->baseUrl}/{$endpoint}";

        // Add detailed logging of the request
        Log::debug('TAP API Request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'url' => $url,
            'headers' => array_merge($this->headers, ['Authorization' => 'Bearer ' . substr($this->secretKey, 0, 10) . '...']),
            'data' => $data
        ]);

        try {
            $response = Http::withHeaders($this->headers)
                ->withToken($this->secretKey)
                ->$method($url, $data);

            $statusCode = $response->status();
            $responseData = $response->json();

            // Log the response
            Log::debug('TAP API Response', [
                'endpoint' => $endpoint,
                'status' => $statusCode,
                'response' => $responseData,
                'headers' => $response->headers()
            ]);

            if ($statusCode >= 400) {
                Log::error("Tap {$endpoint} request failed", [
                    'endpoint' => $endpoint,
                    'method' => $method,
                    'status' => $statusCode,
                    'response' => $responseData,
                    'data' => $data
                ]);
                return null;
            }

            return $responseData;
        } catch (\Exception $e) {
            Log::error("Tap API Exception: {$e->getMessage()}", [
                'endpoint' => $endpoint,
                'method' => $method,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }
}

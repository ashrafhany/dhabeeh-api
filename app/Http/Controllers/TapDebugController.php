<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TapPaymentService;
use Illuminate\Support\Facades\Log;

class TapDebugController extends Controller
{
    protected $tapService;

    public function __construct(TapPaymentService $tapService)
    {
        $this->tapService = $tapService;
    }

    /**
     * Test the TAP API connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        $result = $this->tapService->testConnection();

        return response()->json($result);
    }

    /**
     * Create a test charge with minimal data for testing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTestCharge(Request $request)
    {
        try {
            $amount = $request->input('amount', 10.00);

            $data = [
                'amount' => round($amount, 2),
                'currency' => 'SAR',
                'threeDSecure' => true,
                'save_card' => false,
                'description' => 'اختبار الدفع',
                'customer' => [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'email' => 'test@example.com',
                    'phone' => '966500000000'
                ],
                'source' => [
                    'id' => 'src_card'
                ],
                'redirect' => [
                    'url' => route('tap.callback')
                ]
            ];

            Log::info('Creating test TAP charge', ['data' => $data]);

            $response = $this->tapService->createCharge($data);

            Log::info('Test TAP charge response', ['response' => $response]);

            return response()->json([
                'success' => !empty($response),
                'data' => $response
            ]);
        } catch (\Throwable $e) {
            Log::error('Test TAP charge error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

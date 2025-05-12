<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TapPaymentService;
use Illuminate\Support\Facades\Log;

class TapTestController extends Controller
{
    protected $tapService;

    public function __construct(TapPaymentService $tapService)
    {
        $this->tapService = $tapService;
    }

    /**
     * Create a sample charge for testing
     */
    public function testCharge(Request $request)
    {
        try {
            // Build a minimalist charge request
            $chargeData = [
                'amount' => $request->input('amount', 10),
                'currency' => 'SAR',
                'threeDSecure' => true,
                'save_card' => false,
                'description' => 'اختبار الدفع',
                'customer' => [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'email' => 'test@example.com',
                    'phone' => '966500000000',
                ],
                'source' => [
                    'id' => 'src_card',
                    'type' => 'src',
                ],
                'redirect' => [
                    'url' => route('tap.callback'),
                ],
            ];

            // Override source data if provided
            if ($request->has('payment_method')) {
                $method = $request->input('payment_method');
                $source = [
                    'id' => $this->getSourceId($method),
                    'type' => $method === 'apple_pay' ? 'applepay' : 'src',
                ];
                $chargeData['source'] = $source;
            }

            Log::info('Running TAP test charge', ['data' => $chargeData]);

            $response = $this->tapService->createCharge($chargeData);

            return response()->json([
                'success' => !empty($response),
                'data' => $response,
                'request' => $chargeData
            ]);
        } catch (\Exception $e) {
            Log::error('Error in TAP test charge', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Get TAP source ID for testing
     */
    private function getSourceId($method)
    {
        return match ($method) {
            'apple_pay' => 'src_applepay',
            'visa' => 'src_card',
            'cash' => 'src_knet',
            'bank' => 'src_sa_bank',
            default => 'src_card'
        };
    }
}

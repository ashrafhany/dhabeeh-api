<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TapPaymentService;
use Illuminate\Support\Facades\Log;

class TapDirectTestController extends Controller
{
    protected $tapService;
    
    public function __construct(TapPaymentService $tapService)
    {
        $this->tapService = $tapService;
    }
    
    /**
     * Test creating a charge directly with TAP without going through the order flow
     * This is useful for isolating TAP API issues from other parts of the application
     */
    public function testDirectCharge(Request $request)
    {
        // Validate input
        $request->validate([
            'payment_method' => 'required|string|in:apple_pay,visa,cash,bank',
            'amount' => 'required|numeric|min:1',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
        ]);
        
        // Use default values for optional fields
        $firstName = $request->input('first_name', 'Test');
        $lastName = $request->input('last_name', 'User');
        $email = $request->input('email', 'test@example.com');
        $phone = $request->input('phone', '966500000000');
        $amount = $request->input('amount', 10);
        
        try {
            // Choose the correct source ID for the payment method
            $sourceId = $this->getSourceId($request->payment_method);
            $sourceType = $request->payment_method === 'apple_pay' ? 'applepay' : 'src';
            
            // Create the charge request similar to what happens in OrderController
            $chargeData = [
                'amount' => round($amount, 2),
                'currency' => 'SAR',
                'threeDSecure' => true,
                'save_card' => false,
                'description' => 'Direct TAP API Test',
                'customer' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                ],
                'source' => [
                    'id' => $sourceId,
                    'type' => $sourceType,
                ],
                'redirect' => [
                    'url' => route('tap.callback'),
                ],
            ];
            
            Log::info('TapDirectTest: Starting direct charge test', [
                'payment_method' => $request->payment_method,
                'source_id' => $sourceId,
                'source_type' => $sourceType,
            ]);
            
            // Send the request to TAP
            $chargeResponse = $this->tapService->createCharge($chargeData);
            
            if (!$chargeResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create charge. Check server logs for details.',
                    'request_data' => $chargeData
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'TAP charge created successfully',
                'charge_id' => $chargeResponse['id'] ?? null,
                'transaction_url' => $chargeResponse['transaction']['url'] ?? null,
                'status' => $chargeResponse['status'] ?? null,
                'full_response' => $chargeResponse,
                'request_data' => $chargeData
            ]);
            
        } catch (\Exception $e) {
            Log::error('TapDirectTest: Error in direct test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'request_data' => $chargeData ?? null
            ], 500);
        }
    }
    
    /**
     * Get the correct TAP source ID based on payment method
     */
    private function getSourceId($method)
    {
        return match ($method) {
            'apple_pay' => 'src_applepay',
            'visa'      => 'src_card',
            'cash'      => 'src_knet',
            'bank'      => 'src_sa_bank',
            default     => 'src_card'
        };
    }
}

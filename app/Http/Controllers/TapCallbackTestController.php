<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TapPaymentService;
use Illuminate\Support\Facades\Log;

class TapCallbackTestController extends Controller
{
    protected $tapService;
    
    public function __construct(TapPaymentService $tapService)
    {
        $this->tapService = $tapService;
    }
    
    /**
     * Test the TAP callback handling with a specific TAP ID
     */
    public function testCallback(Request $request)
    {
        try {
            $request->validate([
                'tap_id' => 'required|string',
            ]);
            
            $tapId = $request->input('tap_id');
            Log::info('TapCallbackTest: Testing TAP callback with ID', ['tap_id' => $tapId]);
            
            // Get the charge data from TAP API
            $chargeData = $this->tapService->retrieveCharge($tapId);
            
            if (!$chargeData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve charge information from TAP',
                ], 404);
            }
            
            // Create a new request with the tap_id
            $callbackRequest = new Request(['tap_id' => $tapId]);
            
            // Use the OrderController to process this callback
            $orderController = app(OrderController::class);
            $callbackResponse = $orderController->handleTapRedirect($callbackRequest);
            
            // Return the result with charge data for reference
            return response()->json([
                'success' => true,
                'message' => 'TAP callback test completed',
                'tap_id' => $tapId,
                'charge_data' => $chargeData,
                'callback_response' => json_decode($callbackResponse->getContent(), true),
            ]);
            
        } catch (\Exception $e) {
            Log::error('TapCallbackTest: Error testing callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error testing TAP callback',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TapPaymentService;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrderTesterController extends Controller
{
    protected $tapService;
    
    public function __construct(TapPaymentService $tapService) 
    {
        $this->tapService = $tapService;
    }
    
    /**
     * Test the TAP callback flow with a test order
     */
    public function testTapCallback(Request $request)
    {
        Log::info('Starting TAP callback test');
        
        try {
            // Create a test order if tap_id is not provided
            if (!$request->has('tap_id')) {
                // Get a test user or create one
                $user = User::firstOrFail();
                
                // Create a test order
                $order = Order::create([
                    'user_id' => $user->id,
                    'product_id' => 1, // Assuming product ID 1 exists
                    'variant_id' => 1, // Assuming variant ID 1 exists
                    'quantity' => 1,
                    'total_price' => 100,
                    'status' => 'pending',
                    'payment_method' => 'visa',
                    'payment_status' => 'pending'
                ]);
                
                // Create a test charge to get a real TAP ID
                $chargeData = [
                    'amount' => 100,
                    'currency' => 'SAR',
                    'threeDSecure' => true,
                    'save_card' => false,
                    'description' => 'Test Order',
                    'customer' => [
                        'first_name' => $user->first_name ?? 'Test',
                        'last_name' => $user->last_name ?? 'User',
                        'email' => $user->email ?? 'test@example.com',
                        'phone' => $user->phone ?? '966500000000',
                    ],
                    'source' => [
                        'id' => 'src_card',
                        'type' => 'src',
                    ],
                    'redirect' => [
                        'url' => route('tap.callback'),
                    ],
                    'metadata' => [
                        'user_id' => $user->id,
                        'order_ids' => $order->id,
                    ],
                ];
                
                $chargeResponse = $this->tapService->createCharge($chargeData);
                
                if (empty($chargeResponse) || empty($chargeResponse['id'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create test charge',
                    ], 500);
                }
                
                // Update the order with the TAP ID
                $order->update([
                    'tap_id' => $chargeResponse['id'],
                    'payment_url' => $chargeResponse['transaction']['url'] ?? null,
                ]);
                
                // Return the payment URL and TAP ID for testing
                return response()->json([
                    'success' => true,
                    'message' => 'Test order created with TAP payment',
                    'order_id' => $order->id,
                    'tap_id' => $chargeResponse['id'],
                    'payment_url' => $chargeResponse['transaction']['url'] ?? null,
                    'callback_url' => route('tap.callback') . '?tap_id=' . $chargeResponse['id'],
                    'charge_response' => $chargeResponse,
                ]);
            } else {
                // Simulate a callback with the provided TAP ID
                $tapId = $request->tap_id;
                $status = $request->status ?? 'CAPTURED';
                
                // Find the order
                $order = Order::where('tap_id', $tapId)->first();
                
                if (!$order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No order found with the provided TAP ID',
                    ], 404);
                }
                
                // Get the charge info from TAP API
                $chargeData = $this->tapService->retrieveCharge($tapId);
                
                // Simulate a callback to the handleTapRedirect method
                $request->replace(['tap_id' => $tapId]);
                
                // Use the controller method
                $orderController = app(OrderController::class);
                $result = $orderController->handleTapRedirect($request);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Callback test completed',
                    'order' => $order->refresh(),
                    'tap_response' => $chargeData,
                    'callback_result' => json_decode($result->getContent()),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in TAP callback test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error in TAP callback test',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Cart;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderFlowTestController extends Controller
{
    /**
     * Test the full order flow with TAP payment integration
     */
    public function testFullOrderFlow(Request $request)
    {
        Log::info('Starting full order flow test');

        try {
            DB::beginTransaction();

            // Step 1: Create or get test user
            $user = User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'phone' => '966500000000',
                    'role' => 'user',
                    'password' => bcrypt('password123')
                ]
            );

            // Step 2: Create a test product and variant if they don't exist
            $product = Product::firstOrCreate(
                ['name' => 'Test Product'],
                [
                    'category_id' => 1,
                    'description' => 'This is a test product',
                    'price' => 100,
                    'sale_price' => 90,
                    'is_available' => true,
                    'is_featured' => false,
                    'image' => 'test.jpg'
                ]
            );

            $variant = ProductVariant::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'name' => 'Default Variant'
                ],
                [
                    'price' => 100,
                    'sale_price' => 90,
                    'stock' => 100,
                    'is_default' => true
                ]
            );

            // Step 3: Clear user's cart and add the product
            Cart::where('user_id', $user->id)->delete();

            $cart = Cart::create([
                'user_id' => $user->id,
                'variant_id' => $variant->id,
                'quantity' => 1,
                'total_price' => $variant->sale_price ?: $variant->price
            ]);

            DB::commit();

            // Step 4: Create an auth token for the user
            $token = $user->createToken('test-token')->plainTextToken;

            // Step 5: Prepare the order request
            $orderData = [
                'payment_method' => $request->input('payment_method', 'visa'),
                'shipping_address' => $request->input('shipping_address', 'الاستلام من الفرع')
            ];

            // Step 6: Call the OrderController store method directly
            $orderController = app(OrderController::class);

            // Create a request with the order data
            $orderRequest = Request::create('/api/orders', 'POST', $orderData);

            // Set the authenticated user for the request
            $orderRequest->setUserResolver(function() use ($user) {
                return $user;
            });

            // Run the order controller method
            $response = $orderController->store($orderRequest);
            $responseContent = json_decode($response->getContent(), true);

            return response()->json([
                'success' => true,
                'message' => 'Full order flow test completed',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email
                ],
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name
                ],
                'variant' => [
                    'id' => $variant->id,
                    'name' => $variant->name
                ],
                'cart' => [
                    'id' => $cart->id,
                    'total_price' => $cart->total_price
                ],
                'order_response' => $responseContent,
                'auth_token' => $token
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in full order flow test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error in full order flow test',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

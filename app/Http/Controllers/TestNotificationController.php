<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use App\Notifications\PaymentStatusChanged;
use App\Models\Order;

class TestNotificationController extends Controller
{
    /**
     * Send a test order status notification to the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOrderStatusTest(Request $request)
    {
        $user = auth()->user();
        $orderId = $request->input('order_id');

        if (!$orderId) {
            // Get the latest order for the user
            $order = Order::where('user_id', $user->id)->latest()->first();
            if (!$order) {
                return response()->json([
                    'error' => 'No orders found for this user',
                ], 404);
            }
        } else {
            $order = Order::find($orderId);
            if (!$order || $order->user_id != $user->id) {
                return response()->json([
                    'error' => 'Order not found or does not belong to this user',
                ], 404);
            }
        }

        // Send a test notification
        $user->notify(new OrderStatusChanged($order, 'test'));

        return response()->json([
            'message' => 'Test order status notification sent successfully',
            'order_id' => $order->id
        ]);
    }

    /**
     * Send a test payment status notification to the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPaymentStatusTest(Request $request)
    {
        $user = auth()->user();
        $orderId = $request->input('order_id');

        if (!$orderId) {
            // Get the latest order for the user
            $order = Order::where('user_id', $user->id)->latest()->first();
            if (!$order) {
                return response()->json([
                    'error' => 'No orders found for this user',
                ], 404);
            }
        } else {
            $order = Order::find($orderId);
            if (!$order || $order->user_id != $user->id) {
                return response()->json([
                    'error' => 'Order not found or does not belong to this user',
                ], 404);
            }
        }

        // Send a test notification
        $user->notify(new PaymentStatusChanged($order));

        return response()->json([
            'message' => 'Test payment status notification sent successfully',
            'order_id' => $order->id
        ]);
    }
}

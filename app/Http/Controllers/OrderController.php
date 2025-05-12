<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use Illuminate\Support\Facades\Log;
use App\Services\TapPaymentService;

class OrderController extends Controller
{
    protected $tapService;

    public function __construct(TapPaymentService $tapService)
    {
        $this->tapService = $tapService;
    }

    public function index()
    {
        return OrderResource::collection(Order::with(['user', 'variant.product'])->latest()->paginate(15));
    }

    public function checkCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|exists:coupons,code',
        ]);

        $user = auth()->user();
        $cartItems = $user->cart()->with('variant.product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'السلة فارغة'], 400);
        }

        $coupon = Coupon::where('code', $request->coupon_code)->first();
        if (!$coupon || !$coupon->isvalid()) {
            return response()->json(['error' => 'كود الخصم غير صالح أو منتهي'], 400);
        }

        $totalOrderPrice = $cartItems->sum('total_price');
        $discountAmount = $coupon->calculateDiscount($totalOrderPrice);
        $finalTotalPrice = $totalOrderPrice - $discountAmount;

        cache()->put('applied_coupon_' . $user->id, $coupon->id, now()->addMinutes(10));
        cache()->put('discount_amount_' . $user->id, $discountAmount, now()->addMinutes(10));

        return response()->json([
            'total_price_before_discount' => $totalOrderPrice,
            'discount_amount'             => $discountAmount,
            'final_total_price'           => $finalTotalPrice,
            'message'                     => 'تم تطبيق الخصم بنجاح ' . $discountAmount . ' ريال من السعر الإجمالي',
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        $cartItems = $user->cart()->with('variant.product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'السلة فارغة'], 400);
        }

        $request->validate([
            'shipping_address' => 'nullable|string|max:255',
            'payment_method'   => 'required|string|in:apple_pay,visa,cash,bank',
        ]);

        $totalOrderPrice = $cartItems->sum('total_price');
        $discountAmount = cache()->pull('discount_amount_' . $user->id, 0);
        $couponId = cache()->pull('applied_coupon_' . $user->id, null);
        $finalTotalPrice = $totalOrderPrice - $discountAmount;

        Log::info('Applying Coupon:', [
            'discount_amount'     => $discountAmount,
            'coupon_id'           => $couponId,
            'total_order_price'   => $totalOrderPrice,
        ]);

        $orders = [];

        foreach ($cartItems as $cartItem) {
            $variant = $cartItem->variant;
            if (!$variant || $variant->stock < $cartItem->quantity) {
                return response()->json(['error' => 'بعض العناصر غير متوفرة في المخزون'], 400);
            }

            $itemDiscount = ($cartItem->total_price / $totalOrderPrice) * $discountAmount;
            $itemFinalPrice = max(0, $cartItem->total_price - $itemDiscount);

            $order = Order::create([
                'user_id'          => $user->id,
                'variant_id'       => $cartItem->variant_id,
                'quantity'         => $cartItem->quantity,
                'total_price'      => $itemFinalPrice,
                'status'           => 'pending',
                'shipping_address' => $request->input('shipping_address', 'الاستلام من الفرع'),
                'notes'            => $request->input('notes', ''),
                'coupon_id'        => $couponId,
                'discount_amount'  => $itemDiscount,
                'payment_method'   => $request->payment_method
            ]);

            $orders[] = $order;
        }

        $user->cart()->delete();

        Log::debug('TAP Payment Request Data', [
            'amount'       => round($finalTotalPrice, 2),
            'currency'     => 'SAR',
            'threeDSecure' => true,
            'save_card'    => false,
            'description'  => 'طلب من متجرنا',
            'metadata'     => [
                'user_id'  => $user->id,
                'order_ids' => implode(',', collect($orders)->pluck('id')->toArray()),
            ],
            'receipt'      => [
                'email' => true,
                'sms'   => true,
            ],
            'customer'     => [
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'phone'      => $user->phone,
            ],
            'source'       => [
                'id' => $this->getTapSourceId($request->payment_method),
                'type' => $request->payment_method === 'apple_pay' ? 'applepay' : 'src',
            ],
            'redirect'     => [
                'url' => route('tap.callback'),
            ],
        ]);

        Log::info('TAP Payment Request Sent', [
            'request_data' => [
                'amount' => round($finalTotalPrice, 2),
                'currency' => 'SAR',
                'threeDSecure' => true,
                'save_card' => false,
                'description' => 'طلب من متجرنا',
                'metadata' => [
                    'user_id' => $user->id,
                    'order_ids' => implode(',', collect($orders)->pluck('id')->toArray()),
                ],
                'receipt' => [
                    'email' => true,
                    'sms' => true,
                ],
                'customer' => [
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->email,
                    'phone'      => $user->phone,
                ],
                'source' => [
                    'id' => $this->getTapSourceId($request->payment_method),
                    'type' => $request->payment_method === 'apple_pay' ? 'applepay' : 'src',
                ],
                'redirect' => [
                    'url' => route('tap.callback'),
                ],
            ],
        ]);

        $chargeResponse = $this->tapService->createCharge([
            'amount'       => round($finalTotalPrice, 2),
            'currency'     => 'SAR',
            'threeDSecure' => true,
            'save_card'    => false,
            'description'  => 'طلب من متجرنا',
            'metadata'     => [
                'user_id'  => $user->id,
                'order_ids' => implode(',', collect($orders)->pluck('id')->toArray()),
            ],
            'receipt'      => [
                'email' => true,
                'sms'   => true,
            ],
            'customer'     => [
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'phone'      => $user->phone,
            ],
            'source'       => [
                'id' => $this->getTapSourceId($request->payment_method),
                'type' => $request->payment_method === 'apple_pay' ? 'applepay' : 'src',
            ],
            'redirect'     => [
                'url' => route('tap.callback'),
            ],
        ]);

        Log::info('TAP Payment Response Received', [
            'response_data' => $chargeResponse,
        ]);

        // Log the detailed charge response for debugging
        Log::debug('TAP Payment Response', [
            'response' => $chargeResponse,
            'request_payment_method' => $request->payment_method,
            'tap_source_id' => $this->getTapSourceId($request->payment_method),
            'callback_url' => route('tap.callback')
        ]);

        if (!$chargeResponse || empty($chargeResponse['id'])) {
            return response()->json(['error' => 'فشل في معالجة الدفع'], 500);
        }

        foreach ($orders as $order) {
            $order->update([
                'tap_id'      => $chargeResponse['id'],
                'payment_url' => $chargeResponse['transaction']['url'] ?? null,
            ]);
        }

        return response()->json([
            'message'           => 'تم إنشاء الطلب بنجاح!',
            'total_order_price' => $finalTotalPrice,
            'discount_amount'   => $discountAmount,
            'orders'            => OrderResource::collection($orders),
            'payment_url'       => $chargeResponse['transaction']['url'] ?? null,
        ], 201);
    }

    private function getTapSourceId($method)
    {
        return match ($method) {
            'apple_pay' => 'src_apple_pay',
            'visa'      => 'src_card',
            'cash'      => 'src_knet',  // KNET is typically used for cash payments in TAP
            'bank'      => 'src_sa.mada', // for Saudi bank transfers
            'STC_Pay'   =>'src_sa.stcpay',
            'google_pay' => 'src_google_pay',
            default     => 'src_card'
        };
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('delete', $order);

        if ($order->variant) {
            $order->variant->increment('stock', $order->quantity);
        }

        $order->delete();

        return response()->json(['message' => 'تم حذف الطلب بنجاح!']);
    }

    public function getOrdersByStatus($status)
    {
        if (!$status) {
            return response()->json(['error' => 'الحالة مطلوبة'], 400);
        }

        $orders = Order::with(['user', 'variant.product'])
            ->where('status', $status)
            ->paginate(15);

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'لا يوجد طلبات بهذه الحالة'], 200);
        }

        return OrderResource::collection($orders);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'variant.product'])->findOrFail($id);
        return new OrderResource($order);
    }

    /**
     * Update the order status
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,current,inDelivery,delivered,refused',
            'payment_status' => 'nullable|string|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string|max:255',
        ]);

        $order = Order::with(['user', 'variant.product'])->findOrFail($id);

        // Store current values before updating
        $oldStatus = $order->status;
        $oldPaymentStatus = $order->payment_status;

        // Update order
        $order->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status ?? $order->payment_status,
            'notes' => $request->has('notes') ? $request->notes : $order->notes,
        ]);

        // Reload the order with relationships
        $order = $order->fresh(['user', 'variant.product']);

        return response()->json([
            'message' => 'تم تحديث الطلب بنجاح',
            'order' => new OrderResource($order)
        ]);
    }

    public function handleTapRedirect(Request $request)
    {
        Log::info('TAP Payment Callback Received', ['request' => $request->all()]);

        $tap_id = $request->tap_id;

        if (!$tap_id) {
            Log::warning('TAP callback missing tap_id', ['request' => $request->all()]);
            return response()->json(['error' => 'Invalid request - missing tap_id'], 400);
        }

        $data = $this->tapService->retrieveCharge($tap_id);
        Log::info('TAP Charge Retrieved', ['tap_id' => $tap_id, 'data' => $data]);

        if (!$data) {
            return response()->json(['error' => 'فشل في استرجاع معلومات الدفع'], 500);
        }

        $orders = Order::where('tap_id', $tap_id)->get();

        if ($orders->isEmpty()) {
            Log::error('TAP Payment Callback - No matching orders found', ['tap_id' => $tap_id]);
            return response()->json(['error' => 'No matching orders found for this payment'], 404);
        }

        // Handle different payment statuses
        switch (strtoupper($data['status'])) {
            case 'CAPTURED':
                // Payment successful
                foreach ($orders as $order) {
                    $order->update([
                        'status'         => 'current',
                        'payment_status' => 'paid',
                    ]);
                }

                return response()->json([
                    'message' => 'تم الدفع بنجاح',
                    'status'  => 'success',
                    'orders'  => OrderResource::collection($orders),
                ]);

            case 'DECLINED':
            case 'FAILED':
            case 'ABANDONED':
            case 'CANCELLED':
            case 'RESTRICTED':
                // Payment failed
                foreach ($orders as $order) {
                    $order->update([
                        'payment_status' => 'failed',
                    ]);
                }

                return response()->json([
                    'message' => 'فشل في اتمام عملية الدفع',
                    'status'  => 'failed',
                    'reason'  => $data['response']['message'] ?? 'Unknown error',
                    'orders'  => OrderResource::collection($orders),
                ]);

            case 'AUTHORIZED':
            case 'INITIATED':
            case 'PENDING':
                // Payment in process
                return response()->json([
                    'message' => 'عملية الدفع قيد المعالجة',
                    'status'  => 'pending',
                    'orders'  => OrderResource::collection($orders),
                ]);

            default:
                Log::warning('TAP Payment Callback - Unhandled status', [
                    'tap_id' => $tap_id,
                    'status' => $data['status']
                ]);

                return response()->json([
                    'message' => 'حالة الدفع: ' . $data['status'],
                    'status'  => 'unknown',
                    'orders'  => OrderResource::collection($orders),
                ]);
        }
    }
}

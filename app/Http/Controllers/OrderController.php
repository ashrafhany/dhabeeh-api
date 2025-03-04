<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Models\User;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // جلب جميع الطلبات
    public function index()
    {
        return OrderResource::collection(Order::with(['user', 'variant.product'])->latest()->paginate(15));
    }

    // ✅ فحص الكوبون وحفظه في الجلسة
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
    if (!$coupon || !$coupon->isValid()) {
        return response()->json(['error' => 'كود الخصم غير صالح أو منتهي'], 400);
    }

    $totalOrderPrice = $cartItems->sum('total_price'); // إجمالي الطلب
    $discountAmount = $coupon->calculateDiscount($totalOrderPrice);
    $finalTotalPrice = $totalOrderPrice - $discountAmount;

    // ✅ حفظ بيانات الخصم في `cache()` بدلاً من `session()`
    cache()->put('applied_coupon_' . $user->id, $coupon->id, now()->addMinutes(10));
    cache()->put('discount_amount_' . $user->id, $discountAmount, now()->addMinutes(10));

    return response()->json([
        'total_price_before_discount' => $totalOrderPrice,
        'discount_amount' => $discountAmount,
        'final_total_price' => $finalTotalPrice,
        'message' =>'تم خصم '.$discountAmount.' ريال من السعر الإجمالي'
    ]);
}
    // ✅ إنشاء الطلب مع تطبيق الخصم
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
    ]);

    // ✅ استرجاع بيانات الخصم من `cache()`
    $totalOrderPrice = $cartItems->sum('total_price');
    $discountAmount = cache()->pull('discount_amount_' . $user->id, 0);
    $couponId = cache()->pull('applied_coupon_' . $user->id, null);
    $finalTotalPrice = $totalOrderPrice - $discountAmount;

    Log::info('Applying Coupon:', [
        'discount_amount' => $discountAmount,
        'coupon_id' => $couponId,
        'total_order_price' => $totalOrderPrice,
    ]);

    $orders = [];

    foreach ($cartItems as $cartItem) {
        $variant = $cartItem->variant;
        if (!$variant || $variant->stock < $cartItem->quantity) {
            return response()->json(['error' => 'بعض العناصر غير متوفرة في المخزون'], 400);
        }

        // ✅ توزيع الخصم على كل منتج حسب قيمته في السلة
        $itemDiscount = ($cartItem->total_price / $totalOrderPrice) * $discountAmount;
        $finalItemPrice = max(0, $cartItem->total_price - $itemDiscount);

        $order = Order::create([
            'user_id' => $user->id,
            'variant_id' => $cartItem->variant_id,
            'quantity' => $cartItem->quantity,
            'total_price' => $finalItemPrice,
            'status' => 'pending',
            'shipping_address' => $request->input('shipping_address', 'الاستلام من الفرع'),
            'notes'=>$request->input('notes',''),
            'coupon_id' => $couponId,
            'discount_amount' => $itemDiscount,
        ]);

        $orders[] = $order;
    }

    // ✅ حذف السلة بعد إتمام الطلب
    $user->cart()->delete();

    return response()->json([
        'message' => 'تم إنشاء الطلب بنجاح!',
        'total_order_price' => $finalTotalPrice,
        'discount_amount' => $discountAmount,
        'orders' => OrderResource::collection($orders),
    ], 201);
}

    // ✅ حذف طلب
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('delete', $order);

        // ✅ إرجاع الكمية للمخزون قبل الحذف
        if ($order->variant) {
            $order->variant->increment('stock', $order->quantity);
        }

        $order->delete();

        return response()->json(['message' => 'تم حذف الطلب بنجاح!']);
    }

    // ✅ جلب الطلبات حسب الحالة
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
}

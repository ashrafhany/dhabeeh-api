<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Models\User;
use App\Models\Product;
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // جل   ب جميع الطلبات
    public function index()
{
    return OrderResource::collection(Order::with(['user', 'variant.product'])->latest()->paginate(15));
}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // إضافة طلب جديد
    public function store(Request $request)
{
    if (!auth()->check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = auth()->user();
    $cartItems = $user->cart()->with('variant.product')->get(); // جلب كل عناصر السلة الخاصة بالمستخدم

    if ($cartItems->isEmpty()) {
        return response()->json(['error' => 'السلة فارغة'], 400);
    }

    $totalOrderPrice = 0;
    $orders = [];

    foreach ($cartItems as $cartItem) {
        $variant = $cartItem->variant; // جلب الـ variant المرتبط بالعنصر
        if (!$variant || $variant->stock < $cartItem->quantity) {
            return response()->json(['error' => 'بعض العناصر غير متوفرة في المخزون'], 400);
        }

        // حساب السعر بعد الخصم إن وجد
        $discountedPrice = $cartItem->total_price - $cartItem->discount_amount;
        $totalOrderPrice += $discountedPrice;

        // إنشاء الطلب لكل عنصر في السلة
        $order = Order::create([
            'user_id' => $user->id,
            'variant_id' => $cartItem->variant_id,
            'quantity' => $cartItem->quantity,
            'total_price' => $discountedPrice,
            'status' => 'pending',
        ]);

        $orders[] = $order;

        // تحديث المخزون
        $variant->decrement('stock', $cartItem->quantity);
    }

    // حذف السلة بعد إنشاء الطلب
    $user->cart()->delete();

    return response()->json([
        'message' => 'تم إنشاء الطلب بنجاح!',
        'total_order_price' => $totalOrderPrice,
        'orders' => $orders
    ], 201);
}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
        // عرض تفاصيل طلب معين
        public function show($id)
        {
            $order = Order::with(['user', 'variant.product'])->findOrFail($id);
            return new OrderResource($order);
        }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // تحديث حالة الطلب
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $this->authorize('update', $order);

        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Order updated successfully!', 'order' => new OrderResource($order)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('delete', $order);
        $order->delete();

            return response()->json(['message' => 'Order deleted successfully!']);

}
public function getOrdersByStatus(Request $request)
{
    $status = $request->get('status');
    if(!$status)
    {
        return response()->json(['error' => 'Status is required'], 400);
    }
    $orders = Order::with(['user', 'product'])->where('status', $status)->paginate(15);
    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No orders found'], 404);
    }
    return OrderResource::collection($orders);

}

    }


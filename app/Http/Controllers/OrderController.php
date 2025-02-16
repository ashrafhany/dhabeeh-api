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
        return OrderResource::collection(Order::with(['user', 'product'])->latest()->paginate(15));
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
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::select ('price','stock')->findOrfail($request->product_id);
        if ($product->stock < $request->quantity) {
            return response()->json(['error' => 'Product out of stock'], 400);
        }
        $totalPrice = $product->price * $request->quantity;

        $order = Order::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);
        $product->stock -= $request->quantity;
        return response()->json(['message' => 'Order created successfully!', 'order' => new OrderResource($order),'remaining_stock'=>$product->stock], 201);

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
        $order =Order::with(['user', 'product'])->findOrFail($id);
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


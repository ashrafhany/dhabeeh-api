<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();
        $totalPrice = $cartItems->sum(fn($item) => $item->product->price * $item->quantity - $item->discount);
        $totalDiscount = $cartItems->sum('discount');

        return response()->json([
            'total_price' => $totalPrice,
            'total_discount' => $totalDiscount,
            'cart_items' => $cartItems
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|exists:coupons,code',
            'options' => 'nullable|array', // ✅ إضافة خيارات المنتج
        ]);

        $userId = Auth::id();
        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'الكمية المطلوبة غير متوفرة'], 400);
        }

        $cartItem = Cart::firstOrNew(['user_id' => $userId, 'product_id' => $product->id]);

        // ✅ تحديث الكمية والخيارات
        if ($cartItem->exists) {
            $cartItem->quantity += $request->quantity;
        } else {
            $cartItem->quantity = $request->quantity;
        }

        $cartItem->options = $request->options; // ✅ تخزين الخيارات المختارة

        $totalPrice = $cartItem->quantity * $product->price;
        $discount = $this->applyCoupon($request->coupon_code, $totalPrice);

        $cartItem->total_price = max($totalPrice - $discount, 0);
        $cartItem->discount = $discount;
        $cartItem->save();

        $product->decrement('stock', $request->quantity);

        return response()->json([
            'message' => 'تمت الإضافة في السلة بنجاح',
            'cart' => $cartItem,
            'remaining_stock' => $product->stock
        ], 201);
    }

    private function applyCoupon($couponCode, $totalPrice)
    {
        if (!$couponCode) return 0;

        $coupon = Coupon::where('code', $couponCode)->first();
        if (!$coupon) return 0;

        return $coupon->type === 'percentage'
            ? min(($coupon->discount / 100) * $totalPrice, $totalPrice)
            : min($coupon->discount, $totalPrice);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'options' => 'nullable|array', // ✅ إمكانية تحديث الخيارات
        ]);

        $cartItem = Cart::where('user_id', Auth::id())->where('product_id', $id)->firstOrFail();
        $product = Product::findOrFail($id);

        $difference = $request->quantity - $cartItem->quantity;
        if ($difference > 0 && $product->stock < $difference) {
            return response()->json(['error' => 'الكمية المطلوبة غير متوفرة'], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->options = $request->options; // ✅ تحديث الخيارات المختارة
        $cartItem->total_price = $request->quantity * $product->price;
        $cartItem->save();

        $product->decrement('stock', $difference);

        return response()->json([
            'message' => 'تم تحديث الكمية والخيارات بنجاح',
            'cart' => $cartItem,
            'remaining_stock' => $product->stock
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'required|integer|min:1']);

        $cartItem = Cart::where('user_id', Auth::id())->where('product_id', $request->product_id)->first();
        if (!$cartItem) return response()->json(['error' => 'العنصر غير موجود في السلة'], 404);

        $product = Product::findOrFail($request->product_id);
        if ($cartItem->quantity > $request->quantity) {
            $cartItem->decrement('quantity', $request->quantity);
            $cartItem->decrement('total_price', $request->quantity * $product->price);
        } else {
            $cartItem->delete();
        }

        $product->increment('stock', $request->quantity);
        return response()->json(['message' => 'تمت إزالة الكمية من السلة بنجاح', 'remaining_stock' => $product->stock]);
    }

    public function clear()
    {
        $cartItems = Cart::where('user_id', Auth::id())->get();
        foreach ($cartItems as $cartItem) {
            $cartItem->product->increment('stock', $cartItem->quantity);
        }
        Cart::where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'تم إفراغ السلة بالكامل']);
    }
}

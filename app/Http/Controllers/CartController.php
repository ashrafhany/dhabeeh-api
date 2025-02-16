<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
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
    $totalPrice = 0; // جمع إجمالي السعر بعد الخصم
    $totalDiscount = 0; // جمع إجمالي الخصم

    foreach ($cartItems as $cartItem) {
        $itemPrice = $cartItem->product->price * $cartItem->quantity;
        $discount = $cartItem->discount; // جلب الخصم المخزن في قاعدة البيانات
        // **تطبيق الخصم إذا وُجد كوبون**
        if ($request->has('coupon_code') && !empty($request->coupon_code)) {
            $coupon = \App\Models\Coupon::where('code', $request->coupon_code)->first();

            if (!$coupon) {
                return response()->json(['message' => 'الكوبون غير صالح'], 400);
            }

            $discount = $this->applyCoupon($coupon, $itemPrice);
            $cartItem->discount = $discount; // ** تخزين قيمة الخصم في قاعدة البيانات**
            $cartItem->save();
            $itemPrice -= $discount;
        }

        // تحديث السعر لكل منتج بعد الخصم
        $cartItem->total_price = $itemPrice;
        $totalPrice += $itemPrice;
        $totalDiscount += $discount;
    }
        return response()->json([
            'total_price' => $totalPrice,
            'total_discount' => $totalDiscount,
            'cart_items' => $cartItems
        ]);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

public function add(Request $request)
{
    // التحقق من المدخلات
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'coupon_code' => 'nullable|exists:coupons,code'
    ]);

    $userId = Auth::id();
    $product = Product::findOrFail($request->product_id);
    if ($product->stock < $request->quantity) {
        return response()->json(['message' => 'الكمية المطلوبة غير متوفرة'], 400);
    }
    // جلب أو إنشاء العنصر في السلة
    $cartItem = Cart::firstOrNew([
        'user_id' => $userId,
        'product_id' => $product->id
    ]);

    // تحديث الكمية
    $cartItem->quantity += $request->quantity;

    // حساب إجمالي السعر قبل الخصم
    $totalPrice = $cartItem->quantity * $product->price;
    $discount = 0;

    // تطبيق الخصم إذا كان هناك كوبون
    if ($request->has('coupon_code') && !empty($request->coupon_code)) {
        $coupon = \App\Models\Coupon::where('code', $request->coupon_code)->first();

        // تحقق من الكوبون
        if (!$coupon) {
            return response()->json(['message' => 'الكوبون غير صالح أو منتهي'], 400);
        }

        // حساب الخصم بناءً على نوع الكوبون
        $discount = $this->applyCoupon($coupon, $totalPrice);
        $totalPrice -= $discount; // خصم السعر
    }

    // تحديث إجمالي السعر في السلة
    $cartItem->total_price = max($totalPrice, 0);  // التأكد من أن السعر النهائي غير سلبي
    $cartItem->discount = $discount;  // تخزين قيمة الخصم في قاعدة البيانات
    $cartItem->save();
// **تحديث المخزون بعد نجاح إضافة المنتج للسلة**
$product->stock -= $request->quantity;
$product->save();

    return response()->json([
        'message' => 'تمت الإضافة في السلة بنجاح',
        'cart' => $cartItem,
        'remaining_stock' => $product->stock
    ], 201);
}

// إضافة كوبون وتطبيق الخصم بناءً على نوع الكوبون
private function applyCoupon($coupon, $totalPrice)
{
    if ($coupon->type === 'percentage') {
        // تأكد من أن الخصم لا يتجاوز السعر الكلي
        return min(($coupon->discount / 100) * $totalPrice, $totalPrice);
    } elseif ($coupon->type === 'fixed') {
        // تأكد من أن الخصم لا يتجاوز السعر الكلي
        return min($coupon->discount, $totalPrice);
    }

    return 0;
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::where('user_id', Auth::id())->where('product_id', $id)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'هذا المنتج غير موجود في السلة'], 404);
        }

        $product = Product::findOrFail($id);
        $difference = $request->quantity - $cartItem->quantity;

        if ($difference > 0 && $product->stock < $difference) {
            return response()->json(['error' => 'الكمية المطلوبة غير متوفرة. الكمية المتاحة: ' . $product->stock], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->total_price = $cartItem->quantity * $product->price;
        $cartItem->save();

        // تحديث المخزون
        $product->stock -= $difference;
        $product->save();

        return response()->json([
            'message' => 'تم تحديث الكمية بنجاح',
            'cart' => $cartItem,
            'remaining_stock' => $product->stock
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /*
    public function remove ($id)
    {
        $cartItem = Cart::where('user_id', Auth::id())->where('product_id', $id)->first();
        if (!$cartItem) {
            return response()->json(['message' => 'هذا المنتج غير موجود في السلة'], 404);
        }
        $cartItem->delete();
        return response()->json(['message' => 'تمت إزالة المنتج من السلة بنجاح']);
    }
*/
public function remove(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
    ]);

    // البحث عن العنصر في السلة
    $cartItem = Cart::where('user_id', Auth::id())
                    ->where('product_id', $request->product_id)
                    ->first();

    if (!$cartItem) {
        // إذا كان العنصر غير موجود في السلة
        return response()->json(['error' => 'العنصر غير موجود في السلة '], 404);
    }

    $product = Product::findOrFail($request->product_id);

        if ($cartItem->quantity > $request->quantity) {
            $cartItem->quantity -= $request->quantity;
            $cartItem->total_price = $cartItem->quantity * $product->price;
            $cartItem->save();
        } else {
            $cartItem->delete();
        }

        // إعادة الكمية إلى المخزون
        $product->stock += $request->quantity;
        $product->save();

        return response()->json([
            'message' => 'تمت إزالة الكمية من السلة بنجاح',
            'remaining_stock' => $product->stock
        ]);
    }



    public function clear()
    {
        $cartItems = Cart::where('user_id', Auth::id())->get();

        foreach ($cartItems as $cartItem) {
            $product = Product::find($cartItem->product_id);
            if ($product) {
                $product->stock += $cartItem->quantity;
                $product->save();
            }
        }

        Cart::where('user_id', Auth::id())->delete();

        return response()->json(['message' => 'تم إفراغ السلة بالكامل']);

    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductOption;
use App\Models\CartOption;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
{
    $cartItems = Cart::with(['variant.product', 'options', 'coupon']) // ✅ تحميل بيانات الكوبون
        ->where('user_id', Auth::id())
        ->get(['id', 'user_id', 'variant_id', 'quantity', 'total_price', 'coupon_id', 'discount_amount']);

    return response()->json([
        'total_price' => $cartItems->sum(fn($item) => max($item->total_price - $item->discount_amount, 0)),
        'total_discount' => $cartItems->sum('discount_amount'),
        'cart_items' => $cartItems,
    ]);
}


    public function add(Request $request)
{
    $request->validate([
        'variant_id' => 'required|exists:product_variants,id',
        'quantity' => 'required|integer|min:1',
        'coupon_code' => 'nullable|exists:coupons,code',
        'options' => 'nullable|array',
    ]);

    $userId = Auth::id();
    $variant = ProductVariant::findOrFail($request->variant_id);

    if ($variant->stock < $request->quantity) {
        return response()->json(['message' => 'الكمية المطلوبة غير متوفرة'], 400);
    }

    // ✅ البحث عن المنتج في السلة أو إنشاؤه
    $cartItem = Cart::firstOrNew([
        'user_id' => $userId,
        'variant_id' => $variant->id
    ]);

    if ($cartItem->exists) {
        $cartItem->quantity += $request->quantity;
    } else {
        $cartItem->quantity = $request->quantity;
    }

    // ✅ حساب `total_price` قبل الحفظ
    $totalPrice = $cartItem->quantity * $variant->price;
    $cartItem->total_price = $totalPrice;

    $cartItem->save(); // ✅ حفظ العنصر في السلة

    // ✅ التعامل مع الخيارات (options)
    if ($request->options) {
        $cartItem->options()->delete(); // حذف الخيارات القديمة

        foreach ($request->options as $option) {
            $opt = ProductOption::findOrFail($option['option_id']);
            $optionTotal = $opt->price * ($option['quantity'] ?? 1);

            CartOption::create([
                'cart_id' => $cartItem->id,
                'option_id' => $option['option_id'],
                'quantity' => $option['quantity'] ?? 1,
                'total_price' => $optionTotal
            ]);

            $totalPrice += $optionTotal;
        }
    }

  // ✅ تطبيق الكوبون (إن وجد)
$cartItem->coupon_id = $request->coupon_code ? Coupon::where('code', $request->coupon_code)->value('id') : null;
$discount = $this->applyCoupon($request->coupon_code, $totalPrice);
$cartItem->discount_amount = $discount;
$cartItem->total_price = max($totalPrice - $discount, 0); // ✅ تحديث السعر النهائي
$cartItem->save();


    // ✅ تحديث المخزون
    $variant->decrement('stock', $request->quantity);

    return response()->json([
        'message' => 'تمت الإضافة إلى السلة بنجاح',
        'cart' => $cartItem->load('options'),
        'remaining_stock' => $variant->stock
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
            'options' => 'nullable|array',
        ]);

        $cartItem = Cart::where('user_id', Auth::id())->where('variant_id', $id)->firstOrFail();
        $variant = ProductVariant::findOrFail($id);

        $difference = $request->quantity - $cartItem->quantity;
        if ($difference > 0 && $variant->stock < $difference) {
            return response()->json(['error' => 'الكمية المطلوبة غير متوفرة'], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        // ✅ تحديث الخيارات
        if ($request->options) {
            $cartItem->options()->delete(); // مسح الخيارات القديمة
            foreach ($request->options as $option) {
                $opt = ProductOption::findOrFail($option['option_id']);
                $optionTotal = $opt->price * ($option['quantity'] ?? 1);

                CartOption::create([
                    'cart_id' => $cartItem->id,
                    'option_id' => $option['option_id'],
                    'quantity' => $option['quantity'] ?? 1,
                    'total_price' => $optionTotal
                ]);
            }
        }

        // ✅ حساب السعر النهائي
        $totalPrice = $cartItem->quantity * $variant->price + $cartItem->options()->sum('total_price');
        $cartItem->total_price = $totalPrice;
        $cartItem->save();

        $variant->decrement('stock', $difference);

        return response()->json([
            'message' => 'تم تحديث الكمية والخيارات بنجاح',
            'cart' => $cartItem->load('options'),
            'remaining_stock' => $variant->stock
        ]);
    }


    public function remove(Request $request)
{
    $request->validate([
        'variant_id' => 'required|exists:product_variants,id',
        'quantity' => 'required|integer|min:1'
    ]);

    $cartItem = Cart::where('user_id', Auth::id())->where('variant_id', $request->variant_id)->first();
    if (!$cartItem) return response()->json(['error' => 'العنصر غير موجود في السلة'], 404);

    $variant = ProductVariant::findOrFail($request->variant_id);

    if ($cartItem->quantity > $request->quantity) {
        $cartItem->decrement('quantity', $request->quantity);
        $cartItem->decrement('total_price', $request->quantity * $variant->price);
    } else {
        $cartItem->delete();
    }

    $variant->increment('stock', $request->quantity);

    return response()->json([
        'message' => 'تمت إزالة الكمية من السلة بنجاح',
        'remaining_stock' => $variant->stock
    ]);
}


public function clear()
{
    $cartItems = Cart::where('user_id', Auth::id())->get();
    foreach ($cartItems as $cartItem) {
        $cartItem->variant->increment('stock', $cartItem->quantity);
    }

    Cart::where('user_id', Auth::id())->delete();

    return response()->json(['message' => 'تم إفراغ السلة بالكامل']);
}

}

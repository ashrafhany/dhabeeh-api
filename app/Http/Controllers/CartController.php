<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\ProductOption;
use App\Models\CartOption;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
{
    if (!auth()->check()){
        return response()->json(['error'=>'Unauthorized'], 401);
    }
    $cartItems = Cart::with(['variant.product', 'options'])
        ->where('user_id', Auth::id())
        ->get(['id','user_id','variant_id','quantity', 'total_price']);
    if ($cartItems->isEmpty()){
        return response()->json([
            'message'=> 'السلة فارغة',
            'cart_items' => [],
        ]);
    }
        $deliveryFee =10.00;
        $vatPercentage = 0.15;
        $totalOrderPrice = $cartItems->sum('total_price');
        $discountAmount = cache()->get ('discount_amount_' . Auth::id(), 0);
        $subtotalBeforeDiscount = $totalOrderPrice + $deliveryFee;
        $vatAmount = $subtotalBeforeDiscount * $vatPercentage;
        $finalTotalPrice = $subtotalBeforeDiscount + $vatAmount - $discountAmount;
        return response()->json([
            'total_order_price'=> round($totalOrderPrice, 2),
            'delivery_fee'=> round ($deliveryFee, 2),
            'cart_items'=> $cartItems
        ]);
}
    public function add(Request $request)
    {
        $request->validate([
            'variant_id'=>'required|exists:product_variants,id',
            'quantity'=>'required|integer|min:1',
            'options'=>'nullable|array',
        ]);
        $userId = Auth::id();
        $variant = ProductVariant::findOrFail($request->variant_id);
        if ($variant->stock < $request-> quantity){
            return response()->json(['massage'=>'الكمية المطلوبة غير متوفرة'],400);
        }
        $cartItem = Cart:: firstOrNew([
            'user_id'=>$userId,
            'variant_id'=>$variant->id
        ]);
        if ($cartItem->exists){
            $cartItem->quantity += $request->quantity;
        }else{
            $cartItem->quantity = $request->quantity;
        }
        $totalPrice = $cartItem->quantity * $variant->price;
        $cartItem->total_price = $totalPrice;
        $cartItem->save();
        if($request-> options){
            $cartItem->options()->delete();
            foreach ($request->options as $option){
                $opt = ProductOption::findOrFail($option['option_id']);
                $optionTotal = $opt->price * ($option['quantity']?? 1);
                CartOption::create([
                    'cart_id'=>$cartItem->id,
                    'option_id'=>$option['option_id'],
                    'quantity'=> $option['quantity'] ?? 1,
                    'total_price'=>$optionTotal
                ]);
                $totalPrice += $optionTotal;
            }
        }
        $cartItem->total_price = $totalPrice;
        $cartItem->save();
        $variant->decrement('stock', $request-> quantity);
        return response()->json([
            'message'=>'تمت الاضافة الي السلة بنجاح',
            'cart'=>$cartItem->load('options'),
            'remaining_stock'=>$variant->stock],201);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity'=> 'required|integer|min:1',
            'options' => 'nullable|array',
        ]);
        $cartItem = Cart::where('user_id', Auth::id())->where('variant_id', $id)->firstOrFail();
        $variant = ProductVariant::findOrFail($id);
        $difference = $request->quantity - $cartItem->quantity;
        if ($difference > 0 && $variant ->stock < $difference){
            return response()->json(['error'=>'الكمية المطلوبة غير متوفرة'], 400);
        }
        $cartItem ->quantity = $request->quantity;
        $cartItem->save();
        if ($request->options){
            $cartItem->options()->delete();
            foreach ($request->options as $option){
                $otp = ProductOption::findOrFail($option['option_id']);
                $optionTotal = $otp->price * ($option['quantity'] ?? 1);
                CartOption::create([
                    'cart_id'=>$cartItem->id,
                    'option_id'=>$option['option_id'],
                    'quantity'=>$option['quantity']?? 1,
                    'total_price' => $optionTotal
                ]);
            }
        }
        $totalPrice = $cartItem->quantity * $variant->price + $cartItem->options()->sum('total_price');
        $cartItem->total_price = $totalPrice;
        $cartItem->save();
        $variant->decrement('stock', $difference);
        return response()->json([
            'message'=>'تم تحديث الكمية والخيارات بنجاح',
            'cart'=>$cartItem->load('options'),
            'remaining_stock'=>$variant->stock
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'variant_id'=>'required|exits:prouduct_variants,id',
            'quantity'=>'required|integer|min:1'
        ]);
        $cartItem = Cart::where('user_id', Auth::id())->where('variant_id', $request->variant_id)->first();
        if (!$cartItem) return response()->json(['error'=>'العنصر غير موجود في السلة'],404);
        $variant = ProductVariant::findOrFail($request->variant_id);
        if ($cartItem->quantity > $request->quantity){
            $cartItem ->decrement('quantity', $request->quantity);
            $cartItem->decrement('total_price', $request->quantity * $variant->price);
        }else{
            $cartItem->delete();
        }
        $variant->increment('stock', $request->quantity);
        return response()->json([
            'message'=>'تمت ازالة الكمية من السلة بنجاح',
            'remaining_stock'=>$variant->stock
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

<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Order;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    //جلب جميع المنتجات
    public function index(Request $request)
    {
        $products = Product::with('category')->paginate($request->get('per_page', 15));
        return ProductResource::collection($products);
    }
    //عرض تفاصيل منتج معين

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
       // dd($product->options);
        return response()->json([
            'product' => new ProductResource($product),
            'options' => $product->options // ✅ إضافة الخيارات مع المنتج
        ]);
    }

    public function getByCategory($category_id)
    {
        // البحث عن المنتجات التي تنتمي إلى التصنيف المحدد
    $products = Product::where('category_id', $category_id)->paginate(15);

    // إذا لم يتم العثور على أي منتجات
    if ($products->isEmpty()) {
        return response()->json(['message' => 'No products found for this category'], 404);
    }

    return ProductResource::collection($products);
    }
    //البحث عن منتج
    public function search(Request $request)
    {
        $query =$request->get('query');
        if (!$query) {
            return response()->json(['message' => 'No query found'], 400);
        }
        $products = Product::where('name', 'like', "%$query%")
        ->orWhere('description', 'like', "%$query%")
        ->paginate(15);
        return ProductResource::collection($products);
    }
    //جلب المنتجات حسب التصنيف
    public function productsByCategory($category_id)
    {
        $products = Product::where('category_id', $category_id)->get();
        return response()->json($products, 200);
    }
public function topProducts()
{
        // جلب افضل 10 منتجات مبيعا
        $products = Product::withCount('orders')
        ->orderByDesc('orders_count') // ترتيب المنتجات بناءً على عدد الطلبات
        ->limit(10) // تحديد أفضل 10 منتجات فقط
        ->get();

    return ProductResource::collection($products);


}
}

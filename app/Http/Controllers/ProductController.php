<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    // جلب جميع المنتجات مع التصنيفات والمتغيرات والخيارات
    public function index(Request $request)
    {
        $products = Product::with(['category', 'variants', 'options'])->paginate($request->get('per_page', 15));
        return ProductResource::collection($products);
    }

    // عرض تفاصيل منتج معين مع التصنيفات والمتغيرات والخيارات
    public function show($id)
    {
        $product = Product::with(['category', 'variants', 'options'])->findOrFail($id);

        return response()->json([
            'product' => new ProductResource($product),
        ]);
    }

    // جلب المنتجات حسب التصنيف
    public function getByCategory($category_id)
    {
        $products = Product::where('category_id', $category_id)
            ->with(['category', 'variants', 'options'])
            ->paginate(15);

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found for this category'], 404);
        }

        return ProductResource::collection($products);
    }

    // البحث عن منتج
    public function search(Request $request)
    {
        $query = $request->get('query');

        if (!$query) {
            return response()->json(['message' => 'No query found'], 400);
        }

        $products = Product::where('name', 'like', "%$query%")
            ->orWhere('description', 'like', "%$query%")
            ->with(['category', 'variants', 'options'])
            ->paginate(15);

        return ProductResource::collection($products);
    }

    // جلب المنتجات الأكثر مبيعًا
    public function topProducts()
    {
        $products = Product::withCount('orders')
            ->orderByDesc('orders_count')
            ->with(['category', 'variants', 'options'])
            ->limit(10)
            ->get();

        return ProductResource::collection($products);
    }

    // إضافة منتج جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required|exists:categories,id', // التأكد من أن التصنيف موجود
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',
        ]);

        $data = $request->only(['name', 'description', 'price', 'category_id', 'stock']);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('public'), $filename);
            $data['image'] = $filename;
        }

        $product = Product::create($data);

        // ✅ إضافة المتغيرات (variants) إن وجدت
        if ($request->has('variants')) {
            $product->variants()->createMany($request->variants);
        }

        // ✅ إضافة الخيارات (options) إن وجدت
        if ($request->has('options')) {
            $product->options()->createMany($request->options);
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => new ProductResource($product)
        ], 201);
    }
}

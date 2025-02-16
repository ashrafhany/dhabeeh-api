<?php

namespace App\Http\Controllers;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use App\Models\Category;
class CategoryController extends Controller
{
    // جلب جميع التصنيفات
    public function index()
    {
        $categories = Category::with('products')->paginate(10);
        return CategoryResource::collection($categories);
    }

    // عرض تفاصيل تصنيف معين
    public function show($id)
    {
        /*
        $category = Category::with('products')->find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        */
        $category = Category::with('products')->findOrFail($id);
        return new CategoryResource($category);
}
}

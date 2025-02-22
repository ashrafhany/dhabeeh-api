<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    // 🟢 1️⃣ إضافة سلايدر جديد
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // يجب أن تكون صورة
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);

        // رفع الصورة إلى التخزين وحفظ اسم الملف فقط في قاعدة البيانات
        $imagePath = $request->file('image')->store('sliders', 'public');

        $slider = Slider::create([
            'image' => $imagePath,
            'title' => $request->title,
            'description' => $request->description,
            'active' => $request->active ?? true
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Slider added successfully!',
            'data' => $slider
        ], 201);
    }

    // 🟢 2️⃣ استرجاع جميع السلايدرز
    public function index()
    {
        $sliders = Slider::where('active', true)->get();

        return response()->json([
            'status' => true,
            'data' => $sliders
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Info;
class InfoController extends Controller
{
    public function about() {
        return response()->json(['content' => 'هذا التطبيق خاص ببيع الذبائح وتوصيلها.']);
    }

    public function contact() {
        return response()->json(['phone' => '+966123456789', 'email' => 'support@example.com']);
    }

    public function privacyPolicy() {
        return response()->json(['content' => 'سياسة الخصوصية هنا...']);
    }

    public function terms() {
        return response()->json(['content' => 'الشروط والأحكام هنا...']);
    }

    public function rateApp(Request $request) {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);
        return response()->json(['message' => 'شكرًا على تقييمك!', 'rating' => $request->rating]);
    }
}

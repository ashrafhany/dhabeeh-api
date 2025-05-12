<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Info;
class InfoController extends Controller
{
    public function about() {
        $info = Info::where('title', 'about')->first();
        $content = $info ? $info->content : 'هذا التطبيق خاص ببيع الذبائح وتوصيلها.';
        return response()->json([
            'content' => $content,
            'html_content' => $content, // للتطبيقات التي تدعم HTML
        ]);
    }

    public function contact() {
        $info = Info::where('title', 'contact')->first();
        if ($info) {
            try {
                $data = json_decode($info->content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    return response()->json($data);
                }
                // إذا لم يكن المحتوى JSON صحيح، نعرض المحتوى كما هو
                return response()->json([
                    'phone' => '+9660507944402',
                    'email' => 'support@example.com',
                    'content' => $info->content
                ]);
            } catch (\Exception $e) {
                // في حالة حدوث خطأ نعيد البيانات الافتراضية
                return response()->json(['phone' => '+9660507944402', 'email' => 'support@example.com']);
            }
        }
        return response()->json(['phone' => '+9660507944402', 'email' => 'support@example.com']);
    }

    public function privacyPolicy() {
        $info = Info::where('title', 'privacy_policy')->first();
        $content = $info ? $info->content : 'سياسة الخصوصية هنا...';
        return response()->json([
            'content' => $content,
            'html_content' => $content, // للتطبيقات التي تدعم HTML
        ]);
    }

    public function terms() {
        $info = Info::where('title', 'terms')->first();
        $content = $info ? $info->content : 'الشروط والأحكام هنا...';
        return response()->json([
            'content' => $content,
            'html_content' => $content, // للتطبيقات التي تدعم HTML
        ]);
    }

    public function rateApp(Request $request) {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);
        return response()->json(['message' => 'شكرًا على تقييمك!', 'rating' => $request->rating]);
    }
}

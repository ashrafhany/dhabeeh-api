<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Setting;
class SettingsController extends Controller
{
    public function getLanguage()
    {
        return response()->json(['language' =>Auth::user()->language]);
    }
    public function setLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,ar'   // ✅ إضافة قاعدة التحقق من الصحة
        ]);
        $user = Auth::user();
        $user->update(['language' => $request->language]);
        return response()->json(['message' => 'تم تحديث اللغة', 'language' => $user->language]);
    }
}

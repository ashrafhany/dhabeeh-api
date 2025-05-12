<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmController extends Controller
{
    /**
     * تسجيل أو تحديث توكن FCM للمستخدم الحالي
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registerToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'required|in:android,ios,web',
        ]);

        $user = Auth::user();
        $user->fcm_token = $request->token;
        $user->save();

        return response()->json([
            'message' => 'تم تسجيل رمز الجهاز بنجاح',
            'success' => true
        ]);
    }

    /**
     * تفعيل أو تعطيل الإشعارات للمستخدم الحالي
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toggleNotifications(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user = Auth::user();
        $user->notifications_enabled = $request->enabled;
        $user->save();

        return response()->json([
            'message' => $request->enabled
                ? 'تم تفعيل الإشعارات بنجاح'
                : 'تم إيقاف الإشعارات بنجاح',
            'notifications_enabled' => $user->notifications_enabled,
            'success' => true
        ]);
    }

    /**
     * حذف توكن FCM للمستخدم الحالي (تسجيل الخروج)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function removeToken(Request $request)
    {
        $user = Auth::user();
        $user->fcm_token = null;
        $user->save();

        return response()->json([
            'message' => 'تم حذف رمز الجهاز بنجاح',
            'success' => true
        ]);
    }
}

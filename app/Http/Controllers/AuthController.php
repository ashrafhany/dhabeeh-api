<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\TwilioService;
class AuthController extends Controller
{
    // تسجيل مستخدم جديد
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|string|email|unique:users',
            'phone' => 'required|string|unique:users|min:10|max:13',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password'=>null,
        ]);

        return response()->json([
            'message' => 'User registered successfully!',
            'token' => $user->createToken('authToken')->plainTextToken
        ], 201);
    }

    // تسجيل الدخول برقم الهاتف فقط
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:13',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['error' => 'Phone number not registered'], 404);
        }

        $token = $user->createToken('authToken')->plainTextToken;
        $expires_at = now()->addHours(6);
        // تحديث التوكن في جدول personal_access_tokens وتحديد صلاحية الانتهاء
    $user->tokens->last()->update([
        'expires_at' => $expires_at
    ]);
        return response()->json([
            'message' => 'Login successful!',
            'token' => $token,
            'expires_at' => $expires_at
        ]);
    }

        // إرسال رمز OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:13',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['error' => 'Phone number not registered'], 404);
        }

        // إنشاء رمز OTP عشوائي وحفظه في Cache لمدة 5 دقائق
        $otp = rand(100000, 999999);
        Cache::put('otp_' . $user->phone, $otp, now()->addMinutes(5));

        // إرسال OTP عبر SMS
        TwilioService::sendOtp($user->phone, $otp);

        return response()->json(['message' => 'OTP sent successfully!']);
    }

    // التحقق من رمز OTP
    public function verifyOtp(Request $request)
{
    $request->validate([
        'phone' => 'required|string|min:10|max:13',
        'otp' => 'required|string|min:6|max:6',
    ]);
    $cachedOtp = Cache::get('otp_' . $request->phone);
    if (!$cachedOtp|| $cachedOtp != $request->otp) {
        return response()->json(['error' => 'Invalid OTP'], 401);
    }
    Cache::forget('otp_' . $request->phone);
    $user = User::where('phone', $request->phone)->first();

    $token = $user->createToken('authToken')->plainTextToken;
    $expires_at = now()->addHours(1);
    return response()->json([
        'message' => 'Login successful!',
        'token' => $token
    ]);
}

    // تسجيل الخروج
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully!'
        ]);
    }

}

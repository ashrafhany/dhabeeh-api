<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // ✅ استيراد Rule بشكل صحيح
use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
class UserController extends Controller
{
    // استرجاع بيانات المستخدم الحالي
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully',
            'data'    => new UserResource($request->user()),
        ]);
    }
    public function profile()
    {
        return response()->json(auth()->user());
    }
    public function update(Request $request)
    {
        $user = auth()->user();
/*
        // التحقق مما إذا كان الرقم مستخدمًا من قبل مستخدم آخر
        if ($request->has('phone')) {
            $existingUser = \App\Models\User::where('phone', $request->phone)
                                            ->where('id', '!=', $user->id) // استبعاد المستخدم الحالي
                                            ->first();

            if ($existingUser) {
                return response()->json([
                    'message' => 'رقم الهاتف مستخدم بالفعل من قبل مستخدم آخر.',
                ], 400);
            }
        }
*/
        // ✅ التحقق من صحة البيانات المدخلة
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name'  => 'sometimes|string|max:255',
            //'phone'      => 'sometimes|string|max:20',
            'address'    => 'sometimes|string|max:255',
            'language'   => 'sometimes|string|in:ar,en',
        ]);

        $user->update($validatedData);

        // ✅ رفع الصورة الرمزية (Avatar) إذا تم تحميلها
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();
        }

        // ✅ إرجاع البيانات الجديدة بعد التحديث
      // ✅ إرجاع البيانات باستخدام UserResource
    return response()->json([
        "message" => "تم تحديث الملف الشخصي بنجاح",
        "user"    => new UserResource($user) // ✅ استخدام الـ Resource هنا
    ], 200);
    }
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete(); // Soft Delete

        return response()->json(['message' => 'Account deleted successfully'], 200);
    }

}

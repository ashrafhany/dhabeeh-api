<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmTestController extends Controller
{
    /**
     * إرسال إشعار اختباري إلى مستخدم محدد
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:255',
        ]);

        $user = User::find($request->user_id);
        if (!$user->fcm_token) {
            return response()->json([
                'message' => 'المستخدم لا يملك رمز FCM مسجل',
                'success' => false
            ], 400);
        }

        try {
            $result = $this->sendFcm(
                $user->fcm_token,
                $request->title,
                $request->body,
                [
                    'type' => 'test',
                    'sent_at' => now()->toDateTimeString(),
                ]
            );

            return response()->json([
                'message' => 'تم إرسال الإشعار الاختباري بنجاح',
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('فشل في إرسال إشعار اختباري: ' . $e->getMessage(), [
                'user_id' => $request->user_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'حدث خطأ أثناء إرسال الإشعار',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * إرسال إشعار اختباري للمستخدم الحالي
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendMeTestNotification(Request $request)
    {
        $user = Auth::user();

        if (!$user->fcm_token) {
            return response()->json([
                'message' => 'لا يوجد رمز FCM مسجل لحسابك. يرجى تسجيل الرمز أولاً',
                'success' => false
            ], 400);
        }

        $request->validate([
            'title' => 'nullable|string|max:100',
            'body' => 'nullable|string|max:255',
        ]);

        $title = $request->title ?? 'إشعار اختباري';
        $body = $request->body ?? 'هذا إشعار تجريبي للتحقق من عمل نظام الإشعارات';

        try {
            $result = $this->sendFcm(
                $user->fcm_token,
                $title,
                $body,
                [
                    'type' => 'test',
                    'user_id' => $user->id,
                    'sent_at' => now()->toDateTimeString(),
                ]
            );

            return response()->json([
                'message' => 'تم إرسال الإشعار بنجاح',
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('فشل في إرسال إشعار اختباري: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'حدث خطأ أثناء إرسال الإشعار',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * إرسال رسالة FCM
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return mixed
     */
    protected function sendFcm($token, $title, $body, $data = [])
    {
        $serviceAccountJson = json_decode(
            file_get_contents(storage_path(config('services.firebase.credentials'))),
            true
        );

        $now = time();
        $payload = [
            'iss' => $serviceAccountJson['client_email'],
            'sub' => $serviceAccountJson['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
        ];

        $jwt = JWT::encode(
            $payload,
            $serviceAccountJson['private_key'],
            'RS256'
        );

        $response = Http::post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        $accessToken = $response->json('access_token');

        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                            'content-available' => 1,
                        ]
                    ]
                ],
            ]
        ];

        $fcmEndpoint = sprintf(
            'https://fcm.googleapis.com/v1/projects/%s/messages:send',
            config('services.firebase.project_id')
        );

        $fcmResponse = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($fcmEndpoint, $message);

        if ($fcmResponse->successful()) {
            return $fcmResponse->json();
        } else {
            Log::error('FCM Error', [
                'status' => $fcmResponse->status(),
                'body' => $fcmResponse->body()
            ]);

            throw new \Exception('FCM Error: ' . $fcmResponse->body());
        }
    }
}

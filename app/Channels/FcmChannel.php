<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;

class FcmChannel
{
    /**
     * المسار الخاص بواجهة برمجة FCM
     */
    protected $fcmEndpoint = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';

    /**
     * إرسال الإشعار للجهاز المحدد
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        // التحقق من وجود معرف الجهاز
        if (!$fcmToken = $notifiable->fcm_token) {
            return;
        }

        // التحقق من وجود الدالة المطلوبة في كلاس الإشعار
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        // الحصول على بيانات الإشعار
        $data = $notification->toFcm($notifiable);

        // تنظيم بيانات الإشعارات حسب هيكل FCM
        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $data['title'] ?? 'إشعار جديد',
                    'body' => $data['body'] ?? '',
                ],
                'data' => $data['data'] ?? [],
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => $data['click_action'] ?? 'FLUTTER_NOTIFICATION_CLICK'
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

        try {
            // الحصول على مفتاح الوصول
            $accessToken = $this->getAccessToken();

            // إرسال الإشعار عبر FCM
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post(
                    sprintf($this->fcmEndpoint, config('services.firebase.project_id')),
                    $message
                );

            // تسجيل استجابة الإشعار
            if ($response->successful()) {
                Log::info('FCM notification sent successfully', ['response' => $response->json()]);
                return $response->json();
            } else {
                Log::error('FCM notification failed', [
                    'error' => $response->body(),
                    'status' => $response->status()
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * الحصول على رمز الوصول من ملف الخدمة
     *
     * @return string
     */
    protected function getAccessToken()
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

        return $response->json('access_token');
    }
}

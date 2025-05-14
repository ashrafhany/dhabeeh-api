<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestFcmNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:fcm-notification {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending FCM notification to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }

            if (!$user->fcm_token) {
                $this->error("User with ID {$userId} does not have an FCM token.");
                return 1;
            }

            $users = collect([$user]);
        } else {
            $users = User::whereNotNull('fcm_token')
                ->where('notifications_enabled', true)
                ->limit(1)
                ->get();

            if ($users->isEmpty()) {
                $this->error("No users with FCM tokens found.");
                return 1;
            }
        }

        $user = $users->first();
        $this->info("Testing FCM notification for user: {$user->id} ({$user->first_name} {$user->last_name})");

        try {
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

            $this->info("Requesting access token from Google...");
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (!$response->successful()) {
                $this->error("Failed to get access token: " . $response->body());
                return 1;
            }

            $accessToken = $response->json('access_token');
            $this->info("Got access token: " . substr($accessToken, 0, 10) . "...");

            // إعداد رسالة الإشعار
            $message = [
                'message' => [
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => 'اختبار الإشعارات',
                        'body' => 'هذا إشعار اختباري لفحص عمل خدمة الإشعارات',
                    ],
                    'data' => [
                        'type' => 'test',
                        'created_at' => now()->toDateTimeString(),
                    ],
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
                    ]
                ]
            ];

            $fcmEndpoint = sprintf(
                'https://fcm.googleapis.com/v1/projects/%s/messages:send',
                config('services.firebase.project_id')
            );

            $this->info("Sending notification to FCM...");
            $fcmResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($fcmEndpoint, $message);

            if ($fcmResponse->successful()) {
                $this->info("Notification sent successfully!");
                $this->info("Response: " . $fcmResponse->body());
                return 0;
            } else {
                $this->error("Failed to send FCM notification: " . $fcmResponse->body());
                Log::error('FCM Error in test command', [
                    'status' => $fcmResponse->status(),
                    'body' => $fcmResponse->body()
                ]);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
            Log::error('FCM Exception in test command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}

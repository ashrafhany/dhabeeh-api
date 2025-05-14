<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\FcmSetting;
use App\Models\NotificationLog;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Builder;

class ManageNotifications extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'إدارة التطبيق';
    protected static ?string $title = 'إرسال إشعارات';
    protected static ?string $navigationLabel = 'إرسال إشعارات';
    protected static ?string $slug = 'manage-notifications';
    protected static ?int $navigationSort = 30;
    protected static string $view = 'filament.pages.manage-notifications';

    // Add authorization check
    public static function canAccess(): bool
    {
        // Only allow admin users with @example.com emails
        // You can customize this logic based on your permission system
        return auth()->check() && auth()->user()->canAccessFilament();
    }

    public $notificationTitle;
    public $body;
    public $target_type = 'all';
    public $topic = 'general';
    public $user_ids = [];
    public $data = [];
    public $click_action = 'GENERAL';
    public $image_url = null;
    
    protected function getFormSchema(): array
    {
        return [
            Card::make()
                ->schema([
                    TextInput::make('notificationTitle')
                        ->label('عنوان الإشعار')
                        ->placeholder('أدخل عنوان الإشعار هنا')
                        ->required()
                        ->maxLength(100),
                    
                    Textarea::make('body')
                        ->label('محتوى الإشعار')
                        ->placeholder('أدخل نص الإشعار هنا')
                        ->required()
                        ->maxLength(255)
                        ->rows(3),
                    
                    Grid::make()
                        ->schema([
                            Select::make('target_type')
                                ->label('نوع الإرسال')
                                ->options([
                                    'all' => 'كل المستخدمين',
                                    'specific' => 'مستخدمين محددين',
                                    'topic' => 'حسب الموضوع'
                                ])
                                ->default('all')
                                ->reactive()
                                ->required(),
                            
                            Select::make('topic')
                                ->label('الموضوع')
                                ->options([
                                    'general' => 'عام',
                                    'orders' => 'الطلبات',
                                    'promotions' => 'العروض',
                                    'news' => 'الأخبار',
                                ])
                                ->default('general')
                                ->visible(fn ($get) => $get('target_type') === 'topic'),
                        ]),
                    
                    Select::make('user_ids')
                        ->label('المستخدمين')
                        ->multiple()
                        ->searchable()
                        ->options(function () {
                            return User::whereNotNull('fcm_token')
                                ->where('notifications_enabled', true)
                                ->pluck('first_name', 'id')
                                ->toArray();
                        })
                        ->visible(fn ($get) => $get('target_type') === 'specific'),
                    
                    TextInput::make('click_action')
                        ->label('إجراء عند النقر')
                        ->default('GENERAL')
                        ->placeholder('مثال: OPEN_ORDERS')
                        ->helperText('رمز الإجراء الذي سيتم تنفيذه عند النقر على الإشعار في التطبيق'),
                    
                    TextInput::make('image_url')
                        ->label('رابط صورة (اختياري)')
                        ->url()
                        ->placeholder('https://example.com/image.jpg')
                        ->helperText('رابط صورة يمكن عرضها مع الإشعار'),
                    
                    Toggle::make('include_data')
                        ->label('إضافة بيانات مخصصة')
                        ->helperText('إضافة بيانات إضافية للإشعار (JSON)')
                        ->reactive()
                        ->default(false),
                    
                    Textarea::make('data')
                        ->label('البيانات المخصصة (JSON)')
                        ->placeholder('{"key": "value", "key2": "value2"}')
                        ->helperText('أدخل البيانات بتنسيق JSON')
                        ->visible(fn ($get) => $get('include_data'))
                ])
                ->columns(1),
        ];
    }

    public function mount(): void
    {
        $this->form->fill();
    }
    
    /**
     * تبديل حالة إعداد معين (تفعيل/تعطيل)
     * 
     * @param string $key
     * @return void
     */
    public function toggleSetting(string $key): void
    {
        $currentValue = FcmSetting::isEnabled($key);
        $newValue = !$currentValue;
        
        FcmSetting::setValue($key, $newValue ? 'true' : 'false');
        
        FcmSetting::clearCache();
        
        $status = $newValue ? 'تفعيل' : 'تعطيل';
        
        Notification::make()
            ->success()
            ->title("تم $status الإعدادات")
            ->body("تم $status إعدادات $key بنجاح")
            ->send();
    }

    public function submit()
    {
        $data = $this->form->getState();
        
        try {
            // تحويل البيانات المخصصة من نص JSON إلى مصفوفة إذا كانت متوفرة
            $customData = [];
            if (!empty($data['data'])) {
                $customData = json_decode($data['data'], true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Notification::make()
                        ->danger()
                        ->title('خطأ في تنسيق JSON')
                        ->body('تأكد من إدخال تنسيق JSON صحيح للبيانات المخصصة.')
                        ->send();
                    return;
                }
            }
            
            // بيانات الإشعار الأساسية
            $notificationData = [
                'type' => 'admin_broadcast',
                'title' => $data['notificationTitle'],
                'body' => $data['body'],
                'click_action' => $data['click_action'],
                'created_at' => now()->toDateTimeString(),
            ];
            
            // دمج البيانات المخصصة مع الأساسية
            $notificationData = array_merge($notificationData, $customData);
            
            $sentCount = 0;
            
            // إرسال الإشعارات حسب نوع الهدف
            if ($data['target_type'] === 'all') {
                // إرسال لكل المستخدمين الذين لديهم توكن FCM
                $users = User::whereNotNull('fcm_token')
                    ->where('notifications_enabled', true)
                    ->get();
                
                foreach ($users as $user) {
                    if ($this->sendFcmToUser($user, $data['notificationTitle'], $data['body'], $notificationData, $data['image_url'])) {
                        $sentCount++;
                    }
                }
                
                Notification::make()
                    ->success()
                    ->title('تم إرسال الإشعارات')
                    ->body("تم إرسال $sentCount إشعار بنجاح من أصل " . $users->count())
                    ->send();
                
            } elseif ($data['target_type'] === 'specific') {
                // إرسال لمستخدمين محددين
                if (empty($data['user_ids'])) {
                    Notification::make()
                        ->warning()
                        ->title('لم يتم اختيار مستخدمين')
                        ->body('الرجاء اختيار مستخدم واحد على الأقل')
                        ->send();
                    return;
                }
                
                $users = User::whereIn('id', $data['user_ids'])
                    ->whereNotNull('fcm_token')
                    ->where('notifications_enabled', true)
                    ->get();
                
                foreach ($users as $user) {
                    if ($this->sendFcmToUser($user, $data['notificationTitle'], $data['body'], $notificationData, $data['image_url'])) {
                        $sentCount++;
                    }
                }
                
                Notification::make()
                    ->success()
                    ->title('تم إرسال الإشعارات')
                    ->body("تم إرسال $sentCount إشعار بنجاح من أصل " . $users->count())
                    ->send();
                
            } elseif ($data['target_type'] === 'topic') {
                // إرسال حسب الموضوع (غير مدعوم حالياً، يمكن إضافته لاحقاً)
                Notification::make()
                    ->warning()
                    ->title('الإرسال حسب الموضوع غير مدعوم حالياً')
                    ->body('هذه الميزة قيد التطوير وستكون متاحة قريباً')
                    ->send();
                return;
            }
            
            // تسجيل الإشعار المرسل
            NotificationLog::create([
                'title' => $data['notificationTitle'],
                'body' => $data['body'],
                'target_type' => $data['target_type'],
                'topic' => $data['target_type'] === 'topic' ? $data['topic'] : null,
                'sent_count' => $sentCount,
                'admin_id' => auth()->id(),
                'data' => $notificationData,
            ]);
            
            Log::info('تم إرسال إشعار جماعي', [
                'title' => $data['notificationTitle'],
                'body' => $data['body'],
                'target_type' => $data['target_type'],
                'sent_count' => $sentCount,
                'admin_id' => auth()->id(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('خطأ في إرسال الإشعارات', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            Notification::make()
                ->danger()
                ->title('خطأ في إرسال الإشعارات')
                ->body($e->getMessage())
                ->send();
        }
    }
    
    /**
     * إرسال إشعار FCM لمستخدم محدد
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param array $data
     * @param string|null $imageUrl
     * @return bool
     */
    private function sendFcmToUser(User $user, string $title, string $body, array $data = [], ?string $imageUrl = null): bool
    {
        try {
            if (!$user->fcm_token || !$user->notifications_enabled) {
                return false;
            }
            
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

            // إعداد رسالة الإشعار
            $message = [
                'message' => [
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
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
                    ]
                ]
            ];
            
            // إضافة صورة إذا كانت متوفرة
            if ($imageUrl) {
                $message['message']['notification']['image'] = $imageUrl;
            }
    
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
                return true;
            } else {
                Log::error('FCM Error for user ' . $user->id, [
                    'status' => $fcmResponse->status(),
                    'body' => $fcmResponse->body()
                ]);
                
                return false;
            }
        } catch (\Exception $e) {
            Log::error('FCM Exception for user ' . $user->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
}

<x-filament::page>
    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm mb-8">
        <h2 class="text-2xl font-semibold mb-4 text-primary-600">إرسال إشعارات جماعية</h2>

        <p class="mb-6 text-gray-600">
            يمكنك من هذه الصفحة إرسال إشعارات فورية للمستخدمين عبر Firebase Cloud Messaging (FCM).
            تأكد من تهيئة إعدادات Firebase بشكل صحيح قبل استخدام هذه الميزة.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                <h3 class="text-lg font-medium text-blue-700 mb-2">إحصائيات الإشعارات</h3>
                <dl class="grid grid-cols-2 gap-2">
                    <dt class="text-sm text-blue-600">المستخدمين المفعلين:</dt>
                    <dd class="text-sm font-bold">{{ \App\Models\User::whereNotNull('fcm_token')->where('notifications_enabled', true)->count() }}</dd>

                    <dt class="text-sm text-blue-600">حالة الإشعارات:</dt>
                    <dd class="text-sm font-bold">{{ \App\Models\FcmSetting::isEnabled('notification_enabled') ? 'مفعلة' : 'معطلة' }}</dd>

                    <dt class="text-sm text-blue-600">إشعارات الطلبات:</dt>
                    <dd class="text-sm font-bold">{{ \App\Models\FcmSetting::isEnabled('order_notifications') ? 'مفعلة' : 'معطلة' }}</dd>

                    <dt class="text-sm text-blue-600">إشعارات الدفع:</dt>
                    <dd class="text-sm font-bold">{{ \App\Models\FcmSetting::isEnabled('payment_notifications') ? 'مفعلة' : 'معطلة' }}</dd>
                </dl>
            </div>

            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                <h3 class="text-lg font-medium text-yellow-700 mb-2">تعليمات الإرسال</h3>
                <ul class="list-disc list-inside text-sm text-yellow-600 space-y-1">
                    <li>تأكد من كتابة عنوان واضح ومختصر للإشعار</li>
                    <li>استخدم محتوى مفيداً ومختصراً (أقل من 255 حرف)</li>
                    <li>يمكنك استهداف مستخدمين محددين أو إرسال إشعار للجميع</li>
                    <li>تأكد من تحديد إجراء النقر الصحيح لتوجيه المستخدمين</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
        <h3 class="text-xl font-semibold mb-4 text-primary-600">إرسال إشعار جديد</h3>

        <form wire:submit.prevent="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end mt-6">
                <x-filament::button type="submit">
                    إرسال الإشعار
                </x-filament::button>
            </div>
        </form>
    </div>

    <div class="p-4 mt-8 bg-white border border-gray-200 rounded-lg shadow-sm">
        <h3 class="text-xl font-semibold mb-4 text-primary-600">إعدادات الإشعارات</h3>

        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50">
                <div>
                    <h4 class="font-medium">تفعيل/تعطيل الإشعارات</h4>
                    <p class="text-sm text-gray-600">التحكم في إرسال كافة الإشعارات</p>
                </div>
                <div>
                    <button
                        wire:click="toggleSetting('notification_enabled')"
                        class="px-4 py-2 text-sm rounded-md {{ \App\Models\FcmSetting::isEnabled('notification_enabled') ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}"
                    >
                        {{ \App\Models\FcmSetting::isEnabled('notification_enabled') ? 'مفعلة' : 'معطلة' }}
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50">
                <div>
                    <h4 class="font-medium">إشعارات الطلبات</h4>
                    <p class="text-sm text-gray-600">إشعارات تغيير حالة الطلبات</p>
                </div>
                <div>
                    <button
                        wire:click="toggleSetting('order_notifications')"
                        class="px-4 py-2 text-sm rounded-md {{ \App\Models\FcmSetting::isEnabled('order_notifications') ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}"
                    >
                        {{ \App\Models\FcmSetting::isEnabled('order_notifications') ? 'مفعلة' : 'معطلة' }}
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50">
                <div>
                    <h4 class="font-medium">إشعارات الدفع</h4>
                    <p class="text-sm text-gray-600">إشعارات تغيير حالة الدفع</p>
                </div>
                <div>
                    <button
                        wire:click="toggleSetting('payment_notifications')"
                        class="px-4 py-2 text-sm rounded-md {{ \App\Models\FcmSetting::isEnabled('payment_notifications') ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}"
                    >
                        {{ \App\Models\FcmSetting::isEnabled('payment_notifications') ? 'مفعلة' : 'معطلة' }}
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50">
                <div>
                    <h4 class="font-medium">إشعارات العروض</h4>
                    <p class="text-sm text-gray-600">إشعارات العروض والتخفيضات</p>
                </div>
                <div>
                    <button
                        wire:click="toggleSetting('promotion_notifications')"
                        class="px-4 py-2 text-sm rounded-md {{ \App\Models\FcmSetting::isEnabled('promotion_notifications') ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}"
                    >
                        {{ \App\Models\FcmSetting::isEnabled('promotion_notifications') ? 'مفعلة' : 'معطلة' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>

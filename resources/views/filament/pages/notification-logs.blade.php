<x-filament::page>
    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm mb-8">
        <h2 class="text-2xl font-semibold mb-4 text-primary-600">سجل الإشعارات المرسلة</h2>

        <p class="mb-6 text-gray-600">
            يعرض هذا السجل قائمة بجميع الإشعارات التي تم إرسالها من لوحة المدير.
            يمكنك استخدام خيارات التصفية والبحث للعثور على إشعارات محددة.
        </p>

        {{ $this->table }}
    </div>
</x-filament::page>

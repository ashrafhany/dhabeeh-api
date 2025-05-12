<x-filament::page>
    <div>
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- ملخص المدفوعات -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-xl font-semibold text-green-600 dark:text-green-400">
                    إجمالي المدفوعات المكتملة
                </h3>
                <div class="text-3xl font-bold">
                    {{ number_format(\App\Models\Order::where('payment_status', 'paid')->sum(DB::raw('total_price - IFNULL(discount_amount, 0)')), 2) }} ريال
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-xl font-semibold text-blue-600 dark:text-blue-400">
                    عدد المعاملات المكتملة
                </h3>
                <div class="text-3xl font-bold">
                    {{ \App\Models\Order::where('payment_status', 'paid')->count() }} معاملة
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-4 text-xl font-semibold text-red-600 dark:text-red-400">
                    المبالغ قيد الانتظار
                </h3>
                <div class="text-3xl font-bold">
                    {{ number_format(\App\Models\Order::where('payment_status', 'pending')->sum(DB::raw('total_price - IFNULL(discount_amount, 0)')), 2) }} ريال
                </div>
            </div>
        </div>
    </div>

    <!-- جدول المعاملات من InteractsWithTable trait -->
    {{ $this->table }}
</x-filament::page>

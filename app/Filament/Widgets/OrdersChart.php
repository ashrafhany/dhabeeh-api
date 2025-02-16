<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\BarChartWidget;

class OrdersChart extends BarChartWidget
{
    protected static ?string $heading = 'إحصائيات الطلبات';
    protected static ?int $sort = 1; // ترتيب الودجت في الصفحة

    protected function getData(): array
    {
        $statuses = ['Pending', 'Current', 'inDelivery', 'Delivered', 'Refused'];

        $data = [];
        foreach ($statuses as $status) {
            $data[] = Order::where('status', $status)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلبات',
                    'data' => $data,
                    'backgroundColor' => ['#facc15', '#3b82f6', '#06b6d4', '#10b981', '#ef4444'],
                ],
            ],
            'labels' => ['قيد الانتظار', 'جاري التنفيذ', 'قيد التوصيل', 'تم التوصيل', 'مرفوض'],
        ];
    }
    protected function getType(): string
    {
        return 'line'; // يمكن تغييره إلى 'line' أو 'pie'
    }
}

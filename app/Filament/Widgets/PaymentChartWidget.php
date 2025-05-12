<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\LineChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentChartWidget extends LineChartWidget
{
    protected static ?string $heading = 'اتجاهات المدفوعات';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // جلب بيانات المدفوعات لآخر 30 يوم
        $data = $this->getPaymentData();

        return [
            'datasets' => [
                [
                    'label' => 'المدفوعات المكتملة (ريال)',
                    'data' => $data['amounts'],
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'عدد المعاملات',
                    'data' => $data['counts'],
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ]
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getPaymentData(): array
    {
        $days = 14; // عدد الأيام للعرض
        $dates = collect(range($days - 1, 0))->map(function ($daysAgo) {
            return Carbon::today()->subDays($daysAgo)->format('Y-m-d');
        });

        // جلب مجموع المدفوعات وعدد المعاملات حسب اليوم
        $paymentData = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price - IFNULL(discount_amount, 0)) as daily_amount'),
                DB::raw('COUNT(*) as daily_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // تحضير البيانات للرسم البياني
        $amounts = [];
        $counts = [];

        foreach ($dates as $date) {
            // القيم
            $amounts[] = $paymentData[$date]['daily_amount'] ?? 0;
            // عدد المعاملات
            $counts[] = $paymentData[$date]['daily_count'] ?? 0;
        }

        return [
            'labels' => $dates->toArray(),
            'amounts' => $amounts,
            'counts' => $counts,
        ];
    }
}

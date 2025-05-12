<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\DB;

class PaymentStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getCards(): array
    {
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_price');

        $pendingPayments = Order::where('payment_status', 'pending')->count();
        $paidOrders = Order::where('payment_status', 'paid')->count();
        $failedPayments = Order::where('payment_status', 'failed')->count();

        $percentagePaid = 0;
        $totalOrders = Order::count();
        if ($totalOrders > 0) {
            $percentagePaid = round(($paidOrders / $totalOrders) * 100);
        }

        // ترتيب الطرق الأكثر استخدامًا للدفع
        $topPaymentMethod = Order::where('payment_status', 'paid')
            ->select('payment_method', DB::raw('count(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->first();

        $paymentMethodDesc = $topPaymentMethod ? $topPaymentMethod->payment_method : 'لا يوجد';

        return [
            Card::make('إجمالي الإيرادات', number_format($totalRevenue, 2) . ' ريال')
                ->description('مجموع المدفوعات المكتملة')
                ->descriptionIcon('heroicon-s-cash')
                ->color('success'),

            Card::make('المدفوعات قيد الانتظار', $pendingPayments)
                ->description('طلبات تنتظر الدفع')
                ->descriptionIcon('heroicon-s-clock')
                ->color('warning'),

            Card::make('نسبة الدفع الناجح', $percentagePaid . '%')
                ->description($paidOrders . ' طلب مدفوع من أصل ' . $totalOrders)
                ->descriptionIcon('heroicon-s-chart-pie')
                ->chart([0, 10, $percentagePaid, 100])
                ->color($percentagePaid > 50 ? 'success' : 'warning'),

            Card::make('طريقة الدفع الأكثر استخدامًا', $paymentMethodDesc)
                ->description('الطريقة المفضلة للعملاء')
                ->descriptionIcon('heroicon-s-credit-card')
                ->color('primary'),
        ];
    }
}

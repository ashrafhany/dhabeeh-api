<?php

namespace App\Filament\Widgets;
use App\Models\Cart;
use Filament\Widgets\BubbleChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CartChart extends BubbleChartWidget
{
    protected static ?string $heading = 'السلة';

    protected function getData(): array
    {
        $dates = collect(range(0, 6))->map(function ($daysAgo) {
            return Carbon::today()->subDays($daysAgo)->format('Y-m-d');
        })->reverse();

        $carts = Cart::whereDate('created_at', '>=', Carbon::today()->subDays(6))
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as total_quantity')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total_quantity', 'date');
            return [
        'datasets' => [
                [
                    'label' => 'عدد المنتجات المضافة للسلة',
                    'data' => $dates->map(fn ($date) => $carts[$date] ?? 0)->toArray(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }
}

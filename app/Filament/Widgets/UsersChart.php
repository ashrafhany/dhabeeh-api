<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\User;
use Illuminate\Support\Carbon;

class UsersChart extends ChartWidget
{
    protected static ?string $heading = 'حركة تسجيل المستخدمين خلال آخر 7 أيام';

    protected function getData(): array
    {
        $dates = collect(range(6, 0))->map(function ($daysAgo) {
            return Carbon::now()->subDays($daysAgo)->format('Y-m-d');
        });

        $users = User::whereBetween('created_at', [Carbon::now()->subDays(6), Carbon::now()])
            ->selectRaw('DATE(created_at) as date, COUNT(id) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $usersData = $dates->map(fn ($date) => $users[$date] ?? 0)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'عدد المستخدمين الجدد',
                    'data' => $usersData,
                    'backgroundColor' => 'rgba(255, 110, 132, 0.5)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // يمكن تغييره إلى 'line' أو 'pie'
    }
}

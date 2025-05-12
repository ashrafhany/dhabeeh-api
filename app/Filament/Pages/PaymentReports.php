<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentReports extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cash';
    protected static ?string $navigationGroup = 'إدارة المالية';
    protected static ?string $title = 'تقارير المدفوعات';
    protected static ?string $navigationLabel = 'تقارير المدفوعات';
    protected static ?int $navigationSort = 100;
    protected static string $view = 'filament.pages.payment-reports';

    public function getTableQuery(): Builder
    {
        return Order::query()
            ->with(['user', 'variant.product'])
            ->whereNotNull('payment_status')
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('رقم الطلب')
                ->sortable(),
            TextColumn::make('user.first_name')
                ->label('المستخدم')
                ->searchable(),
            TextColumn::make('variant.product.name')
                ->label('المنتج')
                ->searchable(),
            TextColumn::make('payment_method')
                ->label('طريقة الدفع')
                ->searchable(),
            BadgeColumn::make('payment_status')
                ->label('حالة الدفع')
                ->enum([
                    'pending' => 'في انتظار الدفع',
                    'paid' => 'تم الدفع',
                    'failed' => 'فشل الدفع',
                    'refunded' => 'تم استرداد المبلغ'
                ])
                ->colors([
                    'warning' => 'pending',
                    'success' => 'paid',
                    'danger' => 'failed',
                    'secondary' => 'refunded',
                ])
                ->sortable(),
            TextColumn::make('total_price')
                ->label('المبلغ الإجمالي')
                ->formatStateUsing(fn ($record) => number_format($record->total_price, 2) . ' ريال')
                ->sortable(),
            TextColumn::make('discount_amount')
                ->label('قيمة الخصم')
                ->formatStateUsing(fn ($record) => $record->discount_amount ? number_format($record->discount_amount, 2) . ' ريال' : '-')
                ->sortable(),
            TextColumn::make('final_amount')
                ->label('المبلغ النهائي')
                ->formatStateUsing(fn ($record) => number_format($record->total_price - ($record->discount_amount ?? 0), 2) . ' ريال')
                ->sortable(),
            TextColumn::make('created_at')
                ->label('تاريخ الطلب')
                ->date('Y-m-d H:i')
                ->sortable(),
            TextColumn::make('payment_date')
                ->label('تاريخ الدفع')
                ->date('Y-m-d H:i')
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('payment_status')
                ->label('حالة الدفع')
                ->options([
                    'pending' => 'في انتظار الدفع',
                    'paid' => 'تم الدفع',
                    'failed' => 'فشل الدفع',
                    'refunded' => 'تم استرداد المبلغ'
                ]),
            Filter::make('created_at')
                ->form([
                    DatePicker::make('from')
                        ->label('من تاريخ'),
                    DatePicker::make('until')
                        ->label('إلى تاريخ'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
                ->label('تاريخ الطلب'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view_order')
                ->label('عرض الطلب')
                ->url(fn ($record) => url('/admin/orders/' . $record->id . '/edit'))
                ->icon('heroicon-o-eye'),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('export')
                ->label('تصدير التقرير')
                ->icon('heroicon-o-document-download')
                ->action(function () {
                    // يمكن إضافة كود لتصدير التقرير إلى Excel أو PDF
                    $this->notification()->success(
                        'تصدير التقرير',
                        'جاري تصدير التقرير، سيتم تحميله قريبًا'
                    );
                }),
        ];
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\NotificationLog;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;

class NotificationLogs extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'إدارة التطبيق';
    protected static ?string $title = 'سجل الإشعارات';
    protected static ?string $navigationLabel = 'سجل الإشعارات';
    protected static ?string $slug = 'notification-logs';
    protected static ?int $navigationSort = 31;

    // Add authorization check
    public static function canAccess(): bool
    {
        // Only allow admin users with @example.com emails
        // You can customize this logic based on your permission system
        return auth()->check() && auth()->user()->canAccessFilament();
    }
    
    protected static string $view = 'filament.pages.notification-logs';

    public function getTableQuery(): Builder
    {
        return NotificationLog::query()->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('title')
                ->label('العنوان')
                ->searchable(),

            TextColumn::make('body')
                ->label('المحتوى')
                ->limit(50)
                ->searchable(),

            TextColumn::make('target_type')
                ->label('نوع الهدف')
                ->enum([
                    'all' => 'كل المستخدمين',
                    'specific' => 'مستخدمين محددين',
                    'topic' => 'حسب الموضوع'
                ]),

            TextColumn::make('sent_count')
                ->label('عدد المرسل إليهم')
                ->sortable(),

            TextColumn::make('admin.name')
                ->label('بواسطة')
                ->searchable(),

            TextColumn::make('created_at')
                ->label('تاريخ الإرسال')
                ->dateTime('Y-m-d H:i:s')
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('target_type')
                ->label('نوع الهدف')
                ->options([
                    'all' => 'كل المستخدمين',
                    'specific' => 'مستخدمين محددين',
                    'topic' => 'حسب الموضوع'
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
                ->label('تاريخ الإرسال'),
        ];
    }
}

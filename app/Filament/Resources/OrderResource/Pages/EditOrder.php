<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف الطلب'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Order Tabs')
                ->tabs([
                    Tab::make('معلومات الطلب')
                        ->schema([
                            // بيانات الطلب الأساسية
                            Select::make('user_id')
                                ->label('المستخدم')
                                ->relationship('user', 'first_name')
                                ->searchable()
                                ->required(),
                            Select::make('variant_id')
                                ->label('المنتج')
                                ->relationship('variant', 'product.name')
                                ->searchable()
                                ->required(),
                            Select::make('status')
                                ->label('حالة الطلب')
                                ->options([
                                    'pending' => 'قيد الانتظار',
                                    'Current' => 'جاري التنفيذ',
                                    'inDelivery' => 'قيد التوصيل',
                                    'Delivered' => 'تم التوصيل',
                                    'Refused' => 'مرفوض',
                                ])
                                ->required(),
                            TextInput::make('quantity')
                                ->label('الكمية')
                                ->disabled(),
                            TextInput::make('shipping_address')
                                ->label('عنوان الشحن')
                                ->disabled(),
                            TextInput::make('notes')
                                ->label('ملاحظات')
                                ->disabled(),
                        ]),

                    Tab::make('معلومات الدفع')
                        ->schema([
                            // بيانات الدفع
                            Select::make('payment_status')
                                ->label('حالة الدفع')
                                ->options([
                                    'pending' => 'في انتظار الدفع',
                                    'paid' => 'تم الدفع',
                                    'failed' => 'فشل الدفع',
                                    'refunded' => 'تم استرداد المبلغ'
                                ])
                                ->required(),
                            TextInput::make('payment_method')
                                ->label('طريقة الدفع'),
                            TextInput::make('payment_id')
                                ->label('رقم المعاملة')
                                ->disabled(),
                            DateTimePicker::make('payment_date')
                                ->label('تاريخ الدفع')
                                ->disabled(),
                            Section::make('تفاصيل المبالغ')
                                ->schema([
                                    TextInput::make('total_price')
                                        ->label('السعر الإجمالي')
                                        ->prefix('SAR')
                                        ->disabled(),
                                    TextInput::make('discount_amount')
                                        ->label('قيمة الخصم')
                                        ->prefix('SAR')
                                        ->disabled(),
                                    Placeholder::make('final_amount')
                                        ->label('المبلغ النهائي')
                                        ->content(fn ($record) => number_format($record->total_price - $record->discount_amount, 2) . ' ريال')
                                ])
                                ->collapsible(),
                        ]),
                ])
                ->columnSpan('full')
        ];
    }

    public function getTitle(): string
    {
        return 'تعديل الطلب';
    }
public function getSaveFormAction(): Actions\Action
{
    return parent::getSaveFormAction()->label('حفظ');
}

public function getCancelFormAction(): Actions\Action
{
    return parent::getCancelFormAction()->label('إلغاء');
}
}

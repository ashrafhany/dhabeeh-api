<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'الطلبات';
    protected static ?string $navigationGroup = 'إدارة الطلبات';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               // TextInput::make('order_number')
                  //  ->required(),
                Select::make('status')
                    ->label('حالة الطلب')
                ->options([
                        'pending' => 'قيد الانتظار',
                        'Current' => 'جاري التنفيذ',
                        'inDelivery' => 'قيد التوصيل',
                        'Delivered' => 'تم التوصيل',
                        'Refused' => 'مرفوض',
                    ])
                    ->required()
                    ->default(fn ($record) => $record->status ?? 'pending'),
             //   TextInput::make('user.name')
                  //  ->label('User')
                  //  ->disabled(),
               // TextInput::make('product.name')
                  //  ->label('Product')
                //    ->disabled(),
                TextInput::make('total_price')
                ->label('السعر الإجمالي')
                ->disabled(),
                TextInput::make('quantity')
                ->label('الكمية')
                ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.first_name')->label('المستخدم')->sortable(),
                TextColumn::make('total_price')->label('السعر الكلي')->sortable(),
                BadgeColumn::make('status') // عرض حالة الطلب مع تمييز اللون
                    ->label('حالة الطلب')
                    ->enum([
                        'pending' => 'قيد الانتظار',
                        'Current' => 'جاري التنفيذ',
                        'inDelivery' => 'قيد التوصيل',
                        'Delivered' => 'تم التوصيل',
                        'Refused' => 'مرفوض',
                ])
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(), // عرض تاريخ الإنشاء
                ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'قيد الانتظار',
                    'Current' => 'جاري التنفيذ',
                    'inDelivery' => 'قيد التوصيل',
                    'Delivered' => 'تم التوصيل',
                    'Refused' => 'مرفوض',
                ]),
                ])
            ->actions([

                Tables\Actions\EditAction::make()->label('تعديل الطلب'),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('حذف الطلب'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
    public static function getPluralModelLabel(): string
{
    return 'الطلبات';
}


}
